<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/chart/cpk_excel_logic.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Invalid request.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('User not authenticated.');
        }

        $user_id = $_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        // =========================
        // PARAMETER UTAMA
        // =========================
        $site_name   = $data['site_name'] ?? null;
        $line_id     = filter_var($data['line_id'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $app_id      = filter_var($data['application_id'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $file_id     = filter_var($data['file_id'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $header_name = $data['header_name'] ?? null;

        if (!$site_name) {
            throw new Exception('Missing site_name');
        }

        $is_active = !empty($data['is_active']) ? 1 : 0;

        $custom_lcl = isset($data['custom_lcl']) && $data['custom_lcl'] !== '' ? (float)$data['custom_lcl'] : null;
        $custom_ucl = isset($data['custom_ucl']) && $data['custom_ucl'] !== '' ? (float)$data['custom_ucl'] : null;
        $lower_boundary = isset($data['lower_boundary']) && $data['lower_boundary'] !== '' ? (float)$data['lower_boundary'] : null;
        $interval_width = isset($data['interval_width']) && $data['interval_width'] !== '' ? (float)$data['interval_width'] : null;

        $cp_limit  = isset($data['cp_limit']) ? (float)$data['cp_limit'] : null;
        $cpk_limit = isset($data['cpk_limit']) ? (float)$data['cpk_limit'] : null;
        $site_label = !empty($data['site_label']) ? trim($data['site_label']) : null;

        // =========================
        // SIMPAN USER SETTING
        // =========================
        $sql = "
        INSERT INTO tbl_user_settings (
            user_id, site_name, site_label,
            line_id, application_id, file_id, header_name, is_active,
            custom_lcl, custom_ucl, lower_boundary, interval_width,
            cp_limit, cpk_limit
        ) VALUES (
            :user_id, :site_name, :site_label,
            :line_id, :app_id, :file_id, :header_name, :is_active,
            :custom_lcl, :custom_ucl, :lower_boundary, :interval_width,
            :cp_limit, :cpk_limit
        )
        ON DUPLICATE KEY UPDATE
            site_label     = VALUES(site_label),
            line_id        = VALUES(line_id),
            application_id = VALUES(application_id),
            file_id        = VALUES(file_id),
            header_name    = VALUES(header_name),
            is_active      = VALUES(is_active),
            custom_lcl     = VALUES(custom_lcl),
            custom_ucl     = VALUES(custom_ucl),
            lower_boundary = VALUES(lower_boundary),
            interval_width = VALUES(interval_width),
            cp_limit       = VALUES(cp_limit),
            cpk_limit      = VALUES(cpk_limit);
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'        => $user_id,
            ':site_name'      => $site_name,
            ':site_label'     => $site_label,
            ':line_id'        => $line_id,
            ':app_id'         => $app_id,
            ':file_id'        => $file_id,
            ':header_name'    => $header_name,
            ':is_active'      => $is_active,
            ':custom_lcl'     => $custom_lcl,
            ':custom_ucl'     => $custom_ucl,
            ':lower_boundary' => $lower_boundary,
            ':interval_width' => $interval_width,
            ':cp_limit'       => $cp_limit,
            ':cpk_limit'      => $cpk_limit
        ]);

        // =========================
        // 🔥 FAST LANE HITUNG LANGSUNG
        // =========================
        if (
            $file_id &&
            $header_name &&
            $interval_width > 0
        ) {
            $params = [
                'user_id'        => $user_id,
                'site_name'      => $site_name,
                'file_id'        => $file_id,
                'header_name'    => $header_name,
                'line_id'        => $line_id,
                'application_id' => $app_id,
                'standard_upper' => (float)$custom_ucl,
                'standard_lower' => (float)$custom_lcl,
                'lower_boundary' => (float)$lower_boundary,
                'interval_width' => (float)$interval_width
            ];

            $result = calculate_cpk_excel($params, $pdo);

            if ($result['success'] ?? false) {
                $stmtCache = $pdo->prepare("
                    REPLACE INTO tbl_chart_cache
                    (user_id, site_name, result_json, updated_at)
                    VALUES (:uid, :site, :json, NOW())
                ");
                $stmtCache->execute([
                    ':uid'  => $user_id,
                    ':site' => $site_name,
                    ':json' => json_encode($result, JSON_UNESCAPED_UNICODE)
                ]);
            }
        }

        $response['success'] = true;
        $response['message'] = 'Settings saved & calculated.';
    } catch (Throwable $e) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
exit;
