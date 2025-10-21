<?php
require_once __DIR__ . '/setAlert.php';
require_once __DIR__ . '/redirect.php';

/**
 * Tangani error PDO dan tampilkan alert user
 * Serta simpan pesan error dengan format: nama file | pesan error
 */
function handlePDOError(PDOException $e, string $redirectPath = "")
{
    // Dapatkan nama file dan pesan error
    $fileName = basename($e->getFile());
    $errorMessage = $e->getMessage();

    // Format pesan untuk disimpan ke log bawaan PHP
    $formatted = "[" . date('Y-m-d H:i:s') . "] $fileName | $errorMessage";

    // Kirim ke log bawaan PHP (bisa dicek di error_log)
    error_log($formatted);

    // Tampilkan alert ke user
    setAlert(
        'error',
        'Terjadi Kesalahan pada Sistem',
        'Kami sedang mengalami kendala. Silakan coba lagi beberapa saat.',
        'danger',
        'Kembali'
    );

    // Redirect ke halaman yang diinginkan
    redirect($redirectPath);
}
