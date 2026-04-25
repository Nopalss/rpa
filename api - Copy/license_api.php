<?php
header('Content-Type: application/json');

// Path file .lic (pastikan path benar)
$licenseFile = __DIR__ . '/../lisence.lic';

// Jika tidak ditemukan, kembalikan error
if (!file_exists($licenseFile)) {
    echo json_encode(["error" => "File lisensi tidak ditemukan"]);
    exit;
}

// Baca isi file .lic (format: RPA001-20260101-C59767B4D7E3)
$license = trim(file_get_contents($licenseFile));

// Kembalikan JSON
echo json_encode(["license_key" => $license]);
