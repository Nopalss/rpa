<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../helper/sanitize.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// Aktifkan persistent connection & non-emulated prepare
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_PERSISTENT, true);

try {
    $pdo->beginTransaction();

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

    // Field utama
    $line_id = sanitize($input['line_id'] ?? '');
    $application_id = sanitize($input['application_id'] ?? '');
    $file_id = sanitize($input['file_id'] ?? '');
    $header_id = sanitize($input['header_id'] ?? '');
    // ID unik aman untuk 1000+ concurrent request
    $record_no = !empty($input['record_no']) ? sanitize($input['record_no']) : 'rec_' . bin2hex(random_bytes(8));



    if (empty($line_id) || empty($application_id) || empty($file_id) || empty($header_id)) {
        throw new Exception("Tolong isi form dengan benar");
    }

    // =================================================
    // INSERT KE TABEL PERTAMA: tbl_data
    // =================================================
    $columns1 = [];
    for ($i = 1; $i <= 190; $i++) {
        $columns1["data_$i"] = $input["data_$i"] ?? null;
    }

    $fields1 = ['record_no', 'line_id', 'application_id', 'file_id', 'header_id'];
    $placeholders1 = [':record_no', ':line_id', ':application_id', ':file_id', ':header_id'];
    $params1 = [
        ':record_no' => $record_no,
        ':line_id' => $line_id,
        ':application_id' => $application_id,
        ':file_id' => $file_id,
        ':header_id' => $header_id
    ];

    foreach ($columns1 as $key => $val) {
        $fields1[] = $key;
        $placeholders1[] = ":$key";
        $params1[":$key"] = $val;
    }

    $sql1 = "INSERT INTO tbl_data (" . implode(',', $fields1) . ") VALUES (" . implode(',', $placeholders1) . ")";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute($params1);

    // =================================================
    // INSERT KE TABEL KEDUA: tbl_data2
    // =================================================
    $columns2 = [];
    for ($i = 191; $i <= 380; $i++) {
        $columns2["data_$i"] = $input["data_$i"] ?? null;
    }

    $fields2 = ['record_no', 'line_id', 'application_id', 'file_id', 'header_id'];
    $placeholders2 = [':record_no', ':line_id', ':application_id', ':file_id', ':header_id'];
    $params2 = [
        ':record_no' => $record_no,
        ':line_id' => $line_id,
        ':application_id' => $application_id,
        ':file_id' => $file_id,
        ':header_id' => $header_id
    ];

    foreach ($columns2 as $key => $val) {
        $fields2[] = $key;
        $placeholders2[] = ":$key";
        $params2[":$key"] = $val;
    }

    $sql2 = "INSERT INTO tbl_data2 (" . implode(',', $fields2) . ") VALUES (" . implode(',', $placeholders2) . ")";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute($params2);

    // Commit transaksi
    $pdo->commit();

    http_response_code(200);
    echo json_encode([
        "code" => 200,
        "message" => "Success",
        "record_no" => $record_no
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Catat error ke log server, jangan tampilkan ke client
    error_log("[" . date('Y-m-d H:i:s') . "] Insert Error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        "code" => 400,
        "error" => true,
        "message" => "Terjadi kesalahan saat memproses data."
    ]);
} finally {
    // Tutup koneksi PDO (lebih cepat rilis resource)
    $pdo = null;
}
