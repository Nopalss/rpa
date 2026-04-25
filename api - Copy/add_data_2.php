<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../helper/sanitize.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// Tetap aktifkan atribut PDO MySQL (tidak dihapus, meski tidak langsung dipakai)
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

    // Simpan payload lengkap ke SQLite queue
    $input['record_no'] = $record_no;
    $queuePath = __DIR__ . '/queue.db';
    $sqlite = new PDO('sqlite:' . $queuePath);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sqlite->exec("
        CREATE TABLE IF NOT EXISTS queue (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            payload TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $stmt = $sqlite->prepare("INSERT INTO queue (payload) VALUES (:payload)");
    $stmt->execute(['payload' => json_encode($input)]);

    http_response_code(200);
    echo json_encode([
        "code" => 200,
        "message" => "Queued",
        "record_no" => $record_no
    ]);
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Queue Error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        "code" => 400,
        "error" => true,
        "message" => "Gagal menyimpan ke queue: " . $e->getMessage()
    ]);
} finally {
    $pdo = null;
}
