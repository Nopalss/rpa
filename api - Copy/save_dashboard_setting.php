<?php
require_once __DIR__ . '/../includes/config.php';

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

        // Ambil JSON
        $data = json_decode(file_get_contents('php://input'), true);

        // Parameter utama
        $site_name   = $data['site_name'] ?? null;
        $line_id     = filter_var($data['line_id'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $app_id      = filter_var($data['application_id'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $file_id     = filter_var($data['file_id'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $header_name = $data['header_name'] ?? null;

        $is_active   = !empty($data['is_active']) ? 1 : 0;

        // ------------------------
        // Custom LCL & UCL
        // ------------------------
        $custom_lcl = null;
        if (array_key_exists('custom_lcl', $data) && $data['custom_lcl'] !== '' && $data['custom_lcl'] !== null) {
            $custom_lcl = (float)$data['custom_lcl'];
        }

        $custom_ucl = null;
        if (array_key_exists('custom_ucl', $data) && $data['custom_ucl'] !== '' && $data['custom_ucl'] !== null) {
            $custom_ucl = (float)$data['custom_ucl'];
        }
        $site_label = null;
        if (array_key_exists('site_label', $data) && $data['site_label'] !== '') {
            $site_label = trim($data['site_label']);
        }
        // ------------------------
        // NEW: lower_boundary & interval_width
        // ------------------------
        $lower_boundary = null;
        if (array_key_exists('lower_boundary', $data) && $data['lower_boundary'] !== '' && $data['lower_boundary'] !== null) {
            $lower_boundary = (float)$data['lower_boundary'];
        }

        $interval_width = null;
        if (array_key_exists('interval_width', $data) && $data['interval_width'] !== '' && $data['interval_width'] !== null) {
            $interval_width = (float)$data['interval_width'];
        }

        if (empty($site_name)) {
            throw new Exception('Missing required data (site_name).');
        }

        // ------------------------
        // INSERT / UPDATE
        // ------------------------
        $sql = "INSERT INTO tbl_user_settings (
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
    cpk_limit      = VALUES(cpk_limit);";


        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'        => $user_id,
            ':site_name'      => $site_name,
            ':line_id'        => $line_id,
            ':app_id'         => $app_id,
            ':file_id'        => $file_id,
            ':header_name'    => $header_name,
            ':is_active'      => $is_active,
            ':custom_lcl'     => $custom_lcl,
            ':custom_ucl'     => $custom_ucl,
            ':lower_boundary' => $lower_boundary,
            ':interval_width' => $interval_width,
            ':cp_limit'       => isset($data['cp_limit']) ? (float)$data['cp_limit'] : null,
            ':cpk_limit'      => isset($data['cpk_limit']) ? (float)$data['cpk_limit'] : null,
            ':site_label' => $site_label
        ]);


        $response['success'] = true;
        $response['message'] = 'Settings saved.';
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
exit;
