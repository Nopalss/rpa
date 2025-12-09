<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../helper/sanitize.php';

$REDIS_HOST = '127.0.0.1';
$REDIS_PORT = 6379;
$QUEUE_NAME = 'queue:add_data';

function writeLog($msg)
{
    // cuma tampil di console, tidak disimpan
    echo "[" . date('Y-m-d H:i:s') . "] $msg" . PHP_EOL;
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
            sleep(2);
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

writeLog("🚀 Worker started...");
$redis = connectRedis();

while (true) {
    try {
        $job = $redis->blPop([$QUEUE_NAME], 5);
        if (!$job) continue;

        $data = json_decode($job[1], true);
        if (!$data) {
            writeLog("⚠️ Invalid job format");
            continue;
        }

        if (!mysqlAlive($pdo)) {
            writeLog("⏸ MySQL offline, requeue job...");
            $redis->rPush($QUEUE_NAME, $job[1]);
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

        // ==========================================
        // 🧩 Insert ke tbl_detail_line jika belum ada
        // ==========================================
        $stmt = $pdo->prepare("
            INSERT INTO tbl_detail_line (line_id, application_id)
            SELECT ?, ?
            WHERE NOT EXISTS (
                SELECT 1 FROM tbl_detail_line 
                WHERE line_id = ? AND application_id = ?
            )
        ");
        $stmt->execute([$line_id, $application_id, $line_id, $application_id]);
        writeLog("ℹ️ Checked/inserted detail line_id=$line_id app_id=$application_id");

        // ==========================================
        // 🧩 Insert ke tbl_data (1–190)
        // ==========================================
        $cols1 = $vals1 = [];
        for ($i = 1; $i <= 190; $i++) {
            $cols1[] = "data_$i";
            $vals1[] = $data["data_$i"] ?? null;
        }

        $sql1 = "INSERT INTO tbl_data (record_no, line_id, application_id, file_id, header_id, "
            . implode(',', $cols1) . ") VALUES (" . implode(',', array_fill(0, count($vals1) + 5, '?')) . ")";
        $pdo->prepare($sql1)->execute(array_merge([$record_no, $line_id, $application_id, $file_id, $header_id], $vals1));

        // ==========================================
        // 🧩 Insert ke tbl_data2 (191–380)
        // ==========================================
        $cols2 = $vals2 = [];
        for ($i = 191; $i <= 380; $i++) {
            $cols2[] = "data_$i";
            $vals2[] = $data["data_$i"] ?? null;
        }

        $sql2 = "INSERT INTO tbl_data2 (record_no, line_id, application_id, file_id, header_id, "
            . implode(',', $cols2) . ") VALUES (" . implode(',', array_fill(0, count($vals2) + 5, '?')) . ")";
        $pdo->prepare($sql2)->execute(array_merge([$record_no, $line_id, $application_id, $file_id, $header_id], $vals2));

        writeLog("✅ Job success: record_no=$record_no");
    } catch (PDOException $e) {
        writeLog("❌ DB Error: " . $e->getMessage());
        if (!empty($job[1])) $redis->rPush($QUEUE_NAME, $job[1]);
        sleep(1);
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
