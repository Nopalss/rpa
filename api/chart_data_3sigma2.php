<?php
// api/chart_data_3sigma_excel_clone.php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');
set_time_limit(0);

// Jaga presisi float agar setara Excel (IEEE-754 double)
ini_set('precision', 17);
ini_set('serialize_precision', -1);
date_default_timezone_set('Asia/Jakarta');


function get_production_date($cutoff_hour = 6, $cutoff_minute = 0)
{
    $now = new DateTime('now');

    $hour = (int)$now->format('H');
    $minute = (int)$now->format('i');

    if (
        $hour < $cutoff_hour ||
        ($hour === $cutoff_hour && $minute <= $cutoff_minute)
    ) {
        // sebelum cutoff → pakai tanggal kemarin
        $now->modify('-1 day');
    }

    return $now->format('Y-m-d');
}
$production_date = get_production_date(6, 0);


$file_id = $_POST['file_id'] ?? 0;
$header_name = $_POST['header_name'] ?? '';
$table_type = $_POST['table_type'] ?? 'type1';
$line_id = $_POST['line_id'] ?? 0;
$site_name = $_POST['site_name'] ?? null;
$application_id = $_POST['application_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// --- Parameter wajib sesuai Excel sheet
if (!isset($_POST['standard_upper'], $_POST['standard_lower'], $_POST['lower_boundary'], $_POST['interval_width'])) {
    echo json_encode(['success' => false, 'message' => "User harus mengisi standard_upper, standard_lower, lower_boundary, interval_width (sama seperti Excel)."]);
    exit;
}

$user_standard_upper = (float)$_POST['standard_upper'];
$user_standard_lower = (float)$_POST['standard_lower'];
$user_interval_width = (float)$_POST['interval_width'];
$user_lower_boundary = (float)$_POST['lower_boundary'];

if ($user_interval_width <= 0) {
    echo json_encode(['success' => false, 'message' => "interval_width harus > 0."]);
    exit;
}

if (empty($file_id) || empty($header_name)) {
    echo json_encode(['success' => false, 'message' => 'Parameter file_id / header_name tidak lengkap.']);
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
    // --- Cari kolom header & tabel data yang relevan
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
                    $col_index = (int)$matches[1];
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

// --- Helper untuk nilai numerik
$clean_data_col = "REPLACE(TRIM(d.`{$data_column_name}`), ',', '.')";
$numeric_where = "{$clean_data_col} REGEXP '^-?([0-9]+\\.?[0-9]*|\\.[0-9]+)$'";

$stratification_sql = '';
$bind_params = [':file_id' => $file_id, ':header_name' => $header_name];
$bind_params[':production_date'] = $production_date;

if (!empty($line_id)) {
    $stratification_sql = " AND d.line_id = :line_id ";
    $bind_params[':line_id'] = $line_id;
}
if (!empty($application_id)) {
    $stratification_sql .= " AND d.application_id = :application_id ";
    $bind_params[':application_id'] = $application_id;
}

// --- (1) Statistik dasar
try {
    $agg_sql = "
        SELECT 
          COUNT(*) AS cnt,
          AVG(CAST({$clean_data_col} AS DECIMAL(38,12))) AS mean,
          STDDEV_SAMP(CAST({$clean_data_col} AS DECIMAL(38,12))) AS stddev,
          MIN(CAST({$clean_data_col} AS DECIMAL(38,12))) AS min_val,
          MAX(CAST({$clean_data_col} AS DECIMAL(38,12))) AS max_val
        FROM {$data_table} d
        JOIN {$header_table} h ON d.header_id = h.record_no
        WHERE h.`{$header_column}` = :header_name
          AND d.file_id = :file_id
          AND {$numeric_where}
          {$stratification_sql}
           AND d.date = :production_date
    ";
    $stmtAgg = $pdo->prepare($agg_sql);
    $stmtAgg->execute($bind_params);
    $stats = $stmtAgg->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan saat mengambil statistik: ' . $e->getMessage()]);
    exit;
}

$n = (int)($stats['cnt'] ?? 0);
if ($n < 1) {
    echo json_encode(['success' => false, 'message' => "Data tidak cukup ($n nilai)."]);
    exit;
}

$rata_rata = (float)$stats['mean'];
$standar_deviasi = (float)$stats['stddev'];
$min_val = (float)$stats['min_val'];
$max_val = (float)$stats['max_val'];

// --- (2) Gunakan USL/LSL sesuai input user
$usl = $user_standard_upper;
$lsl = $user_standard_lower;

// --- (3) Buat batas interval (upper boundaries) PERSIS seperti Excel
$midpoints = [];
$edges = []; // ambang kumulatif Excel = MidPoint + IntervalWidth

for ($i = 0; $i <= 22; $i++) {
    // =((2*$C10)+$C11*(1+(E8*2)))/2
    $mid = ((2 * $user_lower_boundary) + $user_interval_width * (1 + ($i * 2))) / 2;
    $midpoints[$i] = $mid;
    $edges[$i] = $mid + $user_interval_width; // inilah yang dipakai Excel untuk COUNTIF & NORMDIST
}
$num_bins = count($midpoints);

// --- (4) Ambil semua nilai data dan hitung Observed Values (COUNTIF Excel)
$value_sql = "
    SELECT CAST({$clean_data_col} AS DECIMAL(38,12)) AS nilai
    FROM {$data_table} d
    JOIN {$header_table} h ON d.header_id = h.record_no
    WHERE h.`{$header_column}` = :header_name
      AND d.file_id = :file_id
      AND {$numeric_where}
      {$stratification_sql}
       AND d.date = :production_date
";
$stmt = $pdo->prepare($value_sql);
$stmt->execute($bind_params);

$out_of_control_vals = [];
$count_out = 0;
$max_out = null;
$min_out = null;
$limited_out_storage = 100;

$values = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $val = (float)$row['nilai'];
    $values[] = $val;

    if ($val > $usl || $val < $lsl) {
        $count_out++;
        if ($max_out === null || $val > $max_out) $max_out = $val;
        if ($min_out === null || $val < $min_out) $min_out = $val;
        if (count($out_of_control_vals) < $limited_out_storage) $out_of_control_vals[] = $val;
    }
}

// --- (5) Implementasi literal COUNTIF Excel
$bins = array_fill(0, $num_bins, 0);
for ($bi = 0; $bi < $num_bins; $bi++) {
    $upper_edge = $edges[$bi];                 // MidPoint(i) + Width
    $lower_edge = ($bi > 0) ? $edges[$bi - 1]  : null; // MidPoint(i-1) + Width

    $count_upper = 0;
    $count_lower = 0;

    foreach ($values as $v) {
        if ($v <= $upper_edge) $count_upper++;
        if ($lower_edge !== null && $v <= $lower_edge) $count_lower++;
    }

    // =COUNTIF(<=Mid(i)+W) - COUNTIF(<=Mid(i-1)+W)
    // untuk i=0: otomatis COUNTIF(<=Mid(0)+W)
    $bins[$bi] = $count_upper - $count_lower;
}

// --- (6) Build chart data & predicted values persis Excel
$chart_series_data = [];
$bin_labels = [];
$normal_curve_data = [];
$predicted_total = 0.0;

for ($bi = 0; $bi < $num_bins; $bi++) {
    $mid = $midpoints[$bi];
    $upper_edge = $edges[$bi];
    $lower_edge = ($bi > 0) ? $edges[$bi - 1] : null;

    // Observed (x=mid, y=freq)
    $chart_series_data[] = [$mid, (int)$bins[$bi]];

    // Label (opsional): pakai edge sebelumnya → edge sekarang
    $label_start = ($bi === 0) ? $user_lower_boundary : $edges[$bi - 1];
    $label_end   = $upper_edge;
    $label_start_str = rtrim(rtrim((string)$label_start, '0'), '.');
    $label_end_str   = rtrim(rtrim((string)$label_end, '0'), '.');
    $bin_labels[] = sprintf("%s - %s", $label_start_str, $label_end_str);

    // Predicted Excel:
    // Bin 0: NORMDIST(Mid0+W, mean, stdev, TRUE) * n
    // Bin i>=1: NORMDIST(Mid(i)+W) - NORMDIST(Mid(i-1)+W)  (lalu × n)
    if ($standar_deviasi > 0) {
        $cdf_upper = normal_cdf($upper_edge, $rata_rata, $standar_deviasi);
        $cdf_lower = ($lower_edge === null) ? 0.0 : normal_cdf($lower_edge, $rata_rata, $standar_deviasi);

        $predicted_freq = $n * ($cdf_upper - $cdf_lower);
        $predicted_total += $predicted_freq;
        $normal_curve_data[] = [$mid, $predicted_freq];
    } else {
        $normal_curve_data[] = [$mid, 0.0];
    }
}

// --- (7) Capability metrics
$cp = ($standar_deviasi > 0) ? ($usl - $lsl) / (6.0 * $standar_deviasi) : null;
$cpu = ($standar_deviasi > 0) ? ($usl - $rata_rata) / (3.0 * $standar_deviasi) : null;
$cpl = ($standar_deviasi > 0) ? ($rata_rata - $lsl) / (3.0 * $standar_deviasi) : null;
$cpk = ($cpu !== null && $cpl !== null) ? min($cpu, $cpl) : null;

// --- (8) Defect probabilities Excel-style
$eu = ($standar_deviasi > 0) ? (1.0 - normal_cdf($usl, $rata_rata, $standar_deviasi)) : 0.0;
$el = ($standar_deviasi > 0) ? normal_cdf($lsl, $rata_rata, $standar_deviasi) : 0.0;
$estimated_defect_rate = $eu + $el;

$percent_out = ($n > 0) ? ($count_out / $n) * 100.0 : 0.0;

// --- (9) Output
$output = [
    'success' => true,
    'series_data' => $chart_series_data,
    'bin_labels' => $bin_labels,
    'normal_curve' => $normal_curve_data,
    'rata_rata' => $rata_rata,
    'standar_deviasi' => $standar_deviasi,
    'usl' => $usl,
    'lsl' => $lsl,
    'cp' => $cp,
    'cpk' => $cpk,
    'cpu' => $cpu,
    'cpl' => $cpl,
    'eu' => $eu,
    'el' => $el,
    'estimated_defect_rate' => $estimated_defect_rate,
    'debug_data_column' => $data_column_name,
    'debug_total_data' => $n,
    'debug_bin_count' => $num_bins,
    'debug_predicted_total' => $predicted_total,
    'line_name' => $line_name ?? null,
    'application_name' => $application_name ?? null,
    'file_name' => $file_name ?? null,
    'header_name' => $header_name ?? null,
    'out_of_control' => $count_out > 0,
    'out_of_control_count' => $count_out,
    'out_of_control_percent' => $percent_out,
    'out_of_control_sample' => $out_of_control_vals,
    'out_of_control_min' => $min_out,
    'out_of_control_max' => $max_out,
    'debug_lower_boundary' => $user_lower_boundary,
    'debug_interval_width' => $user_interval_width,
    'debug_upper_boundaries' => $edges,
    'min_val' => $min_val,
    'max_val' => $max_val
];

// --- (10) Limit Cp/Cpk per site (optional)
$std_limit_cp = 0.85;
$std_limit_cpk = 0.85;

if ($user_id && $site_name) {
    $stmtLimit = $pdo->prepare("SELECT cp_limit, cpk_limit FROM tbl_user_settings WHERE user_id = :user_id AND site_name = :site_name LIMIT 1");
    $stmtLimit->execute([':user_id' => $user_id, ':site_name' => $site_name]);
    $rowLimit = $stmtLimit->fetch(PDO::FETCH_ASSOC);
    if ($rowLimit) {
        $std_limit_cp = (float)($rowLimit['cp_limit'] ?? 0.85);
        $std_limit_cpk = (float)($rowLimit['cpk_limit'] ?? 0.85);
    }
}

$cp_status = ($cp !== null && $cp >= $std_limit_cp) ? "OK" : "Over Spec";
$cpk_status = ($cpk !== null && $cpk >= $std_limit_cpk) ? "OK" : "Over Spec";
$output['cp_status'] = $cp_status;
$output['cpk_status'] = $cpk_status;
$output['std_limit_cp'] = $std_limit_cp;
$output['std_limit_cpk'] = $std_limit_cpk;

// --- (11) Range Y-axis
$max_freq = !empty($bins) ? max($bins) : 0;
$output['y_axis_min'] = 0;
$output['y_axis_max'] = $max_freq * 1.1;
$output['x_axis_min'] = min($lsl, $min_val) - abs($user_interval_width);
$output['x_axis_max'] = max($usl, $max_val) + abs($user_interval_width);
$output['midpoint_labels'] = array_map(fn($m) => round($m, 3), $midpoints);


echo json_encode($output, JSON_UNESCAPED_UNICODE);
exit;


// ------------------ Helper functions (Excel-accurate Normal CDF) ------------------
function normal_cdf($x, $mu, $sigma)
{
    if ($sigma <= 0.0) return ($x < $mu) ? 0.0 : 1.0;
    $z = ($x - $mu) / $sigma;
    return std_normal_cdf($z);
}

function std_normal_cdf($z)
{
    $b1 = 0.319381530;
    $b2 = -0.356563782;
    $b3 = 1.781477937;
    $b4 = -1.821255978;
    $b5 = 1.330274429;
    $p = 0.2316419;
    $c = 0.39894228; // 1/sqrt(2π)

    if ($z >= 0.0) {
        $t = 1.0 / (1.0 + $p * $z);
        return 1.0 - $c * exp(-$z * $z / 2.0) * $t *
            ($b1 + $t * ($b2 + $t * ($b3 + $t * ($b4 + $t * $b5))));
    } else {
        return 1.0 - std_normal_cdf(-$z);
    }
}
