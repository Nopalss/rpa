<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/cpk_excel_logic.php';

set_time_limit(0);
ini_set('memory_limit', '1024M');

$logFile    = __DIR__ . '/worker_cpk.log';
$statusFile = __DIR__ . '/worker_cpk.status';

function logWorker($msg)
{
    global $logFile;
    $time = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$time] $msg\n", FILE_APPEND);
}

logWorker("WORKER STARTED");

while (true) {
    try {

        // 🔥 Ambil semua konfigurasi site yang VALID
        $rows = $pdo->query("
            SELECT 
                user_id,
                site_name
            FROM tbl_user_settings
            WHERE file_id IS NOT NULL
              AND header_name IS NOT NULL
              AND interval_width > 0
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {

            // HEARTBEAT
            file_put_contents($statusFile, date('Y-m-d H:i:s'));

            $user_id = (int)$row['user_id'];
            $site    = $row['site_name'];

            logWorker("PROCESS user={$user_id} site={$site}");

            // Ambil config lengkap per user + site
            $stmt = $pdo->prepare("
                SELECT *
                FROM tbl_user_settings
                WHERE user_id = :uid
                  AND site_name = :site
                LIMIT 1
            ");
            $stmt->execute([
                ':uid'  => $user_id,
                ':site' => $site
            ]);

            $cfg = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cfg) {
                logWorker("SKIP {$site} (CONFIG NOT FOUND)");
                continue;
            }

            // VALIDASI PARAMETER WAJIB (TANPA LOGIC BARU)
            if (
                empty($cfg['file_id']) ||
                empty($cfg['header_name']) ||
                (float)$cfg['interval_width'] <= 0
            ) {
                logWorker("SKIP {$site} (INVALID PARAM)");
                continue;
            }

            /**
             * =========================================
             * PARAMETER KE ENGINE (TIDAK DIUBAH)
             * =========================================
             */
            $params = [
                'user_id'        => $user_id,
                'site_name'      => $site,
                'file_id'        => $cfg['file_id'],
                'header_name'    => $cfg['header_name'],
                'table_type'     => $cfg['table_type'],
                'line_id'        => $cfg['line_id'],
                'application_id' => $cfg['application_id'],
                'standard_upper' => (float)$cfg['custom_ucl'],
                'standard_lower' => (float)$cfg['custom_lcl'],
                'lower_boundary' => (float)$cfg['lower_boundary'],
                'interval_width' => (float)$cfg['interval_width'],
                'cp_limit'       => $cfg['cp_limit'] ?? null,
                'cpk_limit'      => $cfg['cpk_limit'] ?? null,
            ];

            /**
             * =========================================
             * HITUNG (ENGINE MURNI – TIDAK DIUBAH)
             * =========================================
             */
            $result = calculate_cpk_excel($params, $pdo);

            if (!($result['success'] ?? false)) {
                logWorker("FAILED {$site}");
                continue;
            }

            /**
             * =========================================
             * SIMPAN KE CACHE
             * =========================================
             */
            $stmtSave = $pdo->prepare("
                REPLACE INTO tbl_chart_cache
                (user_id, site_name, result_json, updated_at)
                VALUES (:uid, :site, :json, NOW())
            ");

            $stmtSave->execute([
                ':uid'  => $user_id,
                ':site' => $site,
                ':json' => json_encode($result, JSON_UNESCAPED_UNICODE)
            ]);

            logWorker(
                "SUCCESS user={$user_id} site={$site} " .
                    "CP={$result['cp']} CPK={$result['cpk']} STATUS=" . ($result['final_status'] ?? '-')
            );
            usleep(300000); // 0.3 detik antar site
        }

        // PUTARAN SELESAI
        file_put_contents($statusFile, date('Y-m-d H:i:s'));
        sleep(10);
    } catch (Throwable $e) {
        logWorker("FATAL: " . $e->getMessage());
        sleep(5);
    }
}
