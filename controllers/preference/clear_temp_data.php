<?php
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

try {
    // Cek apakah request method-nya POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }
    unset($_SESSION['form_add_csv']);
    // Ambil data dari POST
    $action = $_POST['action'] ?? '';
    $application_id = $_POST['application_id'] ?? null;

    if ($action !== 'delete_temp_data' || empty($application_id)) {
        throw new Exception("Invalid parameters");
    }

    // Mulai transaksi
    $pdo->beginTransaction();

    // 1️ Ambil semua file_id berdasarkan application_id
    $stmt = $pdo->prepare("SELECT file_id FROM tbl_filename WHERE temp_id = ?");
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
    $stmtDeleteFiles = $pdo->prepare("DELETE FROM tbl_filename WHERE temp_id = ?");
    $stmtDeleteFiles->execute([$application_id]);

    // 4 Hapus juga data dari tbl_application (jika datanya sementara)
    $stmtDeleteApp = $pdo->prepare("DELETE FROM tbl_temp_application WHERE id = ?");
    $stmtDeleteApp->execute([$application_id]);

    // 5 Commit transaksi
    $pdo->commit();

    unset($_SESSION['form_add_csv']);

    echo json_encode([
        "status" => "success",
        "message" => "Temporary data successfully deleted."
    ]);
} catch (PDOException $e) {
    // Rollback jika ada error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log error untuk debugging
    error_log("Error deleting temp data: " . $e->getMessage());

    echo json_encode([
        "status" => "error",
        "message" => "Failed to delete temporary data: " . $e->getMessage()
    ]);
}
