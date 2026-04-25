<?php
// Set header untuk merespon sebagai JSON
header('Content-Type: application/json');

// Mulai config dan session
require_once __DIR__ . '/../includes/config.php'; // Sesuaikan path ke config.php

// Inisialisasi response
$response = [
    'success' => false,
    'message' => 'Terjadi kesalahan.'
];

try {
    // 1. Validasi Input
    $user_id = $_SESSION['user_id'] ?? 0;
    $site_name = $_POST['site_name'] ?? '';
    $second = $_POST['second'] ?? null;

    if ($user_id == 0) {
        throw new Exception('Sesi tidak valid. Silakan login kembali.');
    }
    if (empty($site_name)) {
        throw new Exception('Site Name tidak boleh kosong.');
    }
    if ($second === null || !is_numeric($second)) {
        throw new Exception('Interval harus berupa angka.');
    }

    // 2. Update Database
    // Kita update berdasarkan user_id (dari Sesi) dan site_name (dari AJAX)
    $stmt = $pdo->prepare("
        UPDATE tbl_user_settings 
        SET second = :second 
        WHERE user_id = :user_id AND site_name = :site_name
    ");

    $success = $stmt->execute([
        ':second' => $second,
        ':user_id' => $user_id,
        ':site_name' => $site_name
    ]);

    if ($success) {
        $response['success'] = true;
        $response['message'] = 'Interval berhasil diperbarui.';
    } else {
        throw new Exception('Gagal memperbarui database.');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// 3. Kembalikan response JSON
echo json_encode($response);
exit;
