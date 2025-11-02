<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../helper/sanitize.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {

try {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        // Ambil data dari JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Format JSON tidak valid: " . json_last_error_msg());
        }
    } else {
        // Ambil data dari form-data atau x-www-form-urlencoded
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

    $stmt_max = $pdo->query("SELECT MAX(data_id) as max_id FROM tbl_data");
    $max_id = $stmt_max->fetchColumn();

    //  Hitung record_no 
    $record_no = (int)$max_id + 1;


    if (empty($line_id) || empty($application_id) || empty($file_id) || empty($header_id)) {
        throw new Exception("Tolong isi form dengan benar");
    }

    // Kumpulkan semua data (max 128)
    $columns1 = [];
    for ($i = 1; $i <= 190; $i++) {
        $key = "data_$i";
        $columns1[$key] = isset($input[$key]) ? $input[$key] : null;
    }

    // Buat query dinamis
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

    // Insert table header
    $sql = "INSERT INTO tbl_data (" . implode(',', $fields1) . ") 
                VALUES (" . implode(',', $placeholders1) . ")";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params1);

    // Kumpulkan semua data (max 380)
    $columns2 = [];
    for ($i = 191; $i <= 380; $i++) {
        $key = "data_$i";
        $columns2[$key] = isset($input[$key]) ? $input[$key] : null;
    }

    // Buat query dinamis
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

    // Insert table header
    $sql = "INSERT INTO tbl_data2 (" . implode(',', $fields2) . ") 
                VALUES (" . implode(',', $placeholders2) . ")";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params2);

    http_response_code(200);
    echo json_encode([
        "code" => 200,
        "message" => "Success"
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "code" => 400,
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
// } else {
//     http_response_code(405);
//     echo json_encode([
//         "code" => 405,
//         "error" => true,
//         "message" => "Invalid request method. Only POST allowed."
//     ]);
// }
