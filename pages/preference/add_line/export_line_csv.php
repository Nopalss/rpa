<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../helper/redirect.php';
require_once __DIR__ . '/../../../helper/setAlert.php'; // pastikan file ini berisi function setAlert()

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="tbl_line_export.csv"');
header('Pragma: no-cache');
header('Expires: 0');

try {
    // Query semua data dari tbl_line
    $stmt = $pdo->query("SELECT line_id, line_name, create_at, create_by, update_by, update_date FROM tbl_line");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Jika data kosong
    if (empty($rows)) {
        setAlert('info', 'Tidak ada data', 'Tabel tbl_line masih kosong.', 'info');
        redirect('pages/preference/add_line/');
        exit;
    }

    // Output CSV
    $output = fopen('php://output', 'w');

    // Header kolom CSV
    fputcsv($output, ['line_id', 'line_name', 'create_at', 'create_by', 'update_by', 'update_date']);

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
    redirect('pages/preference/add_line/');
    exit;
}
