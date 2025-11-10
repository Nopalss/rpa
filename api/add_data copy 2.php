<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../helper/sanitize.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// Mulai Database Transaction untuk setiap request
$pdo->beginTransaction();

try {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        // Ambil data dari JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Format JSON tidak valid: " . json_last_error_msg());
        }
    } else {
        // Ambil data dari form-data
        $input = $_POST;
    }

    if (!$input || !is_array($input)) {
        throw new Exception("Input tidak valid atau kosong");
    }

    // Ambil field utama
    $line_id = sanitize($input['line_id'] ?? '');
    $application_id = sanitize($input['application_id'] ?? '');
    $file_id = sanitize($input['file_id'] ?? '');
    $header_id = sanitize($input['header_id'] ?? '');

    // --- LOGIKA ID DIUBAH ---
    // Logika SELECT MAX() dihapus.
    // Kita buat ID unik sebagai STRING di sini.
    // Ini aman dari race condition.
    $record_no = uniqid('rec_'); // Hasilnya: "rec_6723a1a1c9a87"

    if (empty($line_id) || empty($application_id) || empty($file_id) || empty($header_id)) {
        throw new Exception("Tolong isi form dengan benar");
    }

    // ===========================================
    // --- INSERT PERTAMA (tbl_data) ---
    // ===========================================

    $columns1 = [];
    for ($i = 1; $i <= 190; $i++) {
        $columns1["data_$i"] = $input["data_$i"] ?? null;
    }

    // 'record_no' sekarang dimasukkan sebagai string unik
    $fields1 = ['record_no', 'line_id', 'application_id', 'file_id', 'header_id'];
    $placeholders1 = [':record_no', ':line_id', ':application_id', ':file_id', ':header_id'];
    $params1 = [
        ':record_no' => $record_no, // <-- Menggunakan string uniqid()
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

    // ===========================================
    // --- INSERT KEDUA (tbl_data2) ---
    // ===========================================

    $columns2 = [];
    for ($i = 191; $i <= 380; $i++) {
        $columns2["data_$i"] = $input["data_$i"] ?? null;
    }

    $fields2 = ['record_no', 'line_id', 'application_id', 'file_id', 'header_id'];
    $placeholders2 = [':record_no', ':line_id', ':application_id', ':file_id', ':header_id'];
    $params2 = [
        ':record_no' => $record_no, // <-- Menggunakan string uniqid() YANG SAMA
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

    // Jika kedua insert berhasil, simpan
    $pdo->commit();

    http_response_code(200);
    echo json_encode([
        "code" => 200,
        "message" => "Success",
        "record_no" => $record_no // Kirim balik ID string barunya
    ]);
} catch (Exception $e) {
    // Jika ada error, batalkan insert
    $pdo->rollBack();

    http_response_code(400);
    echo json_encode([
        "code" => 400,
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
