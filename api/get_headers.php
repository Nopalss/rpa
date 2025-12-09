<?php
// api/get_headers.php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$file_id = $_POST['file_id'] ?? 0;
$response = ['success' => false, 'headers' => []];

// Batas kolom tiap tabel
$TABLE1_MAX_COL = 190;
$TABLE2_START_COL = 191;
$TABLE2_MAX_COL = 380;

if ($file_id > 0) {
    try {
        $unionParts = [];

        // --- Ambil dari tbl_header (kolom 1–190, type1) ---
        for ($i = 1; $i <= $TABLE1_MAX_COL; $i++) {
            $col = "column_" . $i;
            $unionParts[] = "
                SELECT {$col} AS header_name, 'type1' AS `table_type`
                FROM tbl_header 
                WHERE file_id = :file_id 
                AND {$col} IS NOT NULL AND TRIM({$col}) <> ''
            ";
        }

        // --- Ambil dari tbl_header2 (kolom 191–380, type2) ---
        for ($i = $TABLE2_START_COL; $i <= $TABLE2_MAX_COL; $i++) {
            $col = "column_" . $i;
            $unionParts[] = "
                SELECT {$col} AS header_name, 'type2' AS `table_type`
                FROM tbl_header2 
                WHERE file_id = :file_id 
                AND {$col} IS NOT NULL AND TRIM({$col}) <> ''
            ";
        }

        // --- Gabungkan semua hasil jadi satu query besar ---
        $sql = implode(" UNION ALL ", $unionParts);
        $final_sql = "
            SELECT DISTINCT header_name, `table_type`
            FROM ({$sql}) AS combined_headers 
            WHERE header_name IS NOT NULL AND header_name <> ''
        ";

        $stmt = $pdo->prepare($final_sql);
        $stmt->execute([':file_id' => $file_id]);
        $headers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['headers'] = $headers;
        $response['message'] = 'Header berhasil diambil.';
    } catch (Exception $e) {
        $response['message'] = 'Gagal memuat header: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'File ID tidak valid.';
}

echo json_encode($response);
