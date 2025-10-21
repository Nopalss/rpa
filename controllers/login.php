<?php
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../helper/setAlert.php";
require_once __DIR__ . "/../helper/redirect.php";
if (isset($_SESSION['username'])) {
    // if (isset($_SESSION['role']) && $_SESSION['role'] == "teknisi") {
    //     redirect("pages/schedule/");
    // }
    redirect("pages/dashboard.php");
}


if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = trim($_POST['password']);
    if (isset($users[$username]) && $password === $users[$username]) {
        $_SESSION['username'] = $username;
        setAlert('success', "Login Berhasil", 'Selamat datang kembali!', 'success', 'OKe');
        redirect("pages/dashboard.php");
    } else {
        setAlert('error', "Login Gagal!", 'Username atau Password Salah!', 'danger', 'Coba Lagi');
        redirect("");
    }
    //          setAlert('warning', "Username atau password harus diisi", 'Silakan coba lagi!', 'danger', 'Coba Lagi');
    //         redirect("");
    //     }
    //     try {
    //         // ambil data username
    //         $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    //         $stmt->execute([':username' => $username]);
    //         $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //         // cek password 
    //         if ($user && password_verify($password, $user['password'])) {
    //             // cek role
    //             switch ($user['role']) {
    //                 case 'admin':
    //                     $table = 'admin';
    //                     $id_col = 'admin_id';
    //                     $redirect_path = "pages/dashboard.php";
    //                     break;
    //                 case 'teknisi':
    //                     $table = 'technician';
    //                     $id_col = 'tech_id';
    //                     $redirect_path = "pages/schedule/";
    //                     break;
    //                 default:
    //                     throw new Exception("Role tidak dikenali");
    //             }

    //             $stmt = $pdo->prepare("SELECT * FROM $table WHERE username = :username LIMIT 1");
    //             $stmt->execute([':username' => $username]);
    //             $karyawan = $stmt->fetch(PDO::FETCH_ASSOC);

    //             $_SESSION['id_karyawan'] = $karyawan[$id_col];
    //             $_SESSION['username'] = $user['username'];
    //             $_SESSION['role'] = $user['role'];
    //             $_SESSION['name'] = $karyawan['name'];

    //             $_SESSION['alert'] = [
    //                 'icon' => 'success',
    //                 'title' => 'Login Berhasil',
    //                 'text' => 'Selamat datang kembali!',
    //                 'style' => 'success'
    //             ];

    //             redirect($redirect_path);
    //         } else {
    //             $_SESSION['alert'] = [
    //                 'icon' => 'error',
    //                 'title' => 'Login Gagal',
    //                 'text' => 'Username atau password salah!',
    //                 'style' => 'danger'
    //             ];
    //         }
    //     } catch (PDOException $e) {
    //         $_SESSION['alert'] = [
    //             'icon' => 'error',
    //             'title' => 'Terjadi Kesalahan',
    //             'text' => 'Silakan coba lagi nanti.',
    //             'style' => 'danger'
    //         ];
    //         redirect("");
    //     }
}
