<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../helper/sanitize.php';
require_once __DIR__ . '/../../../helper/handlePdoError.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Ambil data POST ---
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : null;
    $passwordRaw = isset($_POST['password']) ? trim($_POST['password']) : null; // 
    $role     = isset($_POST['role']) ? sanitize($_POST['role']) : null;

    // --- Cek Username Exist ---
    $check = $pdo->prepare("SELECT 1 FROM tbl_user WHERE username = :username");
    $check->execute([':username' => $username]);
    if ($check->fetchColumn()) {
        setAlert(
            'error',
            "Oops!",
            'Username sudah digunakan',
            'danger',
            'Coba Lagi'
        );
        redirect("pages/setting/user/create.php");
    }

    // --- Validasi field wajib ---
    $required = compact('username', 'password', 'role');
    foreach ($required as $field => $value) {
        if (empty($value)) {
            setAlert(
                'error',
                "Oops!",
                'Field <b>$field</b> tidak boleh kosong.',
                'danger',
                'Coba Lagi'
            );
            redirect("pages/setting/user/create.php");
        }
    }

    // --- Hash Password ---
    function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }
    $password = hashPassword($passwordRaw);

    try {
        // Insert ke tabel tbl_user
        $sql = "INSERT INTO tbl_user (username,password,rule)
                VALUES (:username,:password,:role)";
        $stmt = $pdo->prepare($sql);
        $user_success = $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':role'     => $role
        ]);

        setAlert(
            'success',
            "Selamat!",
            'Pembuatan Akun Sukses',
            'success',
            'Oke'
        );
    } catch (PDOException $e) {
        handlePdoError($e, "pages/setting/user");
    }
}
redirect("pages/setting/user/");
