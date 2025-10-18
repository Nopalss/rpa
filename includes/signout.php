<?php
require_once __DIR__ . '/config.php';

session_unset();
$_SESSION['alert'] = [
    'icon' => 'success',
    'title' => 'Logout Berhasil',
    'text' => 'Anda telah keluar dari sistem. Silakan login kembali jika ingin mengakses aplikasi.',
    'button' => "Oke",
    'style' => "success"
];
header("Location: " . BASE_URL);
exit;
