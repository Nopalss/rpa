<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$software = $input['software'] ?? null;

if (!$software) {
    echo json_encode(['error' => 'Invalid software']);
    exit;
}

/**
 * ===============================
 * CACHE CONFIG
 * ===============================
 */
$CACHE_TTL = 600; // 10 menit
$cacheDir  = __DIR__ . '/cache';

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

$cacheKey  = md5($software);
$cacheFile = "$cacheDir/get_csv_$cacheKey.json";

/**
 * ===============================
 * RETURN CACHE IF VALID
 * ===============================
 */
if (file_exists($cacheFile)) {
    if ((time() - filemtime($cacheFile)) < $CACHE_TTL) {
        echo file_get_contents($cacheFile);
        exit;
    }
}

try {
    // Ambil data aplikasi
    $stmt = $pdo->prepare("SELECT id, name, path FROM tbl_application WHERE name = ?");
    $stmt->execute([$software]);
    $appData = $stmt->fetch();

    if (!$appData) {
        echo json_encode(['error' => 'Application not found']);
        exit;
    }

    $application_id = $appData['id'];

    // Ambil file CSV
    $stmt = $pdo->prepare("
        SELECT f.file_id, f.filename, h.header_id
        FROM tbl_filename f
        LEFT JOIN tbl_header h ON h.file_id = f.file_id
        WHERE f.application_id = ?
    ");
    $stmt->execute([$application_id]);
    $rows = $stmt->fetchAll();

    $csvFiles = [];
    foreach ($rows as $row) {
        $csvFiles[$row['filename']] = [
            'file_id'   => (int)$row['file_id'],
            'header_id' => (int)$row['header_id']
        ];
    }

    $response = [
        'application_id'   => (int)$appData['id'],
        'application_name' => $appData['name'],
        'csv_path'         => $appData['path'],
        'csv_files'        => $csvFiles,
        'cached_at'        => date('Y-m-d H:i:s')
    ];

    file_put_contents($cacheFile, json_encode($response));
    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
