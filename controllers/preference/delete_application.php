<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . "/../../helper/checkPassword.php";
require_once __DIR__ . "/../../helper/redirect.php";
require_once __DIR__ . "/../../helper/sanitize.php";
require_once __DIR__ . "/../../helper/handlePdoError.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id       = isset($_POST['id']) ? sanitize($_POST['id']) : null;
    $username = $_SESSION['username'] ?? null;
    $password = trim($_POST['password'] ?? '');

    // Validasi dasar
    if (empty($application_id) || empty($password) || empty($username)) {
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
        // $stmt = $pdo->prepare("SELECT filename FROM tbl_filename WHERE file_id = :id");
        // $stmt->execute([':id' => $id]);
        // $targetFile = $stmt->fetch(PDO::FETCH_ASSOC);

        // if (!$targetFile) {
        //     throw new Exception("File dengan ID tersebut tidak ditemukan.");
        // }

        // Jalankan transaksi penghapusan
        $pdo->beginTransaction();
        // 1️ Ambil semua file_id berdasarkan application_id
        $stmt = $pdo->prepare("SELECT file_id FROM tbl_filename WHERE application_id = ?");
        $stmt->execute([$application_id]);
        $fileIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 2 Hapus semua header berdasarkan file_id yang terkait
        if (!empty($fileIds)) {
            $inQuery = implode(',', array_fill(0, count($fileIds), '?'));
            $stmtDeleteHeaders = $pdo->prepare("DELETE FROM tbl_header WHERE file_id IN ($inQuery)");
            $stmtDeleteHeaders->execute($fileIds);
            $stmtDeleteHeaders = $pdo->prepare("DELETE FROM tbl_header2 WHERE file_id IN ($inQuery)");
            $stmtDeleteHeaders->execute($fileIds);
        }

        // 3 Hapus semua file_name yang terhubung dengan application_id
        $stmtDeleteFiles = $pdo->prepare("DELETE FROM tbl_filename WHERE application_id = ?");
        $stmtDeleteFiles->execute([$application_id]);

        // 4 Hapus juga data dari tbl_application (jika datanya sementara)
        $stmtDeleteApp = $pdo->prepare("DELETE FROM tbl_application WHERE id = ?");
        $stmtDeleteApp->execute([$application_id]);

        $pdo->commit();

        setAlert(
            'success',
            "Berhasil!",
            'Data berhasil dihapus.',
            'success',
            'Oke'
        );

        unset($_SESSION['form_add_csv']);

        redirect("pages/preference/model_setting/");
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        handlePdoError($e, "pages/preference/model_setting/create.php");
    }
}
