<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../helper/sanitize.php';

/**
 * =========================================================
 * CONFIGURATION
 * =========================================================
 */
$REDIS_HOST = '127.0.0.1';
$REDIS_PORT = 6379;
$QUEUE_NAME = 'queue:add_data';

$LOG_DIR  = __DIR__ . '/logs';
$LOG_FILE = $LOG_DIR . '/worker_' . date('Y-m-d') . '.log';
$LOG_RETENTION_DAYS = 7;

if (!is_dir($LOG_DIR)) {
    mkdir($LOG_DIR, 0777, true);
}

/**
 * =========================================================
 * LOGGING
 * =========================================================
 */
function writeLog($msg)
{
    global $LOG_FILE;
    $line = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;
    echo $line;
    file_put_contents($LOG_FILE, $line, FILE_APPEND);
}

function cleanOldLogs($dir, $days)
{
    foreach (glob($dir . '/worker_*.log') as $file) {
        if (is_file($file) && (time() - filemtime($file)) > ($days * 86400)) {
            @unlink($file);
        }
    }
}
cleanOldLogs($LOG_DIR, $LOG_RETENTION_DAYS);

/**
 * =========================================================
 * REDIS
 * =========================================================
 */
function connectRedis()
{
    global $REDIS_HOST, $REDIS_PORT;

    while (true) {
        try {
            $redis = new Redis();
            @$redis->connect($REDIS_HOST, $REDIS_PORT, 2.5);
            $redis->setOption(Redis::OPT_READ_TIMEOUT, -1);
            if (defined('Redis::OPT_TCP_KEEPALIVE')) {
                $redis->setOption(Redis::OPT_TCP_KEEPALIVE, 1);
            }
            writeLog("Connected to Redis {$REDIS_HOST}:{$REDIS_PORT}");
            return $redis;
        } catch (Exception $e) {
            writeLog("Redis connect failed: {$e->getMessage()}, retrying...");
            sleep(3);
        }
    }
}

function redisAlive($redis)
{
    try {
        $pong = @$redis->ping();
        return ($pong === '+PONG' || $pong === true);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * =========================================================
 * MYSQL
 * =========================================================
 */
function connectMysql()
{
    global $dsn, $user, $pass, $options;

    while (true) {
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            writeLog("Connected to MySQL");
            return $pdo;
        } catch (PDOException $e) {
            writeLog("MySQL connect failed: {$e->getMessage()}, retrying...");
            sleep(5);
        }
    }
}

function mysqlAlive($pdo)
{
    try {
        $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * =========================================================
 * PRODUCTION DATE
 * =========================================================
 */
date_default_timezone_set('Asia/Jakarta');

function get_production_date($cutoff_hour = 6, $cutoff_minute = 0)
{
    $now = new DateTime();
    $h = (int)$now->format('H');
    $m = (int)$now->format('i');

    if ($h < $cutoff_hour || ($h === $cutoff_hour && $m <= $cutoff_minute)) {
        $now->modify('-1 day');
    }
    return $now->format('Y-m-d');
}

/**
 * =========================================================
 * MAIN LOOP
 * =========================================================
 */
writeLog("Worker started");

$redis = connectRedis();
$pdo   = connectMysql();

$lastRedisReconnect = 0;

while (true) {
    try {

        /**
         * ---------- Redis health ----------
         */
        if (!redisAlive($redis)) {
            if (time() - $lastRedisReconnect < 3) {
                sleep(1);
                continue;
            }
            writeLog("Redis disconnected, reconnecting...");
            $redis = connectRedis();
            $lastRedisReconnect = time();
            sleep(2);
            continue;
        }

        /**
         * ---------- MySQL health ----------
         */
        if (!mysqlAlive($pdo)) {
            writeLog("MySQL disconnected, reconnecting...");
            $pdo = connectMysql();
            continue;
        }

        /**
         * ---------- Fetch job ----------
         */
        $job = null;
        try {
            $job = @$redis->blPop([$QUEUE_NAME], 5);
        } catch (RedisException $e) {
            writeLog("Redis BLPOP error: " . $e->getMessage());
            $redis = connectRedis();
            sleep(2);
            continue;
        }

        if (!$job) {
            continue; // idle
        }

        /**
         * ---------- Decode payload ----------
         */
        $data = json_decode($job[1], true);
        if (!$data) {
            writeLog("Invalid JSON payload, skipped");
            continue;
        }

        /**
         * ---------- Extract core fields ----------
         */
        $record_no       = sanitize($data['record_no'] ?? '');
        $line_id         = sanitize($data['line_id'] ?? '');
        $application_id  = sanitize($data['application_id'] ?? '');
        $file_id         = sanitize($data['file_id'] ?? '');
        $header_id       = sanitize($data['header_id'] ?? '');
        $production_date = $data['production_date'] ?? get_production_date();

        // SCM = data_1
        $scm = isset($data['data_1']) ? sanitize($data['data_1']) : null;

        /**
         * ---------- tbl_detail_line ----------
         */
        $stmt = $pdo->prepare("
            INSERT INTO tbl_detail_line (line_id, application_id)
            SELECT ?, ?
            WHERE NOT EXISTS (
                SELECT 1 FROM tbl_detail_line
                WHERE line_id = ? AND application_id = ?
            )
        ");
        $stmt->execute([$line_id, $application_id, $line_id, $application_id]);

        /**
         * =====================================================
         * tbl_data (data_1 – data_190)
         * UNIQUE KEY: (file_id, date, data_1)
         * =====================================================
         */
        $cols1 = $vals1 = $upd1 = [];

        for ($i = 1; $i <= 190; $i++) {
            $col = "data_$i";
            $cols1[] = $col;
            $vals1[] = $data[$col] ?? null;
            $upd1[]  = "$col = VALUES($col)";
        }

        $sql1 = "
            INSERT INTO tbl_data (
                record_no, date, line_id, application_id, file_id, header_id,
                " . implode(',', $cols1) . "
            ) VALUES (
                " . implode(',', array_fill(0, count($vals1) + 6, '?')) . "
            )
            ON DUPLICATE KEY UPDATE
                record_no = VALUES(record_no),
                line_id = VALUES(line_id),
                application_id = VALUES(application_id),
                header_id = VALUES(header_id),
                " . implode(',', $upd1) . "
        ";

        $pdo->prepare($sql1)->execute(
            array_merge(
                [$record_no, $production_date, $line_id, $application_id, $file_id, $header_id],
                $vals1
            )
        );

        /**
         * =====================================================
         * tbl_data2 (data_191 – data_380)
         * UNIQUE KEY: (file_id, date, scm)
         * =====================================================
         */
        $cols2 = $vals2 = $upd2 = [];

        for ($i = 191; $i <= 380; $i++) {
            $col = "data_$i";
            $cols2[] = $col;
            $vals2[] = $data[$col] ?? null;
            $upd2[]  = "$col = VALUES($col)";
        }

        $sql2 = "
            INSERT INTO tbl_data2 (
                record_no, date, line_id, application_id, file_id, header_id, scm,
                " . implode(',', $cols2) . "
            ) VALUES (
                " . implode(',', array_fill(0, count($vals2) + 7, '?')) . "
            )
            ON DUPLICATE KEY UPDATE
                record_no = VALUES(record_no),
                line_id = VALUES(line_id),
                application_id = VALUES(application_id),
                header_id = VALUES(header_id),
                scm = VALUES(scm),
                " . implode(',', $upd2) . "
        ";

        $pdo->prepare($sql2)->execute(
            array_merge(
                [$record_no, $production_date, $line_id, $application_id, $file_id, $header_id, $scm],
                $vals2
            )
        );

        writeLog("Job success | file_id={$file_id} | date={$production_date} | scm={$scm}");
        file_put_contents(__DIR__ . '/worker.status', time());
    } catch (Exception $e) {
        writeLog("General error: " . $e->getMessage());
        sleep(2);
    }
}
