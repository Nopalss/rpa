<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../helper/sanitize.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
        $date = sanitize($input['date'] ?? '');
        $time = sanitize($input['time'] ?? '');
        $line_id = sanitize($input['line_id'] ?? '');
        $model_id = sanitize($input['model_id'] ?? '');
        $file_id = sanitize($input['file_id'] ?? '');
        $header_id = sanitize($input['header_id'] ?? '');


        if (empty($line_id) || empty($model_id) || empty($file_id) || empty($header_id) || empty($date) || empty($time)) {
            throw new Exception("Tolongg isi form dengan benar");
        }

        // Kumpulkan semua data (max 128)
        $columns = [];
        for ($i = 1; $i <= 128; $i++) {
            $key = "data_$i";
            $columns[$key] = isset($input[$key]) ? $input[$key] : null;
        }

        // Buat query dinamis
        $fields = ['date', 'time', 'line_id', 'model_id', 'file_id', 'header_id'];
        $placeholders = [':date', ':time', ':line_id', ':model_id', ':file_id', ':header_id'];
        $params = [
            ':date' => $date,
            ':time' => $time,
            ':line_id' => $line_id,
            ':model_id' => $model_id,
            ':file_id' => $file_id,
            ':header_id' => $header_id
        ];
        foreach ($columns as $key => $val) {
            $fields[] = $key;
            $placeholders[] = ":$key";
            $params[":$key"] = $val;
        }

        // Insert table header
        $sql = "INSERT INTO tbl_data (" . implode(',', $fields) . ") 
                VALUES (" . implode(',', $placeholders) . ")";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Selamat, data berhasil ditambahkan"
        ]);
    } catch (Exception $e) {

        http_response_code(400);
        echo json_encode([
            "error" => true,
            "message" => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "error" => true,
        "message" => "Invalid request method. Only POST allowed."
    ]);
}
