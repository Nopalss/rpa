<?php
// --- Bagian 1: Ambil Data ---
require_once __DIR__ . '/../../includes/config.php';
require __DIR__ . '/../../includes/clear_temp_session.php';

// Ambil parameter
$line = $_GET['line_id'] ?? '';
$application = $_GET['application_id'] ?? '';
$date = $_GET['date'] ?? ''; // Asumsi Y-m-d
$file_id = $_GET['file_id'] ?? '';
$header_id = $_GET['header_id'] ?? '';

if (empty($line) || empty($application) || empty($date) || empty($file_id) || empty($header_id)) {
    die("Error: Parameter line_id, application_id, date, dan file_id wajib diisi.");
}

// --- PERUBAHAN 1: Ambil Nama File (Ini masih sama) ---
$sql = "SELECT filename 
        FROM tbl_filename 
        WHERE file_id = :file_id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':file_id' => $file_id]);
$filename = $stmt->fetchColumn();

if ($filename === false) {
    die("Error: File ID " . htmlspecialchars($file_id) . " tidak ditemukan.");
}

// --- PERUBAHAN 2: Logika Ambil Header (Diganti total) ---
// Kita JOIN tbl_header dan tbl_header2 menggunakan record_no
$sqlHeader = "SELECT h1.*, h2.* FROM tbl_header h1
              JOIN tbl_header2 h2 ON h1.record_no = h2.record_no
              WHERE h1.record_no = :record_no 
              LIMIT 1";
$stmtHeader = $pdo->prepare($sqlHeader);
$stmtHeader->execute([':record_no' => $header_id]);
$headerData = $stmtHeader->fetch(PDO::FETCH_ASSOC);

if ($headerData === false) {
    die("Error: Header data untuk file ID " . htmlspecialchars($header_id) . " tidak ditemukan.");
}

// Buat 2 array:
// 1. $csv_header_row: Untuk nama kolom di CSV (e.g., "Nama Customer")
// 2. $data_column_keys: Untuk kunci data yang akan diambil (e.g., "data_1")
$csv_header_row = [];
$data_column_keys = [];

// Loop dari 1 sampai 380 (total kolom Anda)
for ($i = 1; $i <= 380; $i++) {
    $colKey = "column_$i";
    // Cek apakah kolom header ini ada dan tidak kosong
    if (isset($headerData[$colKey]) && !empty(trim($headerData[$colKey]))) {

        // Simpan nama header (e.g., "Nama Customer")
        $csv_header_row[] = $headerData[$colKey];

        // Simpan kunci kolom datanya (e.g., "data_1")
        $data_column_keys[] = "data_$i";
    }
}

if (empty($csv_header_row)) {
    die("Error: Tidak ada header valid yang ditemukan untuk file ini.");
}


// --- Bagian 3: Buat Output CSV (Header HTTP) ---
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"{$filename}_" . date("d-m-Y") . ".csv\"");

$output = fopen("php://output", "w");

// Tulis baris header yang sudah kita proses
fputcsv($output, $csv_header_row);

// --- PERUBAHAN 3: Logika Ambil Data (JOIN d1 dan d2) ---
// Kita JOIN tbl_data (d1) dan tbl_data2 (d2) menggunakan record_no
$sqlData = "SELECT d1.*, d2.* FROM tbl_data d1
            JOIN tbl_data2 d2 ON d1.record_no = d2.record_no
            WHERE d1.line_id = :line_id
            AND d1.application_id = :application_id 
            AND d1.date = :date 
            AND d1.file_id = :file_id
            AND d1.header_id = :header_id";

$stmtData = $pdo->prepare($sqlData);
$stmtData->execute([
    ':line_id' => $line,
    ':application_id' => $application,
    ':date' => $date,
    ':file_id' => $file_id,
    ':header_id' => $header_id,
]);


// --- PERUBAHAN 4: Loop Penulisan Data (Lebih Dinamis) ---
// Loop while-nya masih sama
while ($r = $stmtData->fetch(PDO::FETCH_ASSOC)) {
    $csv_data_row = [];

    // Loop BUKAN lagi 1-128, tapi pakai $data_column_keys
    // yang kita buat di PERUBAHAN 2
    foreach ($data_column_keys as $dataKey) {
        // $dataKey akan berisi "data_1", "data_5", "data_191", etc.
        // Ini memastikan data yang diambil sesuai dengan header yang tampil
        $csv_data_row[] = $r[$dataKey] ?? ''; // (Default ke string kosong jika data-nya null)
    }

    fputcsv($output, $csv_data_row);
}

// Tutup file output
fclose($output);

exit;
