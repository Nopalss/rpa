<?php
// 1. TENTUKAN HEADER SEBAGAI JSON
// Ini sangat penting untuk respons AJAX
header('Content-Type: application/json');

// 2. MASUKKAN FILE KONFIGURASI DAN HELPER
require_once __DIR__ . '/../../includes/config.php'; // (Menyediakan $pdo dan memulai session)
require_once __DIR__ . '/../../helper/sanitize.php'; // (Menyediakan sanitize())
// Kita tidak memerlukan redirect.php karena ini adalah API
// Kita akan menangani error PDO secara manual untuk JSON

// 3. SIAPKAN RESPONS DEFAULT
$response = [
    'success' => false,
    'message' => 'Invalid request.'
];

// 4. HANYA PROSES JIKA METODENYA POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 5. BACA DATA JSON DARI FETCH
        // Bukan dari $_POST, tapi dari 'body' request
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);

        // 6. VALIDASI DAN SANITASI INPUT
        $line_name = sanitize($data['line_name'] ?? '');

        if (empty($line_name)) {
            // Jika input kosong, kirim pesan error
            $response['message'] = 'Nama line (line_name) tidak boleh kosong.';
        } else {
            // 7. PROSES DATABASE (mengikuti gaya example Anda)
            $pdo->beginTransaction();

            // Asumsi tabel Anda adalah 'tbl_line' dan Anda ingin mencatat 'create_by'
            // seperti di contoh Anda.
            $sql = "INSERT INTO tbl_line (line_name, create_by) 
                    VALUES (:line_name, :create_by)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':line_name' => $line_name,
                ':create_by' => $_SESSION['username'] // Asumsi config.php sudah session_start()
            ]);

            $pdo->commit();

            // 8. KIRIM RESPONS SUKSES
            $response['success'] = true;
            $response['message'] = "Data line '$line_name' berhasil ditambahkan!";
        }
    } catch (PDOException $e) {
        // 9. TANGANI ERROR DATABASE (PDO)
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Kirim error sebagai JSON, jangan panggil handlePdoError()
        // karena itu mungkin akan me-redirect, yang tidak kita inginkan.
        $response['message'] = 'Database Error: ' . $e->getMessage();
        // Untuk produksi, Anda mungkin ingin pesan yang lebih umum:
        // $response['message'] = 'Terjadi kesalahan pada server saat menyimpan data.';

    } catch (Exception $e) {
        // 10. TANGANI ERROR UMUM LAINNYA
        $response['message'] = $e->getMessage();
    }
}

// 11. KIRIM RESPONS AKHIR SEBAGAI JSON
echo json_encode($response);
exit;
