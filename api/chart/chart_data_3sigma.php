<?php
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

$user_id   = $_SESSION['user_id'];
$site_name = $_POST['site_name'] ?? null;

if (!$site_name) {
    echo json_encode([
        'success' => false,
        'message' => 'site_name wajib'
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT result_json
    FROM tbl_chart_cache
    WHERE user_id = :uid
      AND site_name = :site
    LIMIT 1
");
$stmt->execute([
    ':uid'  => $user_id,
    ':site' => $site_name
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode([
        'success' => false,
        'message' => 'Data belum tersedia (sedang dihitung)'
    ]);
    exit;
}

echo $row['result_json'];
exit;
