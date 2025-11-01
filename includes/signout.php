<?php
// 1. Memuat file config (yang di dalamnya sudah ada session_start())
require_once __DIR__ . '/config.php';

// 2. Hapus semua variabel dari sesi
session_unset();

// 3. Hancurkan sesi lama (membuat session ID lama tidak valid)
session_destroy();

// 4. TRIK PENTING: Mulai sesi baru yang bersih HANYA untuk membawa alert
session_start();

// 5. Atur alert di sesi yang BARU
$_SESSION['alert'] = [
    'icon' => 'success',
    'title' => 'Logout Berhasil',
    'text' => 'Anda telah keluar dari sistem. Silakan login kembali jika ingin mengakses aplikasi.',
    'button' => "Oke",
    'style' => "success"
];

// 6. Redirect ke halaman login
header("Location: " . BASE_URL);
exit;
