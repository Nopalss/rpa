<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = (int) $_POST['file_id'];

    try {
        // Generate query UNION ALL otomatis untuk 128 kolom
        $unionParts = [];
        for ($i = 1; $i <= 128; $i++) {
            $col = "column_$i";
            // Hanya ambil kolom yang tidak NULL dan tidak kosong
            $unionParts[] = "SELECT $col AS header_name 
                             FROM tbl_header 
                             WHERE file_id = :file_id 
                               AND $col IS NOT NULL 
                               AND TRIM($col) <> ''";
        }

        // Gabungkan jadi satu query besar pakai UNION ALL
        $sql = implode(" UNION ALL ", $unionParts);

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':file_id' => $file_id]);
        $headers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($headers);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
