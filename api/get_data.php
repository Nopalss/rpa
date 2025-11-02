<?php
require_once __DIR__ . "/../includes/config.php";

header('Content-Type: application/json');

try {
    // 1. AMBIL PARAMETER DARI DALAM 'query'
    // Ini adalah perbaikan utama
    $line = $_POST['query']['line_id'] ?? '';
    $application = $_POST['query']['application_id'] ?? '';
    $date = $_POST['query']['date'] ?? '';


    $sql = "SELECT d.line_id, d.date, d.record_no, d.header_id, d.application_id, f.filename, f.file_id
            FROM tbl_data d 
            JOIN tbl_filename f ON d.file_id = f.file_id";

    $conditions = [];
    $params = [];

    // Tambahkan kondisi HANYA JIKA filternya diisi (tidak kosong)
    if (!empty($line)) {
        $conditions[] = "d.line_id = :line_id";
        $params[':line_id'] = $line;
    }

    if (!empty($application)) {
        $conditions[] = "d.application_id = :application_id";
        $params[':application_id'] = $application;
    }

    if (!empty($date)) {
        // 4. Masukkan tanggal yang sudah diformat ulang ke parameter
        $conditions[] = "d.date = :date";
        $params[':date'] = $date;
    }

    // Gabungkan semua kondisi (jika ada)
    if (count($conditions) > 0) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // Hindari duplikat file
    $sql .= " GROUP BY d.line_id, d.date, d.record_no, d.header_id, d.application_id, f.file_id, f.filename";
    // Jalankan kueri
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "data" => $data
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
