<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . "/../../../helper/checkPassword.php";
require_once __DIR__ . "/../../../helper/redirect.php";
require_once __DIR__ . "/../../../helper/sanitize.php";
require_once __DIR__ . "/../../../helper/handlePdoError.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = isset($_POST['id']) ? sanitize($_POST['id']) : null;
    $username = $_SESSION['username'] ?? null;
    $password = trim($_POST['password'] ?? '');

    // Validasi dasar
    if (empty($id) || empty($password) || empty($username)) {
        setAlert(
            'warning',
            "Oops!",
            'Data tidak lengkap.',
            'warning',
            'Coba Lagi'
        );
        return redirect("pages/setting/user/");
    }

    // Cek password user
    $user = checkLogin($pdo, $username, $password);
    if (!$user) {
        setAlert(
            'error',
            "Oops!",
            'Password salah.',
            'danger',
            'Coba Lagi'
        );
        return redirect("pages/setting/user/");
    }

    try {
        // Cek apakah user dengan ID tersebut ada
        $stmt = $pdo->prepare("SELECT rule FROM tbl_user WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $id]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            throw new Exception("User dengan ID tersebut tidak ditemukan.");
        }

        // Jalankan transaksi penghapusan
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM tbl_user WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $id]);

        $pdo->commit();

        setAlert(
            'success',
            "Berhasil!",
            'User berhasil dihapus.',
            'success',
            'Oke'
        );

        redirect("pages/setting/user/");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo $e;
        // handlePdoError($e, "pages/setting/user");
    }
}
