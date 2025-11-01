<?php
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../helper/handlePdoError.php";

if (isset($_SESSION['username'])) {
    // if (isset($_SESSION['role']) && $_SESSION['role'] == "teknisi") {
    //     redirect("pages/schedule/");
    // }
    redirect("pages/dashboard.php");
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = trim($_POST['password']);

    if (empty($username) && empty($password)) {
        setAlert('warning', "Username atau password harus diisi", 'Silakan coba lagi!', 'danger', 'Coba Lagi');
        redirect("");
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM tbl_user WHERE username = :username LIMIT 1");
        $stmt->execute(([':username' => $username]));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['rule'] = $user['rule'];
            setAlert('success', "Login Berhasil", 'Selamat datang kembali!', 'success', 'OKe');
            redirect("pages/dashboard.php");
        } else {
            setAlert('error', "Login Gagal!", 'Username atau Password Salah!', 'danger', 'Coba Lagi');
            redirect("");
        }
    } catch (PDOException $e) {
        handlePdoError($e, "");
    }
}
