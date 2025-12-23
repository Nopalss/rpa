<?php
// Aktifkan batas waktu tak terbatas untuk file besar
set_time_limit(0);

// Tentukan lokasi file ZIP yang mau di-download
$file = __DIR__ . '/download/ClientUploader2_Package.zip';

// Cek apakah file ada
if (!file_exists($file)) {
    http_response_code(404);
    exit('❌ File tidak ditemukan.');
}

// Bersihkan buffer output (mencegah error header already sent)
if (ob_get_level()) {
    ob_end_clean();
}

// Atur header agar browser langsung download file ZIP
header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));

// Kirim file ke browser
readfile($file);
exit;
