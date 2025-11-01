<?php
// session_start() sudah ada di dalam config.php
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../helper/handlePdoError.php";

// 1. Cek jika sudah login, lempar ke dashboard
if (isset($_SESSION['username'])) {
    redirect("pages/dashboard.php");
}

// 2. Hanya jalankan jika ada kiriman form POST
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = trim($_POST['password']);

    // PERBAIKAN 1: Logika divalidasi menggunakan 'ATAU' (||)
    if (empty($username) || empty($password)) {
        setAlert('warning', "Username dan password harus diisi", 'Silakan coba lagi!', 'danger', 'Coba Lagi');
        redirect("");
    }

    try {
        // 3. Ambil data user (sudah aman dengan prepared statement)
        $stmt = $pdo->prepare("SELECT * FROM tbl_user WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 4. Verifikasi user dan password (sudah aman dengan password_verify)
        if ($user && password_verify($password, $user['password'])) {

            // PERBAIKAN 2 (KEAMANAN): Buat session ID baru untuk cegah Session Fixation
            session_regenerate_id(true);

            // 5. Simpan data ke session
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['rule'] = $user['rule'];

            setAlert('success', "Login Berhasil", 'Selamat datang kembali!', 'success', 'OKe');
            redirect("pages/dashboard.php");
        } else {
            // 6. Jika user tidak ada atau password salah
            setAlert('error', "Login Gagal!", 'Username atau Password Salah!', 'danger', 'Coba Lagi');
            redirect("");
        }
    } catch (PDOException $e) {
        handlePdoError($e, "");
    }
}
