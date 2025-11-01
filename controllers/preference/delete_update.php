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
    $application_id = $_SESSION['temp_id'];

    // Validasi dasar
    if (empty($id) || empty($password) || empty($username)) {
        setAlert(
            'warning',
            "Oops!",
            'Data tidak lengkap.',
            'warning',
            'Coba Lagi'
        );
        return redirect("pages/preference/model_setting/update.php?id=$application_id");
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
        return redirect("pages/preference/model_setting/update.php?id=$application_id");
    }

    try {
        // Cek apakah user dengan ID tersebut ada
        $stmt = $pdo->prepare("SELECT filename, application_id, temp_id FROM tbl_filename WHERE file_id = :id");
        $stmt->execute([':id' => $id]);
        $targetFile = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$targetFile) {
            redirect("pages/preference/model_setting/update.php?id=$application_id");
        }

        // Jalankan transaksi penghapusan
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM tbl_header WHERE file_id = :id");
        $stmt->execute([':id' => $id]);
        $stmt = $pdo->prepare("DELETE FROM tbl_header2 WHERE file_id = :id");
        $stmt->execute([':id' => $id]);

        $stmt = $pdo->prepare("DELETE FROM tbl_filename WHERE file_id = :id");
        $stmt->execute([':id' => $id]);

        $pdo->commit();

        setAlert(
            'success',
            "Berhasil!",
            'Data berhasil dihapus.',
            'success',
            'Oke'
        );

        redirect("pages/preference/model_setting/update.php?id=$application_id");
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        handlePdoError($e, "pages/preference/model_setting/update.php?id=$application_id");
    }
}
