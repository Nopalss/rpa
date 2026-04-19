<?php
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

$site = $_GET['site'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

// ==========================
// VALIDATION
// ==========================
if (!$site || !$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
    exit;
}

// ==========================
// QUERY (JOIN)
// ==========================
$stmt = $pdo->prepare("
    SELECT 
        us.*,
        l.line_name,
        a.name AS application_name,
        f.filename AS file_name

    FROM tbl_spc_model_settings us

    LEFT JOIN tbl_line l 
        ON us.line_id = l.line_id

    LEFT JOIN tbl_application a 
        ON us.application_id = a.id

    LEFT JOIN tbl_filename f 
        ON us.file_id = f.file_id

    WHERE us.user_id = ? 
    AND us.site_name = ?
    LIMIT 1
");

$stmt->execute([$user_id, $site]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// ==========================
// HANDLE NOT FOUND
// ==========================
if (!$row) {
    echo json_encode([
        "success" => false,
        "message" => "Site config not found"
    ]);
    exit;
}

// ==========================
// RESPONSE
// ==========================
echo json_encode([
    "success" => true,

    // core config
    "line_id" => $row['line_id'],
    "application_id" => $row['application_id'],
    "file_id" => $row['file_id'],
    "standard_upper" => $row['standard_upper'],
    "standard_lower" => $row['standard_lower'],
    "lower_boundary" => $row['lower_boundary'],
    "interval_width" => $row['interval_width'],
    "site_name" => $row['site_name'],
    "agg_mode" => $row['agg_mode'],
    "start_col" => $row['start_col'],
    "end_col" => $row['end_col'],

    // tambahan (buat summary UI)
    "line_name" => $row['line_name'] ?? '-',
    "application_name" => $row['application_name'] ?? '-',
    "file_name" => $row['file_name'] ?? '-',
    "site_name" => $row['site_name'] ?? '-',
    "site_label" => $row['site_label'] ?? '-'
]);
