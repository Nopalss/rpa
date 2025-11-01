<?php
require_once __DIR__ . '/../includes/config.php'; // Sesuaikan path ke config.php

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Invalid request.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Ganti 'user_id' dengan session key Anda
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('User not authenticated.');
        }
        $user_id = $_SESSION['user_id'];

        // Ambil data JSON dari 'fetch' atau '$.ajax'
        $data = json_decode(file_get_contents('php://input'), true);

        // Sanitasi data
        $site_name = $data['site_name'] ?? null;
        $line_id = filter_var($data['line_id'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $app_id = filter_var($data['application_id'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $file_id = filter_var($data['file_id'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $header_name = $data['header_name'] ?? null;

        // --- INI PERBAIKANNYA ---
        // Ubah boolean (true/false) dari JS menjadi integer (1/0) untuk MySQL
        $is_active = !empty($data['is_active']) ? 1 : 0;
        // --- AKHIR PERBAIKAN ---

        if (empty($site_name) || empty($user_id)) {
            throw new Exception('Missing required data (user_id, site_name).');
        }

        // Query ini akan meng-UPDATE jika data sudah ada (berkat UNIQUE KEY)
        $sql = "INSERT INTO tbl_user_settings (
                    user_id, site_name, line_id, application_id, file_id, header_name, is_active
                ) VALUES (
                    :user_id, :site_name, :line_id, :app_id, :file_id, :header_name, :is_active
                )
                ON DUPLICATE KEY UPDATE
                    line_id = VALUES(line_id),
                    application_id = VALUES(application_id),
                    file_id = VALUES(file_id),
                    header_name = VALUES(header_name),
                    is_active = VALUES(is_active)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':site_name' => $site_name,
            ':line_id' => $line_id,
            ':app_id' => $app_id,
            ':file_id' => $file_id,
            ':header_name' => $header_name,
            ':is_active' => $is_active // Ini sekarang akan mengirim 1 atau 0
        ]);

        $response['success'] = true;
        $response['message'] = 'Settings saved.';
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
exit;
