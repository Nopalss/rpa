<?php
require_once __DIR__ . '/setAlert.php';
require_once __DIR__ . '/redirect.php';

/**
 * Tangani error PDO dan tampilkan alert user
 * Serta simpan pesan error dengan format: nama file | pesan error
 */
function handlePDOError($e, string $redirectPath = "")
{
    // Tampilkan alert ke user
    setAlert(
        'error',
        'Terjadi Kesalahan pada Sistem',
        $e,
        'danger',
        'Kembali'
    );

    // Redirect ke halaman yang diinginkan
    redirect($redirectPath);
}
