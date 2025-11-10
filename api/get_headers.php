<?php
// api/get_headers.php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$file_id = $_POST['file_id'] ?? 0;
$response = ['success' => false, 'headers' => [], 'type' => 'type1'];

// Definisikan rentang kolom Anda
$TABLE1_MAX_COL = 190;
$TABLE2_START_COL = 191;
$TABLE2_MAX_COL = 380;

if ($file_id > 0) {
    try {

        // --- 1. Ambil Application ID ---
        $stmt_app = $pdo->prepare("SELECT application_id FROM tbl_filename WHERE file_id = :file_id");
        $stmt_app->execute([':file_id' => $file_id]);
        $application_id = $stmt_app->fetchColumn();

        // --- 2. Tentukan Tipe Tabel ---
        $table_type = 'type1';
        if ($application_id == 5) {
            $table_type = 'split';
        } elseif ($application_id == 6 || $application_id == 7) {
            $table_type = 'type2';
        }

        // --- 3. Buat Kueri UNION ALL berdasarkan Tipe Tabel ---
        $unionParts = [];

        // Jika type1 atau split, cari di tabel 1 (Kolom 1-190)
        if ($table_type === 'type1' || $table_type === 'split') {
            for ($i = 1; $i <= $TABLE1_MAX_COL; $i++) {
                $col = "column_" . $i;
                $unionParts[] = "
                    SELECT {$col} AS header_name 
                    FROM tbl_header 
                    WHERE file_id = :file_id 
                    AND {$col} IS NOT NULL AND TRIM({$col}) <> ''
                ";
            }
        }

        // Jika type2 atau split, cari di tabel 2 (Kolom 191-380)
        if ($table_type === 'type2' || $table_type === 'split') {
            for ($i = $TABLE2_START_COL; $i <= $TABLE2_MAX_COL; $i++) {
                $col = "column_" . $i;
                $unionParts[] = "
                    SELECT {$col} AS header_name 
                    FROM tbl_header2 
                    WHERE file_id = :file_id 
                    AND {$col} IS NOT NULL AND TRIM({$col}) <> ''
                ";
            }
        }

        // --- 5. Eksekusi Kueri ---
        if (empty($unionParts)) {
            $headers = []; // Tidak ada header yang ditemukan
        } else {
            $sql = implode(" UNION ALL ", $unionParts);
            $final_sql = "SELECT DISTINCT header_name FROM ({$sql}) AS combined_headers WHERE header_name IS NOT NULL AND header_name <> ''";

            $stmt = $pdo->prepare($final_sql);
            $stmt->execute([':file_id' => $file_id]);
            $headers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- 6. Set Response ---
        $response['success'] = true;
        $response['headers'] = $headers;
        $response['type'] = $table_type;
    } catch (Exception $e) {
        $response['message'] = 'Gagal memuat header: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'File ID tidak valid.';
}

echo json_encode($response);
