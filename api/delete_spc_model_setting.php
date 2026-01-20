<?php
// api/delete_spc_model_setting.php

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $site_name = $_POST['site_name'] ?? '';

    if (!$site_name) {
        throw new Exception('site_name is required');
    }

    // 🔒 Safety: hanya allow format siteX
    if (!preg_match('/^site\d+$/', $site_name)) {
        throw new Exception('Invalid site_name format');
    }

    $stmt = $pdo->prepare("
        DELETE FROM tbl_spc_model_settings
        WHERE site_name = ?
    ");

    $stmt->execute([$site_name]);

    echo json_encode([
        'success' => true,
        'deleted_site' => $site_name
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
