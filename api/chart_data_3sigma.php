<?php
// api/chart_data_3sigma.php (streaming-safe version)
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$file_id = $_POST['file_id'] ?? 0;
$header_name = $_POST['header_name'] ?? '';
$table_type = $_POST['table_type'] ?? 'type1';
$line_id = $_POST['line_id'] ?? 0;

if (empty($file_id) || empty($header_name)) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
    exit;
}

$TABLE1_MAX_COL = 190;
$TABLE2_START_COL = 191;
$TABLE2_MAX_COL = 380;

$data_column_name = '';
$header_column = '';
$data_table = '';
$header_table = '';
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
    $stmtInfo->execute([':file_id' => $file_id, ':line_id' => $line_id]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
    $file_name = $info['file_name'] ?? null;
    $application_name = $info['application_name'] ?? null;
    $line_name = $info['line_name'] ?? null;

    $tables_to_check = [];
    if ($table_type === 'type1' || $table_type === 'split') {
        $tables_to_check[] = ['tbl_header', 'tbl_data', 1, $TABLE1_MAX_COL];
    }
    if ($table_type === 'type2' || $table_type === 'split') {
        $tables_to_check[] = ['tbl_header2', 'tbl_data2', $TABLE2_START_COL, $TABLE2_MAX_COL];
    }

    foreach ($tables_to_check as [$header_tbl, $data_tbl]) {
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
                    break 2;
                }
            }
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan saat mencari indeks kolom: ' . $e->getMessage()]);
    exit;
}

if (!$found) {
    echo json_encode(['success' => false, 'message' => "Kolom header '{$header_name}' tidak ditemukan."]);
    exit;
}

$clean_data_col = "REPLACE(TRIM(d.`{$data_column_name}`), ',', '.')";
$numeric_where = "{$clean_data_col} REGEXP '^-?([0-9]+\\.?[0-9]*|\\.[0-9]+)$'";

$stratification_sql = '';
$bind_params = [':file_id' => $file_id, ':header_name' => $header_name];
if (!empty($line_id)) {
    $stratification_sql = " AND d.line_id = :line_id ";
    $bind_params[':line_id'] = $line_id;
}

// 1️⃣ Ambil statistik dasar dulu (COUNT, AVG, STDDEV_POP, MIN, MAX)
$agg_sql = "
    SELECT 
      COUNT(*) AS cnt,
      AVG(CAST({$clean_data_col} AS DECIMAL(38,12))) AS mean,
      STDDEV_POP(CAST({$clean_data_col} AS DECIMAL(38,12))) AS stddev,
      MIN(CAST({$clean_data_col} AS DECIMAL(38,12))) AS min_val,
      MAX(CAST({$clean_data_col} AS DECIMAL(38,12))) AS max_val
    FROM {$data_table} d
    JOIN {$header_table} h ON d.header_id = h.record_no
    WHERE h.`{$header_column}` = :header_name
      AND d.file_id = :file_id
      AND {$numeric_where}
      {$stratification_sql}
";
$stmtAgg = $pdo->prepare($agg_sql);
$stmtAgg->execute($bind_params);
$stats = $stmtAgg->fetch(PDO::FETCH_ASSOC);
$n = (int)$stats['cnt'];

if ($n < 2) {
    echo json_encode(['success' => false, 'message' => "Data tidak cukup ($n nilai)."]);
    exit;
}

$rata_rata = (float)$stats['mean'];
$standar_deviasi = (float)$stats['stddev'];
$min_val = (float)$stats['min_val'];
$max_val = (float)$stats['max_val'];
$batas_atas = $rata_rata + (3 * $standar_deviasi);
$batas_bawah = $rata_rata - (3 * $standar_deviasi);

// 2️⃣ Siapkan variabel untuk histogram dan OOC detection
$num_bins = max(5, min(50, ceil(1 + 3.322 * log10($n))));
$range = $max_val - $min_val;
if ($range <= 0) {
    $bin_width = 1;
    $num_bins = 1;
    $min_val -= 0.5;
} else {
    $bin_width = $range / $num_bins;
}
$bins = array_fill(0, $num_bins, 0);
$out_of_control = [];
$count_out = 0;
$max_out = null;
$min_out = null;

// 3️⃣ STREAMING FETCH (tanpa fetchAll) — hemat memori
$value_sql = "
    SELECT CAST({$clean_data_col} AS DECIMAL(38,12)) AS nilai
    FROM {$data_table} d
    JOIN {$header_table} h ON d.header_id = h.record_no
    WHERE h.`{$header_column}` = :header_name
      AND d.file_id = :file_id
      AND {$numeric_where}
      {$stratification_sql}
";
$stmt = $pdo->prepare($value_sql);
$stmt->execute($bind_params);

// 4️⃣ Loop baris satu per satu
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $val = (float)$row['nilai'];

    // OOC detection langsung di loop
    if ($val > $batas_atas || $val < $batas_bawah) {
        $out_of_control[] = $val;
        $count_out++;
        if ($max_out === null || $val > $max_out) $max_out = $val;
        if ($min_out === null || $val < $min_out) $min_out = $val;
    }

    // Histogram binning langsung di stream
    if ($range == 0) {
        $bin_index = 0;
    } else {
        $bin_index = (int) floor(($val - $min_val) / $bin_width);
        if ($bin_index < 0) $bin_index = 0;
        if ($bin_index >= $num_bins) $bin_index = $num_bins - 1;
    }
    $bins[$bin_index]++;
}

// 5️⃣ Siapkan chart data
$chart_series_data = [];
for ($i = 0; $i < $num_bins; $i++) {
    $bin_start = $min_val + ($i * $bin_width);
    $bin_end = $bin_start + $bin_width;
    $mid = ($bin_start + $bin_end) / 2;
    $chart_series_data[] = [round($mid, 2), $bins[$i]];
}

// 6️⃣ Output JSON
$output = [
    'success' => true,
    'series_data' => $chart_series_data,
    'rata_rata' => $rata_rata,
    'batas_atas' => $batas_atas,
    'batas_bawah' => $batas_bawah,
    'standar_deviasi' => $standar_deviasi,
    'debug_data_column' => $data_column_name,
    'debug_data_table' => $data_table,
    'debug_total_data' => $n,
    'debug_line_id' => $line_id,
    'line_name' => $line_name ?? null,
    'application_name' => $application_name ?? null,
    'file_name' => $file_name ?? null,
    'header_name' => $header_name ?? null,
    'out_of_control' => $count_out > 0,
    'out_of_control_count' => $count_out,
    'max_out' => $max_out,
    'min_out' => $min_out,
];

echo json_encode($output);
