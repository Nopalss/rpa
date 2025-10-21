<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../helper/sanitize.php';
require_once __DIR__ . '/../../../helper/handlePdoError.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Ambil data POST ---
    $id       = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : null;
    $passwordRaw = isset($_POST['password']) ? trim($_POST['password']) : null;
    $role     = isset($_POST['role']) ? sanitize($_POST['role']) : null;

    // --- Validasi field wajib ---
    $required = compact('username', 'role');
    foreach ($required as $field => $value) {
        if (empty($value)) {
            setAlert(
                'error',
                "Oops!",
                "Field <b>$field</b> tidak boleh kosong.",
                'danger',
                'Coba Lagi'
            );
            redirect("pages/setting/user/edit.php?id=" . $id);
        }
    }

    // --- Pastikan user ada ---
    $check = $pdo->prepare("SELECT 1 FROM tbl_user WHERE user_id = :id");
    $check->execute([':id' => $id]);
    if (!$check->fetchColumn()) {
        setAlert(
            'error',
            "Oops!",
            "Data user tidak ditemukan.",
            'danger',
            'Kembali'
        );
        redirect("pages/setting/user/");
    }

    // --- Hash password hanya jika diisi ---
    $params = [
        ':username' => $username,
        ':role'     => $role,
        ':id'       => $id
    ];

    $sql = "UPDATE tbl_user 
            SET username = :username, rule = :role";

    if (!empty($passwordRaw)) {
        $sql .= ", password = :password";
        $params[':password'] = password_hash($passwordRaw, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE user_id = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        setAlert(
            'success',
            "Berhasil!",
            "Data user berhasil diperbarui.",
            'success',
            'Oke'
        );
    } catch (PDOException $e) {
        handlePdoError($e, "pages/setting/user");
    }
}

redirect("pages/setting/user/");
