<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../helper/sanitize.php';

// ==============================
// CONFIGURATION
// ==============================
$REDIS_HOST = '127.0.0.1';
$REDIS_PORT = 6379;
$QUEUE_NAME = 'queue:add_data';
$LOG_DIR    = __DIR__ . '/logs';
$LOG_FILE   = $LOG_DIR . '/worker_' . date('Y-m-d') . '.log';
$LOG_RETENTION_DAYS = 7;

if (!is_dir($LOG_DIR)) mkdir($LOG_DIR, 0777, true);

// ==============================
// LOG FUNCTION
// ==============================
function writeLog($msg)
{
    global $LOG_FILE;
    $log = "[" . date('Y-m-d H:i:s') . "] $msg" . PHP_EOL;
    echo $log;
    file_put_contents($LOG_FILE, $log, FILE_APPEND);
}

// ==============================
// CLEAN OLD LOGS
// ==============================
function cleanOldLogs($dir, $retentionDays)
{
    foreach (glob($dir . '/worker_*.log') as $file) {
        if (is_file($file) && (time() - filemtime($file)) > ($retentionDays * 86400)) {
            unlink($file);
        }
    }
}
cleanOldLogs($LOG_DIR, $LOG_RETENTION_DAYS);

// ==============================
// REDIS CONNECTION
// ==============================
function connectRedis()
{
    global $REDIS_HOST, $REDIS_PORT;
    while (true) {
        try {
            $redis = new Redis();
            @$redis->connect($REDIS_HOST, $REDIS_PORT, 2.5);
            $redis->setOption(Redis::OPT_READ_TIMEOUT, -1); // biar blPop gak dianggap idle
            if (defined('Redis::OPT_TCP_KEEPALIVE')) {
                $redis->setOption(Redis::OPT_TCP_KEEPALIVE, 1); // aktifkan keep-alive socket
            }
            writeLog("✅ Connected to Redis at $REDIS_HOST:$REDIS_PORT");
            return $redis;
        } catch (Exception $e) {
            writeLog("❌ Redis connect failed: {$e->getMessage()}, retrying in 3s...");
            sleep(3);
        }
    }
}


// ==============================
// MYSQL CONNECTION
// ==============================
function connectMysql()
{
    global $dsn, $user, $pass, $options;
    while (true) {
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            writeLog("✅ Connected to MySQL");
            return $pdo;
        } catch (PDOException $e) {
            writeLog("❌ MySQL connect failed: {$e->getMessage()}, retrying in 5s...");
            sleep(5);
        }
    }
}

// ==============================
// CONNECTION CHECKERS
// ==============================
function mysqlAlive($pdo)
{
    try {
        $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function redisAlive($redis)
{
    // PING kadang error palsu di Windows → jangan spam
    try {
        $pong = @$redis->ping();
        return $pong === '+PONG' || $pong === true;
    } catch (Exception $e) {
        return false;
    }
}

// ==============================
// MAIN LOOP
// ==============================
writeLog("🚀 Worker started...");
$redis = connectRedis();
$pdo   = connectMysql();

$redisDown = false;
$mysqlDown = false;
$lastRedisConnect = time();

while (true) {
    try {
        // -------- Cek koneksi Redis --------
        if (!redisAlive($redis)) {
            // Hindari reconnect spam → minimal jeda 3 detik
            if (time() - $lastRedisConnect < 3) {
                sleep(1);
                continue;
            }

            writeLog("⚠️ Redis lost connection. Reconnecting...");
            $redis = connectRedis();
            $redisDown = false;
            $lastRedisConnect = time();
            writeLog("🔄 Redis reconnected successfully.");
            sleep(2); // cooldown supaya ping berikutnya gak false
            continue;
        }

        // -------- Cek koneksi MySQL --------
        if (!mysqlAlive($pdo)) {
            if (!$mysqlDown) writeLog("⚠️ MySQL lost connection. Reconnecting...");
            $mysqlDown = true;
            $pdo = connectMysql();
            $mysqlDown = false;
            writeLog("🔄 MySQL reconnected successfully.");
            continue;
        }

        // -------- Ambil job dari Redis --------
        $job = null;
        try {
            $job = @$redis->blPop([$QUEUE_NAME], 5);
        } catch (RedisException $e) {
            if (stripos($e->getMessage(), '10054') !== false || stripos($e->getMessage(), 'connection') !== false) {
                writeLog("⚠️ Redis connection forcibly closed, reconnecting...");
                $redis = connectRedis();
                $lastRedisConnect = time();
                sleep(2);
                continue;
            }
        }

        if (!$job) continue; // idle

        // -------- Decode job JSON --------
        $data = json_decode($job[1], true);
        if (!$data) {
            writeLog("⚠️ Invalid job format, skipping...");
            continue;
        }

        // -------- Pastikan MySQL hidup --------
        if (!mysqlAlive($pdo)) {
            writeLog("⚠️ MySQL lost, requeue job...");
            @$redis->rPush($QUEUE_NAME, $job[1]);
            $pdo = connectMysql();
            continue;
        }

        // -------- INSERT LOGIC (tidak diubah) --------
        $record_no      = sanitize($data['record_no']);
        $line_id        = sanitize($data['line_id']);
        $application_id = sanitize($data['application_id']);
        $file_id        = sanitize($data['file_id']);
        $header_id      = sanitize($data['header_id']);

        // tbl_detail_line
        $stmt = $pdo->prepare("
            INSERT INTO tbl_detail_line (line_id, application_id)
            SELECT ?, ? WHERE NOT EXISTS (
                SELECT 1 FROM tbl_detail_line WHERE line_id = ? AND application_id = ?
            )
        ");
        $stmt->execute([$line_id, $application_id, $line_id, $application_id]);

        // tbl_data (1–190)
        $cols1 = $vals1 = [];
        for ($i = 1; $i <= 190; $i++) {
            $cols1[] = "data_$i";
            $vals1[] = $data["data_$i"] ?? null;
        }

        $sql1 = "INSERT INTO tbl_data (record_no, line_id, application_id, file_id, header_id, "
            . implode(',', $cols1) . ")
              VALUES (" . implode(',', array_fill(0, count($vals1) + 5, '?')) . ")";
        $pdo->prepare($sql1)->execute(array_merge([$record_no, $line_id, $application_id, $file_id, $header_id], $vals1));

        // tbl_data2 (191–380)
        $cols2 = $vals2 = [];
        for ($i = 191; $i <= 380; $i++) {
            $cols2[] = "data_$i";
            $vals2[] = $data["data_$i"] ?? null;
        }

        $sql2 = "INSERT INTO tbl_data2 (record_no, line_id, application_id, file_id, header_id, "
            . implode(',', $cols2) . ")
              VALUES (" . implode(',', array_fill(0, count($vals2) + 5, '?')) . ")";
        $pdo->prepare($sql2)->execute(array_merge([$record_no, $line_id, $application_id, $file_id, $header_id], $vals2));

        writeLog("✅ Job success: record_no=$record_no");
    } catch (Exception $e) {
        writeLog("⚠️ General Error: " . $e->getMessage());
        sleep(2);
    }
}
