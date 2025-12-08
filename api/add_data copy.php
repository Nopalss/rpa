<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../helper/sanitize.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_PERSISTENT, true);

try {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Format JSON tidak valid: " . json_last_error_msg());
        }
    } else {
        $input = $_POST;
    }

    if (!$input || !is_array($input)) {
        throw new Exception("Input tidak valid atau kosong");
    }

    // Validasi utama
    $line_id = sanitize($input['line_id'] ?? '');
    $application_id = sanitize($input['application_id'] ?? '');
    $file_id = sanitize($input['file_id'] ?? '');
    $header_id = sanitize($input['header_id'] ?? '');
    $record_no = !empty($input['record_no']) ? sanitize($input['record_no']) : 'rec_' . bin2hex(random_bytes(8));

    if (empty($line_id) || empty($application_id) || empty($file_id) || empty($header_id)) {
        throw new Exception("Tolong isi form dengan benar");
    }

    // ============ INSERT tbl_data (1–190) ============
    $columns1 = [];
    $placeholders1 = [];
    $values1 = [];
    for ($i = 1; $i <= 190; $i++) {
        $columns1[] = "data_$i";
        $placeholders1[] = "?";
        $values1[] = $input["data_$i"] ?? null;
    }

    $sql1 = "INSERT INTO tbl_data 
            (record_no, line_id, application_id, file_id, header_id, " . implode(',', $columns1) . ")
            VALUES (" . implode(',', array_fill(0, count($values1) + 5, '?')) . ")";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute(array_merge([$record_no, $line_id, $application_id, $file_id, $header_id], $values1));

    // ============ INSERT tbl_data2 (191–380) ============
    $columns2 = [];
    $placeholders2 = [];
    $values2 = [];
    for ($i = 191; $i <= 380; $i++) {
        $columns2[] = "data_$i";
        $placeholders2[] = "?";
        $values2[] = $input["data_$i"] ?? null;
    }

    $sql2 = "INSERT INTO tbl_data2 
            (record_no, line_id, application_id, file_id, header_id, " . implode(',', $columns2) . ")
            VALUES (" . implode(',', array_fill(0, count($values2) + 5, '?')) . ")";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute(array_merge([$record_no, $line_id, $application_id, $file_id, $header_id], $values2));

    // Tidak perlu transaksi → lebih cepat & tahan beban
    http_response_code(200);
    echo json_encode([
        "code" => 200,
        "message" => "Success",
        "record_no" => $record_no
    ]);
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Insert Error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        "code" => 400,
        "error" => true,
        "message" => "Gagal menyimpan data: " . $e->getMessage()
    ]);
} finally {
    $pdo = null;
}
