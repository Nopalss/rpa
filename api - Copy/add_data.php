<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../helper/sanitize.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) throw new Exception("Invalid JSON");

    $record_no = !empty($input['record_no']) ? sanitize($input['record_no']) : 'rec_' . bin2hex(random_bytes(8));
    $input['record_no'] = $record_no;

    $redis->rPush('queue:add_data', json_encode($input));

    echo json_encode(["code" => 200, "message" => "Queued", "record_no" => $record_no]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["code" => 500, "error" => $e->getMessage()]);
}
