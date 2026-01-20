<?php

/**
 * ==========================================================
 * MIN/MAX SPC ENGINE (CLI + HTTP SAFE)
 * ==========================================================
 */

ini_set('precision', 17);
ini_set('serialize_precision', -1);
date_default_timezone_set('Asia/Jakarta');

/**
 * ==========================================================
 * ENTRY POINT (DIPAKAI WORKER & API)
 * ==========================================================
 */
function calculate_cpk_excel_minmax(array $params, PDO $pdo): array
{
    try {

        /**
         * ==================================================
         * 1️⃣ VALIDASI PARAMETER MINIMAL
         * ==================================================
         */
        $required = [
            'file_id',
            'line_id',
            'application_id',
            'start_col',
            'end_col',
            'agg_mode',
            'standard_upper',
            'standard_lower',
            'lower_boundary',
            'interval_width'
        ];

        foreach ($required as $key) {
            if (!array_key_exists($key, $params)) {
                return [
                    'success' => false,
                    'message' => "Missing parameter: {$key}"
                ];
            }
        }

        /**
         * ==================================================
         * 2️⃣ MAP KE $_POST (AGAR LOGIC LAMA TETAP UTUH)
         * ==================================================
         */
        $_POST = [
            'file_id'         => $params['file_id'],
            'line_id'         => $params['line_id'],
            'application_id'  => $params['application_id'],
            'start_col'       => $params['start_col'],
            'end_col'         => $params['end_col'],
            'agg_mode'        => $params['agg_mode'],
            'standard_upper'  => $params['standard_upper'],
            'standard_lower'  => $params['standard_lower'],
            'lower_boundary'  => $params['lower_boundary'],
            'interval_width'  => $params['interval_width'],
        ];

        // ❌ JANGAN session_start di engine
        if (isset($params['user_id'])) {
            $GLOBALS['__engine_user_id__'] = (int)$params['user_id'];
        }
        if (isset($params['site_name'])) {
            $_POST['site_name'] = $params['site_name'];
        }

        /**
         * ==================================================
         * 3️⃣ PANGGIL CORE LOGIC (ANTI EXIT / ANTI RETURN)
         * ==================================================
         *
         * ⚠️ _logic_minmax_core.php:
         * - TIDAK BOLEH exit / die
         * - TIDAK BOLEH return hasil final
         * - HASIL DISIMPAN KE $GLOBALS['__minmax_result__']
         */
        $GLOBALS['__minmax_result__'] = null;

        ob_start();
        include __DIR__ . '/_logic_minmax_core.php';
        ob_end_clean();

        $result = $GLOBALS['__minmax_result__'] ?? null;
        unset($GLOBALS['__minmax_result__']);

        if (!is_array($result) || empty($result['success'])) {
            return [
                'success' => false,
                'message' => 'Engine calculation failed'
            ];
        }

        /**
         * ==================================================
         * 4️⃣ TAMBAHAN STATUS CP / CPK
         * ==================================================
         */
        $cp_limit  = $params['cp_limit']  ?? 0.85;
        $cpk_limit = $params['cpk_limit'] ?? 0.85;

        $result['cp_status'] =
            ($result['cp'] !== null && $result['cp'] >= $cp_limit)
            ? 'OK'
            : 'Over Spec';

        $result['cpk_status'] =
            ($result['cpk'] !== null && $result['cpk'] >= $cpk_limit)
            ? 'OK'
            : 'Over Spec';

        return $result;
    } catch (Throwable $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
