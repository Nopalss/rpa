<?php
// api/chart_data_3sigma.php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// --- 1. Ambil Parameter ---
$file_id = $_POST['file_id'] ?? 0;
$header_name = $_POST['header_name'] ?? '';
$table_type = $_POST['table_type'] ?? 'type1';

if (empty($file_id) || empty($header_name)) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
    exit;
}

// =============================================================
// === BAGIAN KRITIS: MENCARI NAMA KOLOM DATA YANG BENAR ===
// =============================================================

$data_column_prefix = 'data_';
$MAX_COLUMNS = 190;
$data_column_name = '';

// Tentukan tabel header mana yang akan dicari
$header_table = ($table_type === 'type2') ? 'tbl_header2' : 'tbl_header';

try {
    // Iterasi untuk mencari di kolom mana ($header_name) berada
    for ($i = 1; $i <= $MAX_COLUMNS; $i++) {
        $col_header = "column_" . $i;

        $stmt_index = $pdo->prepare("
            SELECT COUNT(*) 
            FROM {$header_table} 
            WHERE {$col_header} = :header_name AND file_id = :file_id
        ");
        $stmt_index->execute([':header_name' => $header_name, ':file_id' => $file_id]);

        if ($stmt_index->fetchColumn() > 0) {
            $data_column_name = $data_column_prefix . $i;
            break;
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan saat mencari indeks kolom data: ' . $e->getMessage()]);
    exit;
}

if (empty($data_column_name)) {
    echo json_encode(['success' => false, 'message' => "Kesalahan data: Tidak dapat menemukan kolom data untuk header '{$header_name}'."]);
    exit;
}

// --- 2. Bangun Kueri Berdasarkan Tipe Tabel dan Kolom Data Dinamis ---

$header_column = "column_" . str_replace($data_column_prefix, '', $data_column_name);
$data_column = $data_column_name;

// Fungsi pembersih data: Ganti koma dengan titik, hilangkan spasi.
$clean_data_col = "REPLACE(TRIM(d.{$data_column}), ',', '.')";
$clean_data_col_2 = "REPLACE(TRIM(d2.{$data_column}), ',', '.')";

if ($table_type === 'split') {
    // UNION ALL - Menggunakan JOIN yang benar dan membersihkan data
    $sql = "
        (
            SELECT CAST({$clean_data_col} AS DECIMAL(18, 4)) AS nilai 
            FROM tbl_data d
            JOIN tbl_header h ON d.header_id = h.record_no  /* <--- JOIN BENAR */
            WHERE h.{$header_column} = :header_name AND d.file_id = :file_id 
            AND {$clean_data_col} IS NOT NULL AND {$clean_data_col} <> ''
        )
        UNION ALL
        (
            SELECT CAST({$clean_data_col_2} AS DECIMAL(18, 4)) AS nilai 
            FROM tbl_data2 d2
            JOIN tbl_header2 h2 ON d2.header_id = h2.record_no  /* <--- JOIN BENAR */
            WHERE h2.{$header_column} = :header_name AND d2.file_id = :file_id 
            AND {$clean_data_col_2} IS NOT NULL AND {$clean_data_col_2} <> ''
        )
    ";
} elseif ($table_type === 'type2') {
    // type2 - Menggunakan JOIN yang benar dan membersihkan data
    $sql = "
        SELECT CAST({$clean_data_col} AS DECIMAL(18, 4)) AS nilai 
        FROM tbl_data2 d
        JOIN tbl_header2 h ON d.header_id = h.record_no  /* <--- JOIN BENAR */
        WHERE h.{$header_column} = :header_name AND d.file_id = :file_id 
        AND {$clean_data_col} IS NOT NULL AND {$clean_data_col} <> ''
    ";
} else { // type1 (Default)
    // type1 - Menggunakan JOIN yang benar dan membersihkan data
    $sql = "
        SELECT CAST({$clean_data_col} AS DECIMAL(18, 4)) AS nilai 
        FROM tbl_data d
        JOIN tbl_header h ON d.header_id = h.record_no  /* <--- JOIN BENAR */
        WHERE h.{$header_column} = :header_name AND d.file_id = :file_id 
        AND {$clean_data_col} IS NOT NULL AND {$clean_data_col} <> ''
    ";
}

// --- 3. Eksekusi Kueri dan Pemrosesan Data ---
try {
    $stmt = $pdo->prepare($sql);
    // Kita bind semua placeholder yang diperlukan
    $stmt->execute([':file_id' => $file_id, ':header_name' => $header_name]);
    $raw_data = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan database saat mengambil data: ' . $e->getMessage()]);
    exit;
}

// --- 4. Perhitungan Statistik (Rata-rata, StDev, 3-Sigma, Binning) ---
if (count($raw_data) < 2) {
    echo json_encode(['success' => false, 'message' => 'Data tidak cukup atau tidak valid (ditemukan ' . count($raw_data) . ' nilai numerik).']);
    exit;
}

$n = count($raw_data);
$sum = array_sum($raw_data);
$rata_rata = $sum / $n;
$variance = 0.0;
foreach ($raw_data as $value) {
    $variance += pow($value - $rata_rata, 2);
}
$standar_deviasi = sqrt($variance / $n);
$batas_atas = $rata_rata + (3 * $standar_deviasi);
$batas_bawah = $rata_rata - (3 * $standar_deviasi);

$num_bins = ceil(2 * pow($n, 1 / 3));
$min_val = min($raw_data);
$max_val = max($raw_data);
$range = $max_val - $min_val;

if ($range == 0) {
    $bin_width = 1;
    $num_bins = 1;
    $min_val -= 0.5;
    $range = 1;
} else {
    $bin_width = $range / $num_bins;
}

$histogram_data = [];
$labels = [];
for ($i = 0; $i < $num_bins; $i++) {
    $histogram_data[$i] = 0;
    $bin_start = $min_val + ($i * $bin_width);
    $bin_end = $bin_start + $bin_width;
    $labels[] = number_format($bin_start, 2) . ' - ' . number_format($bin_end, 2);
}

foreach ($raw_data as $value) {
    $bin_index = floor(($value - $min_val) / $bin_width);
    if ($bin_index == $num_bins) {
        $bin_index--;
    }
    if (isset($histogram_data[$bin_index])) {
        $histogram_data[$bin_index]++;
    }
}

// --- 5. Output JSON ---
$output = [
    'success' => true,
    'series' => array_values($histogram_data),
    'labels' => $labels,
    'rata_rata' => $rata_rata,
    'batas_atas' => $batas_atas,
    'batas_bawah' => $batas_bawah,
    'standar_deviasi' => $standar_deviasi
];

echo json_encode($output);
