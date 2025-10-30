<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . "/../../helper/checkPassword.php";
require_once __DIR__ . "/../../helper/redirect.php";
require_once __DIR__ . "/../../helper/sanitize.php";
require_once __DIR__ . "/../../helper/handlePdoError.php";

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
        $stmt = $pdo->prepare("SELECT line_name FROM tbl_line WHERE line_id = :id");
        $stmt->execute([':id' => $id]);
        $targetFile = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetFile) {
            throw new Exception("File dengan ID tersebut tidak ditemukan.");
        }

        // Jalankan transaksi penghapusan
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM tbl_detail_line WHERE line_id = :id");
        $stmt->execute([':id' => $id]);

        $stmt = $pdo->prepare("DELETE FROM tbl_line WHERE line_id = :id");
        $stmt->execute([':id' => $id]);

        $pdo->commit();

        setAlert(
            'success',
            "Berhasil!",
            'Data berhasil dihapus.',
            'success',
            'Oke'
        );

        redirect("pages/preference/add_line/");
    } catch (PDOException $e) {
        // if ($pdo->inTransaction()) {
        //     $pdo->rollBack();
        // }
        // handlePdoError($e, "pages/preference/add_line/");
        echo $e;
    }
}
