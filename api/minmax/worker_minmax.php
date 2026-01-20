<?php

/**
 * ==========================================================
 * SPC MIN/MAX WORKER (CLI SAFE - PRODUCTION)
 * ==========================================================
 */


error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set(
    'error_log',
    __DIR__ . '/../../storage/logs/php_worker_error.log'
);
date_default_timezone_set('Asia/Jakarta');

/**
 * ==========================================================
 * LOAD CONFIG (CLI ONLY, NO SESSION OUTPUT)
 * ==========================================================
 */
require_once __DIR__ . '/../../includes/config_cli.php';
require_once __DIR__ . '/queue.php';
require_once __DIR__ . '/engine.php';

set_time_limit(0);
ini_set('memory_limit', '1024M');

/**
 * ==========================================================
 * LOGGER (ANTI FILE LOCK WINDOWS)
 * ==========================================================
 */
$logFile = __DIR__ . '/../../storage/logs/minmax_worker.log';

function logWorker(string $msg): void
{
    global $logFile;
    $time = date('Y-m-d H:i:s');

    @file_put_contents(
        $logFile,
        "[$time] $msg\n",
        FILE_APPEND | LOCK_EX // cukup ini
    );
}

logWorker('WORKER STARTED');

/**
 * ==========================================================
 * MAIN LOOP (NEVER EXIT)
 * ==========================================================
 */
while (true) {

    $job_id = null;

    try {

        /**
         * 1️⃣ POP JOB FROM QUEUE
         */
        $job = queue_pop($pdo);

        if (!$job) {
            sleep(2);
            continue;
        }

        $job_id  = (int)$job['id'];
        $user_id = (int)$job['user_id'];
        $site    = $job['site_name'];

        logWorker("PROCESS job={$job_id} user={$user_id} site={$site}");

        /**
         * 2️⃣ LOAD SITE CONFIG
         */
        $stmt = $pdo->prepare("
            SELECT *
            FROM tbl_spc_model_settings
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
            throw new RuntimeException("Config not found");
        }

        /**
         * 3️⃣ VALIDATION (NO LOGIC CHANGE)
         */
        if (
            empty($cfg['file_id']) ||
            empty($cfg['start_col']) ||
            empty($cfg['end_col']) ||
            empty($cfg['agg_mode']) ||
            (float)$cfg['interval_width'] <= 0
        ) {
            throw new RuntimeException("Invalid config parameter");
        }

        /**
         * 4️⃣ BUILD ENGINE PARAMS
         */
        $params = [
            'user_id'        => $user_id,
            'site_name'      => $site,
            'file_id'        => (int)$cfg['file_id'],
            'line_id'        => (int)$cfg['line_id'],
            'application_id' => (int)$cfg['application_id'],
            'start_col'      => (int)$cfg['start_col'],
            'end_col'        => (int)$cfg['end_col'],
            'agg_mode'       => $cfg['agg_mode'],
            'standard_upper' => (float)$cfg['standard_upper'],
            'standard_lower' => (float)$cfg['standard_lower'],
            'lower_boundary' => (float)$cfg['lower_boundary'],
            'interval_width' => (float)$cfg['interval_width'],
            'cp_limit'       => $cfg['cp_limit'] ?? null,
            'cpk_limit'      => $cfg['cpk_limit'] ?? null,
        ];

        /**
         * 5️⃣ CALCULATION (ENGINE)
         */
        $result = calculate_cpk_excel_minmax($params, $pdo);

        if (empty($result['success'])) {
            throw new RuntimeException("Calculation failed");
        }

        /**
         * 6️⃣ SAVE CACHE
         */
        $stmt = $pdo->prepare("
            INSERT INTO spc_minmax_cache
                (user_id, site_name, result_json, calculated_at)
            VALUES
                (:uid, :site, :json, NOW())
            ON DUPLICATE KEY UPDATE
                result_json  = VALUES(result_json),
                calculated_at = NOW()
        ");
        $stmt->execute([
            ':uid'  => $user_id,
            ':site' => $site,
            ':json' => json_encode($result, JSON_UNESCAPED_UNICODE)
        ]);

        /**
         * 7️⃣ MARK JOB DONE
         */
        queue_done($pdo, $job_id);

        logWorker(
            "SUCCESS job={$job_id} site={$site} CP={$result['cp']} CPK={$result['cpk']}"
        );

        usleep(300000);
    } catch (Throwable $e) {

        if ($job_id) {
            queue_fail($pdo, $job_id, $e->getMessage());
        }

        logWorker("ERROR job={$job_id} : " . $e->getMessage());
        sleep(2);
    }
}
