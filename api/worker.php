<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../helper/sanitize.php';

$REDIS_HOST = '127.0.0.1';
$REDIS_PORT = 6379;
$QUEUE_NAME = 'queue:add_data';
$LOG_FILE   = __DIR__ . '/logs/worker.log';

if (!is_dir(__DIR__ . '/logs')) mkdir(__DIR__ . '/logs', 0777, true);

function writeLog($msg)
{
    global $LOG_FILE;
    $line = "[" . date('Y-m-d H:i:s') . "] $msg" . PHP_EOL;
    echo $line;
    file_put_contents($LOG_FILE, $line, FILE_APPEND);
}

function connectRedis()
{
    global $REDIS_HOST, $REDIS_PORT;
    $redis = new Redis();
    while (true) {
        try {
            $redis->connect($REDIS_HOST, $REDIS_PORT);
            writeLog("✅ Connected to Redis at $REDIS_HOST:$REDIS_PORT");
            return $redis;
        } catch (Exception $e) {
            writeLog("❌ Redis connect failed: " . $e->getMessage());
            sleep(3);
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

writeLog("🚀 Worker starting...");
$redis = connectRedis();

while (true) {
    try {
        $job = $redis->blPop(['queue:add_data'], 5);
        if (!$job) continue;

        $data = json_decode($job[1], true);
        if (!$data) {
            writeLog("⚠️ Invalid job format");
            continue;
        }


        if (!mysqlAlive($pdo)) {
            writeLog("⏸ MySQL offline, requeue job...");
            $redis->rPush('queue:add_data', $job[1]);
            sleep(3);
            continue;
        }

        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);

        $record_no = sanitize($data['record_no']);
        $line_id = sanitize($data['line_id']);
        $application_id = sanitize($data['application_id']);
        $file_id = sanitize($data['file_id']);
        $header_id = sanitize($data['header_id']);

        // --- tbl_data ---
        $cols1 = $vals1 = [];
        for ($i = 1; $i <= 190; $i++) {
            $cols1[] = "data_$i";
            $vals1[] = $data["data_$i"] ?? null;
        }

        $sql1 = "INSERT INTO tbl_data (record_no, line_id, application_id, file_id, header_id, "
            . implode(',', $cols1) . ") VALUES (" . implode(',', array_fill(0, count($vals1) + 5, '?')) . ")";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute(array_merge([$record_no, $line_id, $application_id, $file_id, $header_id], $vals1));

        // --- tbl_data2 ---
        $cols2 = $vals2 = [];
        for ($i = 191; $i <= 380; $i++) {
            $cols2[] = "data_$i";
            $vals2[] = $data["data_$i"] ?? null;
        }

        $sql2 = "INSERT INTO tbl_data2 (record_no, line_id, application_id, file_id, header_id, "
            . implode(',', $cols2) . ") VALUES (" . implode(',', array_fill(0, count($vals2) + 5, '?')) . ")";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute(array_merge([$record_no, $line_id, $application_id, $file_id, $header_id], $vals2));

        writeLog("✅ Job success: $record_no");
    } catch (PDOException $e) {
        writeLog("❌ DB Error: " . $e->getMessage());
        if (!empty($job[1])) $redis->rPush($QUEUE_NAME, $job[1]);
        sleep(2);
        continue;
    } catch (RedisException $e) {
        writeLog("⚠️ Redis lost, reconnecting...");
        $redis = connectRedis();
        continue;
    } catch (Exception $e) {
        writeLog("⚠️ General Error: " . $e->getMessage());
        continue;
    }
}
