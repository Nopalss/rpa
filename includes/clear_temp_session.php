<?php

if (isset($_SESSION['form_add_csv'])) {
    try {
        if (isset($_SESSION['form_add_csv']['application_id'])) {
            $application_id = $_SESSION['form_add_csv']['application_id'];

            // Mulai transaksi
            $pdo->beginTransaction();

            // 1️ Ambil semua file_id berdasarkan application_id
            $stmt = $pdo->prepare("SELECT file_id FROM tbl_filename WHERE temp_id = ?");
            $stmt->execute([$application_id]);
            $fileIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // 2 Hapus semua header berdasarkan file_id yang terkait
            if (!empty($fileIds)) {
                $inQuery = implode(',', array_fill(0, count($fileIds), '?'));

                // Perbaikan 1: Hapus dari tbl_header1 (sesuai history kita)
                $stmtDelete1 = $pdo->prepare("DELETE FROM tbl_header WHERE file_id IN ($inQuery)");
                $stmtDelete1->execute($fileIds);

                // Perbaikan 2: Hapus dari tbl_header2
                $stmtDelete2 = $pdo->prepare("DELETE FROM tbl_header2 WHERE file_id IN ($inQuery)");
                $stmtDelete2->execute($fileIds);
            }

            // 3 Hapus semua file_name yang terhubung dengan application_id
            $stmtDeleteFiles = $pdo->prepare("DELETE FROM tbl_filename WHERE temp_id = ?");
            $stmtDeleteFiles->execute([$application_id]);

            // 4 Hapus juga data dari tbl_temp_application
            $stmtDeleteApp = $pdo->prepare("DELETE FROM tbl_temp_application WHERE id = ?");
            $stmtDeleteApp->execute([$application_id]);

            // 5 Commit transaksi HANYA JIKA SEMUA BERHASIL
            $pdo->commit();

            // 6 Hapus session HANYA JIKA COMMIT BERHASIL
            unset($_SESSION['form_add_csv']);
        }
    } catch (PDOException $e) {
        // 7 JIKA ADA ERROR, batalkan semua perubahan
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Opsional: Beri tahu user atau catat error
        // handlePdoError($e, 'halaman_sebelumnya.php');
        error_log("Gagal menghapus temp data: " . $e->getMessage());
    }
}
