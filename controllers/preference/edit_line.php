<?php
// 1. TENTUKAN HEADER SEBAGAI JSON
header('Content-Type: application/json');

// 2. MASUKKAN FILE KONFIGURASI DAN HELPER
require_once __DIR__ . '/../../includes/config.php'; // (Menyediakan $pdo dan memulai session)
require_once __DIR__ . '/../../helper/sanitize.php'; // (Menyediakan sanitize())

// 3. SIAPKAN RESPONS DEFAULT
$response = [
    'success' => false,
    'message' => 'Invalid request.'
];

// 4. HANYA PROSES JIKA METODENYA POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 5. BACA DATA JSON DARI FETCH (BUKAN $_POST)
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);

        // 6. VALIDASI DAN SANITASI INPUT
        // Ambil ID dan nama baru dari data JSON
        $line_id = $data['line_id'] ?? null;
        $line_name = sanitize($data['line_name'] ?? '');

        if (empty($line_id) || empty($line_name)) {
            // Jika input kosong, kirim pesan error
            throw new Exception('ID Line dan Nama Line tidak boleh kosong.');
        }

        // 7. PROSES DATABASE (UPDATE)
        $pdo->beginTransaction();

        // Asumsi Anda punya kolom 'update_by' dan 'update_date'
        // untuk melacak perubahan, mirip seperti 'create_by' di contoh Anda.
        $sql = "UPDATE tbl_line 
                SET 
                    line_name = :line_name, 
                    update_by = :update_by, 
                    update_date = NOW() 
                WHERE 
                    line_id = :line_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':line_name'  => $line_name,
            ':update_by'  => $_SESSION['username'], // Asumsi session sudah ada
            ':line_id'    => $line_id
        ]);

        // Cek apakah ada baris yang benar-benar ter-update
        $rowCount = $stmt->rowCount();

        $pdo->commit();

        // 8. KIRIM RESPONS SUKSES
        if ($rowCount > 0) {
            $response['success'] = true;
            $response['message'] = "Data line berhasil diperbarui!";
        } else {
            // Ini terjadi jika ID tidak ditemukan
            $response['message'] = "Data line tidak ditemukan (ID: $line_id) atau tidak ada perubahan.";
        }
    } catch (PDOException $e) {
        // 9. TANGANI ERROR DATABASE (PDO)
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = 'Database Error: ' . $e->getMessage();
    } catch (Exception $e) {
        // 10. TANGANI ERROR UMUM LAINNYA (misal: validasi)
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = $e->getMessage();
    }
}

// 11. KIRIM RESPONS AKHIR SEBAGAI JSON
echo json_encode($response);
exit;
