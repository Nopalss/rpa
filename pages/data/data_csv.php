<?php
// --- Bagian 1: Ambil Data ---
require_once __DIR__ . '/../../includes/config.php';

$line = $_GET['line_id'] ?? '';
$application = $_GET['application_id'] ?? '';
$date = $_GET['date'] ?? ''; // Asumsi YYYY-MM-DD
$file_id = $_GET['file_id'] ?? '';


$sql = "SELECT filename 
        FROM tbl_filename 
        WHERE file_id = :file_id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':file_id' => $file_id]);
$filename = $stmt->fetchColumn();

if ($filename === false) {
    die("Error: File ID " . htmlspecialchars($file_id) . " tidak ditemukan.");
}

$unionParts = [];
for ($i = 1; $i <= 128; $i++) {
    $col = "column_$i";
    $unionParts[] = "SELECT $col AS header_name 
                     FROM tbl_header 
                     WHERE file_id = :file_id 
                       AND $col IS NOT NULL 
                       AND TRIM($col) <> ''";
}
$sql = implode(" UNION ALL ", $unionParts);
$stmtHeader = $pdo->prepare($sql);
$stmtHeader->execute([':file_id' => $file_id]);
$headers = $stmtHeader->fetchAll(PDO::FETCH_ASSOC);


header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"{$filename}_" . date("d-m-Y") . ".csv\"");


// --- Bagian 3: Buat Output CSV ---
$output = fopen("php://output", "w");

$header_row = [];
// Tulis baris header
foreach ($headers as $h) {
    $header_row[] = $h['header_name'];
}
fputcsv($output, $header_row);


$sql = "SELECT * FROM tbl_data 
        WHERE line_id = :line_id
        AND application_id = :application_id 
        AND date = :date 
        AND file_id = :file_id";
$stmtData = $pdo->prepare($sql);
$stmtData->execute([
    ':line_id' => $line,
    ':application_id' => $application,
    ':date' => $date,
    ':file_id' => $file_id,
]);

while ($r = $stmtData->fetch(PDO::FETCH_ASSOC)) {
    $data_row = [];

    for ($i = 1; $i <= 128; $i++) {
        $data_row[] = $r["data_$i"];
    }

    fputcsv($output, $data_row);
}

// Tutup file output
fclose($output);

exit;
