<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$software = isset($input['software']) ? $input['software'] : null;

if (!$software) {
    echo json_encode(['error' => 'Invalid software']);
    exit;
}

try {
    // Ambil data aplikasi
    $stmt = $pdo->prepare("SELECT id, name, path FROM tbl_application WHERE name = ?");
    $stmt->execute([$software]);
    $appData = $stmt->fetch();
    $application_id = $appData['id'];
    if (!$appData) {
        echo json_encode(['error' => 'Application not found']);
        exit;
    }

    // Ambil file_id, filename, header_id dari join tbl_filename dan tbl_header
    $query = "
        SELECT f.file_id, f.filename, h.header_id
        FROM tbl_filename f
        LEFT JOIN tbl_header h ON h.file_id = f.file_id
        WHERE f.application_id = ?
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$application_id]);
    $rows = $stmt->fetchAll();

    // Susun format csv_files
    $csvFiles = [];
    foreach ($rows as $row) {
        $csvFiles[$row['filename']] = [
            'file_id' => $row['file_id'],
            'header_id' => $row['header_id']
        ];
    }

    // Bentuk response
    $response = [
        'application_id' => $appData['id'],
        'application_name' => $appData['name'],
        'csv_path' => $appData['path'],
        'csv_files' => $csvFiles
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
