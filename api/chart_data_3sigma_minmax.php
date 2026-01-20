<?php
// api/chart_data_3sigma_minmax.php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');
set_time_limit(0);

// ======================================================
// Precision & Timezone
// ======================================================
ini_set('precision', 17);
ini_set('serialize_precision', -1);
date_default_timezone_set('Asia/Jakarta');

// ======================================================
// Production Date
// ======================================================
function get_production_date($cutoff_hour = 6, $cutoff_minute = 0)
{
    $now = new DateTime();
    $h = (int)$now->format('H');
    $m = (int)$now->format('i');

    if ($h < $cutoff_hour || ($h === $cutoff_hour && $m <= $cutoff_minute)) {
        $now->modify('-1 day');
    }
    return $now->format('Y-m-d');
}
$production_date = get_production_date(6, 0);

// ======================================================
// Input
// ======================================================
$file_id        = (int)($_POST['file_id'] ?? 0);
$line_id        = (int)($_POST['line_id'] ?? 0);
$application_id = (int)($_POST['application_id'] ?? 0);
$site_name      = $_POST['site_name'] ?? null;
$user_id        = $_SESSION['user_id'] ?? null;

// New (range mode)
$start_col = isset($_POST['start_col']) ? (int)$_POST['start_col'] : null;
$end_col   = isset($_POST['end_col']) ? (int)$_POST['end_col'] : null;
$agg_mode  = strtolower($_POST['agg_mode'] ?? 'min'); // min | max

// Excel params
if (!isset($_POST['standard_upper'], $_POST['standard_lower'], $_POST['lower_boundary'], $_POST['interval_width'])) {
    echo json_encode(['success' => false, 'message' => 'Parameter Excel tidak lengkap']);
    exit;
}

$usl = (float)$_POST['standard_upper'];
$lsl = (float)$_POST['standard_lower'];
$user_lower_boundary = (float)$_POST['lower_boundary'];
$user_interval_width = (float)$_POST['interval_width'];

if ($user_interval_width <= 0) {
    echo json_encode(['success' => false, 'message' => 'interval_width harus > 0']);
    exit;
}

// ======================================================
// Constants
// ======================================================
$TABLE1_MAX = 190;
$TABLE2_MAX = 380;

// ======================================================
// Build WHERE
// ======================================================
$where = " WHERE d.file_id = :file_id AND d.date = :production_date ";
$params = [
    ':file_id' => $file_id,
    ':production_date' => $production_date
];

if ($line_id) {
    $where .= " AND d.line_id = :line_id ";
    $params[':line_id'] = $line_id;
}
if ($application_id) {
    $where .= " AND d.application_id = :application_id ";
    $params[':application_id'] = $application_id;
}


// ======================================================
// (A) FETCH RAW DATA (SQL ONLY FOR FETCH)
// ======================================================
$tmp_values = [];

if ($start_col !== null && $end_col !== null) {

    if ($start_col < 1 || $end_col > $TABLE2_MAX || $start_col > $end_col) {
        echo json_encode(['success' => false, 'message' => 'Range column tidak valid']);
        exit;
    }

    // ---------- tbl_data ----------
    $cols1 = [];
    for ($i = max(1, $start_col); $i <= min($end_col, $TABLE1_MAX); $i++) {
        $cols1[] = "d.data_$i";
    }

    if ($cols1) {
        $sql = "SELECT " . implode(',', $cols1) . "
        FROM tbl_data d $where";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row_values = [];

            foreach ($cols1 as $c) {
                $col = str_replace('d.', '', $c); // ✅ FIX
                $raw = $row[$col] ?? null;

                if ($raw === null) continue;

                $v = str_replace(',', '.', trim((string)$raw));
                if (is_numeric($v)) {
                    $row_values[] = (float)$v;
                }
            }

            if ($row_values) {
                $val = ($agg_mode === 'max') ? max($row_values) : min($row_values);
                $tmp_values[] = $val;
            }
        }
    }

    // ---------- tbl_data2 ----------
    if ($end_col > $TABLE1_MAX) {
        $cols2 = [];
        for ($i = max(191, $start_col); $i <= $end_col; $i++) {
            $cols2[] = "d2.data_$i";
        }

        $sql = "SELECT " . implode(',', $cols2) . "
        FROM tbl_data2 d2
        WHERE d2.file_id = :file_id
          AND d2.date = :production_date
          AND d2.line_id = :line_id
          AND d2.application_id = :application_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':file_id' => $file_id,
            ':production_date' => $production_date,
            ':line_id' => $line_id,
            ':application_id' => $application_id
        ]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row_values = [];

            foreach ($cols2 as $c) {
                $col = str_replace('d2.', '', $c); // ✅ FIX
                $raw = $row[$col] ?? null;

                if ($raw === null) continue;

                $v = str_replace(',', '.', trim((string)$raw));
                if (is_numeric($v)) {
                    $row_values[] = (float)$v;
                }
            }


            if ($row_values) {
                $val = ($agg_mode === 'max') ? max($row_values) : min($row_values);
                $tmp_values[] = $val;
            }
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Mode lama (1 header) belum diaktifkan di versi ini']);
    exit;
}


$values = $tmp_values;
// ======================================================
// (B) STATISTIK DASAR (TIDAK DIUBAH)
// ======================================================
$n = count($values);
if ($n < 1) {
    echo json_encode(['success' => false, 'message' => 'Data tidak cukup']);
    exit;
}

$rata_rata = array_sum($values) / $n;
$variance = 0.0;
foreach ($values as $v) {
    $variance += pow($v - $rata_rata, 2);
}
$standar_deviasi = sqrt($variance / ($n - 1));

$min_val = min($values);
$max_val = max($values);

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

$count_out = 0;
$out_of_control_vals = [];
$min_out = null;
$max_out = null;
$limited_out_storage = 100;

foreach ($values as $v) {
    if ($v > $usl || $v < $lsl) {
        $count_out++;
        if ($min_out === null || $v < $min_out) $min_out = $v;
        if ($max_out === null || $v > $max_out) $max_out = $v;
        if (count($out_of_control_vals) < $limited_out_storage) {
            $out_of_control_vals[] = $v;
        }
    }
}

$percent_out = ($n > 0) ? ($count_out / $n) * 100.0 : 0.0;


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
    'debug_range' => "{$start_col}-{$end_col}",
    'debug_agg_mode' => $agg_mode,
    'debug_total_data' => $n,
    'debug_bin_count' => $num_bins,
    'debug_predicted_total' => $predicted_total,
    'line_name' => $line_name ?? null,
    'application_name' => $application_name ?? null,
    'file_name' => $file_name ?? null,
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

$output['debug_tmp_rows'] = count($tmp_values);
// --- (10) Limit Cp/Cpk per site (optional)
$std_limit_cp = 0.85;
$std_limit_cpk = 0.85;

if ($user_id && $site_name) {
    $stmtLimit = $pdo->prepare("SELECT cp_limit, cpk_limit FROM tbl_spc_model_settings WHERE user_id = :user_id AND site_name = :site_name LIMIT 1");
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
