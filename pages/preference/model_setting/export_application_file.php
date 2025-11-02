<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../helper/redirect.php';
require_once __DIR__ . '/../../../helper/setAlert.php';

try {
    // Pastikan parameter id ada
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setAlert('error', 'Parameter tidak valid', 'Application ID tidak ditemukan.', 'danger');
        redirect('pages/preference/model_setting.php');
        exit;
    }

    $appId = (int) $_GET['id'];

    // Ambil data aplikasi
    $stmtApp = $pdo->prepare("SELECT name FROM tbl_application WHERE id = :id");
    $stmtApp->execute([':id' => $appId]);
    $application = $stmtApp->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        setAlert('error', 'Data tidak ditemukan', 'Application dengan ID tersebut tidak ada.', 'danger');
        redirect('pages/preference/model_setting.php');
        exit;
    }

    // Ambil semua filename dari tbl_filename
    $stmtFile = $pdo->prepare("
        SELECT filename, create_at, create_by
        FROM tbl_filename
        WHERE application_id = :app_id
        ORDER BY create_at ASC
    ");
    $stmtFile->execute([':app_id' => $appId]);
    $files = $stmtFile->fetchAll(PDO::FETCH_ASSOC);

    if (empty($files)) {
        setAlert('info', 'Tidak ada file', 'Application ini belum memiliki file.', 'info');
        redirect('pages/preference/model_setting.php');
        exit;
    }

    // Buat nama file CSV
    $appName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $application['name']);
    $fileName = "application_{$appName}_files.csv";

    // Header untuk download CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');

    $output = fopen('php://output', 'w');

    // Header kolom CSV
    fputcsv($output, ['filename', 'create_at', 'create_by']);

    // Isi data CSV
    foreach ($files as $file) {
        $cleanRow = array_map(fn($v) => $v === null ? '' : $v, $file);
        fputcsv($output, $cleanRow);
    }

    fclose($output);
    exit;
} catch (Exception $e) {
    setAlert('error', 'Gagal Export CSV', $e->getMessage(), 'danger');
    redirect('pages/preference/model_setting.php');
    exit;
}
