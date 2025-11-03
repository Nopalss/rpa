<?php
// api/get_headers.php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$file_id = $_POST['file_id'] ?? 0;
$response = ['success' => false, 'headers' => [], 'type' => 'type1'];

// JUMLAH KOLOM HEADER ANDA (sesuai dengan 190 yang Anda sebutkan)
$MAX_COLUMNS = 190;

if ($file_id > 0) {
    try {

        // --- 1. Ambil Application ID dari tbl_filename ---
        $stmt_app = $pdo->prepare("SELECT application_id FROM tbl_filename WHERE file_id = :file_id");
        $stmt_app->execute([':file_id' => $file_id]);
        $application_id = $stmt_app->fetchColumn();

        // --- 2. Tentukan Tipe Tabel (Logika Bisnis Anda) ---
        $table_type = 'type1';

        // LOGIKA SPLIT (Sesuai penjelasan Anda: App ID 5 menggunakan kedua tabel)
        if ($application_id == 5) {
            $table_type = 'split';
        }
        // LOGIKA TYPE2 (Jika ada App ID lain yang hanya menggunakan set tabel 2)
        elseif ($application_id == 6 || $application_id == 7) {
            $table_type = 'type2';
        }

        // --- 3. Tentukan Tabel Header yang Digunakan untuk Dropdown ---
        // Asumsi: Header untuk 'type1' dan 'split' diambil dari tbl_header.
        if ($table_type === 'type2') {
            $header_table = 'tbl_header2';
        } else {
            $header_table = 'tbl_header';
        }

        // --- 4. Buat Kueri UNION ALL yang BENAR ---
        $unionParts = [];
        for ($i = 1; $i <= $MAX_COLUMNS; $i++) {
            $col = "column_" . $i;

            // Perbaikan SQL: Ambil nama header string dari kolom, lalu di-alias ke 'header_name'
            // untuk diproses JavaScript.
            $unionParts[] = "
                SELECT 
                    {$col} AS header_name 
                FROM 
                    {$header_table} 
                WHERE 
                    file_id = :file_id 
                    AND {$col} IS NOT NULL 
                    AND TRIM({$col}) <> ''
            ";
        }

        $sql = implode(" UNION ALL ", $unionParts);

        // --- 5. Eksekusi Kueri ---
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':file_id' => $file_id]);
        $headers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- 6. Set Response ---
        $response['success'] = true;
        $response['headers'] = $headers;
        $response['type'] = $table_type; // Mengembalikan tipe tabel ke JS

    } catch (Exception $e) {
        $response['message'] = 'Gagal memuat header: ' . $e->getMessage();
        // Baris debug jika diperlukan:
        // $response['message'] = 'Gagal memuat header: ' . $e->getMessage() . " | SQL Error."; 
    }
} else {
    $response['message'] = 'File ID tidak valid.';
}

echo json_encode($response);
