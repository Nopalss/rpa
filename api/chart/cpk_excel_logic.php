<?php
// cpk_excel_logic.php


function calculate_cpk_excel(array $params, PDO $pdo)
{
    set_time_limit(0);

    // Jaga presisi float setara Excel
    ini_set('precision', 17);
    ini_set('serialize_precision', -1);
    date_default_timezone_set('Asia/Jakarta');

    // ===============================
    // Helper: production date
    // ===============================
    $cutoff_hour = 6;
    $cutoff_minute = 0;
    $now = new DateTime('now');
    $hour = (int)$now->format('H');
    $minute = (int)$now->format('i');
    if ($hour < $cutoff_hour || ($hour === $cutoff_hour && $minute <= $cutoff_minute)) {
        $now->modify('-1 day');
    }
    $production_date = $now->format('Y-m-d');

    // ===============================
    // INPUT (IDENTIK DENGAN $_POST)
    // ===============================
    $file_id        = $params['file_id'] ?? 0;
    $header_name    = $params['header_name'] ?? '';
    $table_type     = $params['table_type'] ?? 'type1';
    $line_id        = $params['line_id'] ?? 0;
    $application_id = $params['application_id'] ?? null;
    $site_name      = $params['site_name'] ?? null;
    $user_id        = $params['user_id'] ?? null;

    $user_standard_upper = (float)($params['standard_upper'] ?? 0);
    $user_standard_lower = (float)($params['standard_lower'] ?? 0);
    $user_interval_width = (float)($params['interval_width'] ?? 0);
    $user_lower_boundary = (float)($params['lower_boundary'] ?? 0);

    if ($user_interval_width <= 0 || empty($file_id) || empty($header_name)) {
        return ['success' => false];
    }

    // ===============================
    // KONSTANTA TABEL (IDENTIK)
    // ===============================
    $TABLE1_MAX_COL   = 190;
    $TABLE2_START_COL = 191;
    $TABLE2_MAX_COL   = 380;

    $data_column_name = '';
    $header_column = '';
    $data_table = '';
    $header_table = '';
    $found = false;

    // ===============================
    // CARI KOLOM HEADER (IDENTIK)
    // ===============================
    $tables_to_check = [];
    if ($table_type === 'type1' || $table_type === 'split') {
        $tables_to_check[] = ['tbl_header', 'tbl_data'];
    }
    if ($table_type === 'type2' || $table_type === 'split') {
        $tables_to_check[] = ['tbl_header2', 'tbl_data2'];
    }

    foreach ($tables_to_check as [$header_tbl, $data_tbl]) {
        $stmtHeader = $pdo->prepare("SELECT * FROM {$header_tbl} WHERE file_id = :file_id LIMIT 1");
        $stmtHeader->execute([':file_id' => $file_id]);
        $row = $stmtHeader->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            foreach ($row as $col_name => $col_value) {
                if ($col_value === $header_name && preg_match('/^column_(\d+)$/', $col_name, $m)) {
                    $idx = (int)$m[1];
                    $data_column_name = "data_{$idx}";
                    $header_column = "column_{$idx}";
                    $data_table = $data_tbl;
                    $header_table = $header_tbl;
                    $found = true;
                    break 2;
                }
            }
        }
    }

    if (!$found) {
        return ['success' => false];
    }

    // ===============================
    // SQL FILTER NUMERIK (IDENTIK)
    // ===============================
    $clean_data_col = "REPLACE(TRIM(d.`{$data_column_name}`), ',', '.')";
    $numeric_where = "{$clean_data_col} REGEXP '^-?([0-9]+\\.?[0-9]*|\\.[0-9]+)$'";

    $bind = [
        ':file_id' => $file_id,
        ':header_name' => $header_name,
        ':production_date' => $production_date
    ];

    $strat = '';
    if (!empty($line_id)) {
        $strat .= " AND d.line_id = :line_id ";
        $bind[':line_id'] = $line_id;
    }
    if (!empty($application_id)) {
        $strat .= " AND d.application_id = :application_id ";
        $bind[':application_id'] = $application_id;
    }

    // ===============================
    // (1) STATISTIK DASAR (IDENTIK)
    // ===============================
    $sqlAgg = "
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
          {$strat}
          AND d.date = :production_date
    ";
    $stmt = $pdo->prepare($sqlAgg);
    $stmt->execute($bind);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $n = (int)$stats['cnt'];
    if ($n < 1) return ['success' => false];

    $mean = (float)$stats['mean'];
    $std  = (float)$stats['stddev'];
    $min_val = (float)$stats['min_val'];
    $max_val = (float)$stats['max_val'];

    // ===============================
    // (2) USL / LSL (IDENTIK)
    // ===============================
    $usl = $user_standard_upper;
    $lsl = $user_standard_lower;

    // ===============================
    // (3) MIDPOINT & EDGES (IDENTIK)
    // ===============================
    $midpoints = [];
    $edges = [];
    for ($i = 0; $i <= 22; $i++) {
        $mid = ((2 * $user_lower_boundary) + $user_interval_width * (1 + ($i * 2))) / 2;
        $midpoints[$i] = $mid;
        $edges[$i] = $mid + $user_interval_width;
    }
    $num_bins = count($midpoints);

    // ===============================
    // (4) AMBIL SEMUA DATA (IDENTIK)
    // ===============================
    $sqlVal = "
        SELECT CAST({$clean_data_col} AS DECIMAL(38,12)) AS nilai
        FROM {$data_table} d
        JOIN {$header_table} h ON d.header_id = h.record_no
        WHERE h.`{$header_column}` = :header_name
          AND d.file_id = :file_id
          AND {$numeric_where}
          {$strat}
          AND d.date = :production_date
    ";
    $stmt = $pdo->prepare($sqlVal);
    $stmt->execute($bind);

    $values = [];
    $out_vals = [];
    $count_out = 0;
    $min_out = null;
    $max_out = null;

    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $v = (float)$r['nilai'];
        $values[] = $v;
        if ($v > $usl || $v < $lsl) {
            $count_out++;
            $min_out = ($min_out === null) ? $v : min($min_out, $v);
            $max_out = ($max_out === null) ? $v : max($max_out, $v);
            if (count($out_vals) < 100) $out_vals[] = $v;
        }
    }

    // ===============================
    // (5) COUNTIF EXCEL (IDENTIK)
    // ===============================
    $bins = array_fill(0, $num_bins, 0);
    for ($i = 0; $i < $num_bins; $i++) {
        $upper = $edges[$i];
        $lower = ($i > 0) ? $edges[$i - 1] : null;
        $cu = 0;
        $cl = 0;
        foreach ($values as $v) {
            if ($v <= $upper) $cu++;
            if ($lower !== null && $v <= $lower) $cl++;
        }
        $bins[$i] = $cu - $cl;
    }

    // ===============================
    // (6) SERIES & CURVE (IDENTIK)
    // ===============================
    $series = [];
    $curve = [];
    $bin_labels = [];
    $pred_total = 0.0;

    for ($i = 0; $i < $num_bins; $i++) {
        $mid = $midpoints[$i];
        $upper = $edges[$i];
        $lower = ($i > 0) ? $edges[$i - 1] : null;

        $series[] = [$mid, (int)$bins[$i]];

        $bin_labels[] = sprintf(
            "%s - %s",
            rtrim(rtrim(($i === 0 ? $user_lower_boundary : $edges[$i - 1]), '0'), '.'),
            rtrim(rtrim($upper, '0'), '.')
        );

        if ($std > 0) {
            $cu = normal_cdf($upper, $mean, $std);
            $cl = ($lower === null) ? 0.0 : normal_cdf($lower, $mean, $std);
            $pf = $n * ($cu - $cl);
            $pred_total += $pf;
            $curve[] = [$mid, $pf];
        } else {
            $curve[] = [$mid, 0];
        }
    }

    // ===============================
    // (7) CP / CPK (IDENTIK)
    // ===============================
    $cp  = ($std > 0) ? ($usl - $lsl) / (6 * $std) : null;
    $cpu = ($std > 0) ? ($usl - $mean) / (3 * $std) : null;
    $cpl = ($std > 0) ? ($mean - $lsl) / (3 * $std) : null;
    $cpk = ($cpu !== null && $cpl !== null) ? min($cpu, $cpl) : null;

    // ===============================
    // (8) DEFECT RATE (IDENTIK)
    // ===============================
    $eu = ($std > 0) ? (1 - normal_cdf($usl, $mean, $std)) : 0;
    $el = ($std > 0) ? normal_cdf($lsl, $mean, $std) : 0;

    // ===============================
    // DEFAULT LIMIT (STANDARD SPC)
    // ===============================
    $DEFAULT_LIMIT = 0.85;

    $cp_limit = (
        array_key_exists('cp_limit', $params) &&
        $params['cp_limit'] !== '' &&
        $params['cp_limit'] !== null
    )
        ? (float)$params['cp_limit']
        : $DEFAULT_LIMIT;

    $cpk_limit = (
        array_key_exists('cpk_limit', $params) &&
        $params['cpk_limit'] !== '' &&
        $params['cpk_limit'] !== null
    )
        ? (float)$params['cpk_limit']
        : $DEFAULT_LIMIT;
    // ===============================
    // (7.1) CP / CPK STATUS (OPSIONAL)
    // ===============================
    $cp_status = null;
    $cpk_status = null;
    $final_status = null;

    if ($cp_limit !== null && $cp !== null) {
        $cp_status = ($cp >= $cp_limit) ? 'OK' : 'Over Spec';
    }

    if ($cpk_limit !== null && $cpk !== null) {
        $cpk_status = ($cpk >= $cpk_limit) ? 'OK' : 'Over Spec';
    }

    if ($cp_status !== null && $cpk_status !== null) {
        $final_status =
            ($cp_status === 'OK' && $cpk_status === 'OK')
            ? 'OK'
            : 'NG';
    }

    return [
        'success' => true,
        'series_data' => $series,
        'bin_labels' => $bin_labels,
        'normal_curve' => $curve,
        'rata_rata' => $mean,
        'standar_deviasi' => $std,
        'usl' => $usl,
        'lsl' => $lsl,
        'cp' => $cp,
        'cpk' => $cpk,
        'cpu' => $cpu,
        'cpl' => $cpl,
        'eu' => $eu,
        'el' => $el,
        'estimated_defect_rate' => $eu + $el,
        'debug_total_data' => $n,
        'out_of_control' => $count_out > 0,
        'out_of_control_count' => $count_out,
        'out_of_control_percent' => ($n > 0) ? ($count_out / $n) * 100 : 0,
        'out_of_control_sample' => $out_vals,
        'out_of_control_min' => $min_out,
        'out_of_control_max' => $max_out,
        'debug_lower_boundary' => $user_lower_boundary,
        'debug_interval_width' => $user_interval_width,
        'debug_upper_boundaries' => $edges,
        'min_val' => $min_val,
        'max_val' => $max_val,
        'cp_status' => $cp_status,
        'cpk_status' => $cpk_status,
        'final_status' => $final_status,
        'std_limit_cp' => $cp_limit,
        'std_limit_cpk' => $cpk_limit
    ];
}

// ===============================
// NORMAL CDF (IDENTIK)
// ===============================
function normal_cdf($x, $mu, $sigma)
{
    if ($sigma <= 0) return ($x < $mu) ? 0 : 1;
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
    $c = 0.39894228;

    if ($z >= 0) {
        $t = 1 / (1 + $p * $z);
        return 1 - $c * exp(-$z * $z / 2) * $t *
            ($b1 + $t * ($b2 + $t * ($b3 + $t * ($b4 + $t * $b5))));
    }
    return 1 - std_normal_cdf(-$z);
}
