<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Pastikan input valid
        $app_id = isset($_POST['app_id']) ? (int) $_POST['app_id'] : 0;

        if ($app_id <= 0) {
            echo json_encode(['error' => 'Invalid line ID']);
            exit;
        }

        // Query relasi line → application
        $stmt = $pdo->prepare("
            SELECT file_id as id, filename as name
            FROM tbl_filename
            WHERE application_id = :id
            ORDER BY filename ASC
        ");
        $stmt->execute([':id' => $app_id]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($data);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
