<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Pastikan input valid
        $line_id = isset($_POST['line_id']) ? (int) $_POST['line_id'] : 0;

        if ($line_id <= 0) {
            echo json_encode(['error' => 'Invalid line ID']);
            exit;
        }

        // Query relasi line → application
        $stmt = $pdo->prepare("
            SELECT 
                dl.application_id as id, 
                a.name 
            FROM tbl_detail_line dl
            INNER JOIN tbl_application a ON dl.application_id = a.id
            WHERE dl.line_id = :id
            ORDER BY a.name ASC
        ");
        $stmt->execute([':id' => $line_id]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($data);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
