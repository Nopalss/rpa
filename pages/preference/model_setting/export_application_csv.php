<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../helper/redirect.php';
require_once __DIR__ . '/../../../helper/setAlert.php'; // sesuai instruksi kamu

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="tbl_application_export.csv"');
header('Pragma: no-cache');
header('Expires: 0');

try {
    // Ambil semua data dari tbl_application
    $stmt = $pdo->query("
        SELECT id, name, path, created_at, created_by, modify_at, modify_by 
        FROM tbl_application
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Jika data kosong
    if (empty($rows)) {
        setAlert('info', 'Tidak ada data', 'Tabel tbl_application masih kosong.', 'info');
        redirect('pages/preference/model_setting.php');
        exit;
    }

    // Output CSV
    $output = fopen('php://output', 'w');

    // Header kolom CSV
    fputcsv($output, ['id', 'name', 'path', 'created_at', 'created_by', 'modify_at', 'modify_by']);

    // Data baris CSV
    foreach ($rows as $row) {
        $cleanRow = array_map(fn($v) => $v === null ? '' : $v, $row);
        fputcsv($output, $cleanRow);
    }

    fclose($output);
    exit;
} catch (PDOException $e) {
    // Jika terjadi error, tampilkan alert dan redirect
    setAlert('error', 'Gagal Export CSV', $e->getMessage(), 'danger');
    redirect('pages/preference/model_setting.php');
    exit;
}
