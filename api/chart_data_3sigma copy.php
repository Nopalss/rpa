<?php
// api/chart_data_3sigma.php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// --- 1. Ambil Parameter ---
$file_id = $_POST['file_id'] ?? 0;
$header_name = $_POST['header_name'] ?? '';
$table_type = $_POST['table_type'] ?? 'type1';
$line_id = $_POST['line_id'] ?? 0;

if (empty($file_id) || empty($header_name)) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
    exit;
}

// --- Definisikan Rentang Kolom ---
// note otomatis
$TABLE1_MAX_COL = 190;
$TABLE2_START_COL = 191;
$TABLE2_MAX_COL = 380;

// =============================================================
// === BAGIAN KRITIS: MENCARI NAMA KOLOM & TABEL YANG BENAR ===
// =============================================================

$data_column_name = ''; // Akan berisi misal: "data_10" atau "data_205"
$header_column = '';    // Akan berisi misal: "column_10" atau "column_205"
$data_table = '';       // Akan berisi "tbl_data" atau "tbl_data2"
$header_table = '';     // Akan berisi "tbl_header" atau "tbl_header2"
$found = false;

try {
    $stmtInfo = $pdo->prepare("
        SELECT 
            f.filename AS file_name,
            a.name AS application_name,
            l.line_name 
        FROM tbl_filename f
        LEFT JOIN tbl_application a ON f.application_id = a.id
        LEFT JOIN tbl_line l ON l.line_id = :line_id
        WHERE f.file_id = :file_id
        LIMIT 1
    ");
    $stmtInfo->execute([
        ':file_id' => $file_id,
        ':line_id' => $line_id,
    ]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    $file_name = $info['file_name'] ?? null;
    $application_name = $info['application_name'] ?? null;
    $line_name = $info['line_name'] ?? null;

    // ==============================================
    // 🔍 OPTIMASI: cari kolom dengan 1 query per tabel
    // ==============================================
    $tables_to_check = [];

    if ($table_type === 'type1' || $table_type === 'split') {
        $tables_to_check[] = ['tbl_header', 'tbl_data', 1, $TABLE1_MAX_COL];
    }
    if ($table_type === 'type2' || $table_type === 'split') {
        $tables_to_check[] = ['tbl_header2', 'tbl_data2', $TABLE2_START_COL, $TABLE2_MAX_COL];
    }

    foreach ($tables_to_check as [$header_tbl, $data_tbl, $start, $end]) {
        $stmtHeader = $pdo->prepare("SELECT * FROM {$header_tbl} WHERE file_id = :file_id LIMIT 1");
        $stmtHeader->execute([':file_id' => $file_id]);
        $row = $stmtHeader->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            foreach ($row as $col_name => $col_value) {
                if ($col_value === $header_name && preg_match('/^column_(\d+)$/', $col_name, $matches)) {
                    $col_index = (int) $matches[1];
                    $data_column_name = "data_{$col_index}";
                    $header_column = "column_{$col_index}";
                    $data_table = $data_tbl;
                    $header_table = $header_tbl;
                    $found = true;
                    break 2; // keluar dari kedua loop
                }
            }
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan saat mencari indeks kolom: ' . $e->getMessage()]);
    exit;
}


if (!$found || empty($data_column_name)) {
    echo json_encode(['success' => false, 'message' => "Kesalahan data: Tidak dapat menemukan kolom data untuk header '{$header_name}'."]);
    exit;
}

// --- 2. Bangun Kueri (Sekarang JAUH LEBIH SEDERHANA) ---

$clean_data_col = "REPLACE(TRIM(d.{$data_column_name}), ',', '.')";
$numeric_check = "d.{$data_column_name} IS NOT NULL AND d.{$data_column_name} <> '' AND {$clean_data_col} REGEXP '^-?([0-9]+\\.?[0-9]*|\\.[0-9]+)$'";

// Persiapan filter line_id (stratifikasi)
$stratification_sql = '';
$bind_params = [
    ':file_id' => $file_id,
    ':header_name' => $header_name
];
if (!empty($line_id)) {
    $stratification_sql = " AND d.line_id = :line_id ";
    $bind_params[':line_id'] = $line_id;
}

// Kueri SQL sekarang dinamis menargetkan tabel yang benar. Tidak perlu UNION ALL.
$sql = "
    SELECT CAST({$clean_data_col} AS DECIMAL(18, 4)) AS nilai 
    FROM {$data_table} d
    JOIN {$header_table} h ON d.header_id = h.record_no
    WHERE h.{$header_column} = :header_name AND d.file_id = :file_id 
    AND {$numeric_check}
    {$stratification_sql}
";

// --- 3. Eksekusi Kueri dan Pemrosesan Data ---
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bind_params);
    $raw_data = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan database saat mengambil data: ' . $e->getMessage()]);
    exit;
}

// --- 4. Perhitungan Statistik (Tidak ada perubahan) ---
if (count($raw_data) < 2) {
    // ... (sisa kode Anda dari sini SAMA SEPERTI SEBELUMNYA)
    echo json_encode(['success' => false, 'message' => 'Data tidak cukup atau tidak valid (ditemukan ' . count($raw_data) . ' nilai numerik).']);
    exit;
}

$raw_data = array_map('floatval', $raw_data);
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


$num_bins = max(5, min(50, ceil(1 + 3.322 * log10($n))));
$min_val = min($raw_data);
$max_val = max($raw_data);
$range = $max_val - $min_val;
if ($range == 0) {
    $bin_width = 1;
    $num_bins = 1;
    $min_val -= 0.5;
} else {
    $bin_width = $range / $num_bins;
    if ($bin_width == 0) $bin_width = 1;
}
$histogram_data = array_fill(0, $num_bins, 0);
$chart_series_data = [];

for ($i = 0; $i < $num_bins; $i++) {
    $bin_start = $min_val + ($i * $bin_width);
    $bin_end = $bin_start + $bin_width;
    $midpoint = ($bin_start + $bin_end) / 2; // Titik tengah bin

    $frequency = 0;
    foreach ($raw_data as $value) {
        if ($value >= $bin_start && ($i == $num_bins - 1 ? $value <= $bin_end : $value < $bin_end)) {
            $frequency++;
        }
    }

    // Format data untuk ApexCharts numeric: [Coordinate X, Value Y]
    // Kita gunakan midpoint sebagai posisi X
    $chart_series_data[] = [round($midpoint, 2), $frequency];
}

// --- 4.5 Deteksi data melewati batas kendali ---
$out_of_control = array_filter($raw_data, function ($v) use ($batas_atas, $batas_bawah) {
    return $v > $batas_atas || $v < $batas_bawah;
});

$is_out_of_control = count($out_of_control) > 0;
$count_out = count($out_of_control);
$max_out = $is_out_of_control ? max($out_of_control) : null;
$min_out = $is_out_of_control ? min($out_of_control) : null;

// --- 5. Output JSON ---
$output = [
    'success' => true,
    'series_data' => $chart_series_data, // <--- DATA BARU [x,y]
    'rata_rata' => $rata_rata,
    'batas_atas' => $batas_atas,
    'batas_bawah' => $batas_bawah,
    'standar_deviasi' => $standar_deviasi,
    'debug_data_column' => $data_column_name,
    'debug_data_table' => $data_table, // Debugging baru
    'debug_total_data' => $n,
    'debug_line_id' => $line_id,
    'line_name' => $line_name ?? null,
    'application_name' => $application_name ?? null,
    'file_name' => $file_name ?? null,
    'header_name' => $header_name ?? null,
    'out_of_control' => $is_out_of_control,
    'out_of_control_count' => $count_out,
    'max_out' => $max_out,
    'min_out' => $min_out,
];

echo json_encode($output);
