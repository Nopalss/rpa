<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/queue.php';

header('Content-Type: application/json');
set_time_limit(5);

$user_id   = $_SESSION['user_id'] ?? 0;
$site_name = $_POST['site_name'] ?? '';

if (!$user_id || !$site_name) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid parameter'
    ]);
    exit;
}

/**
 * 1️⃣ VALIDASI CONFIG SITE (MIN/MAX)
 * Pastikan setting sudah lengkap
 */
$stmt = $pdo->prepare("
    SELECT id
    FROM tbl_spc_model_settings
    WHERE user_id = :uid
      AND site_name = :site
      AND file_id IS NOT NULL
      AND interval_width > 0
    LIMIT 1
");
$stmt->execute([
    ':uid'  => $user_id,
    ':site' => $site_name
]);

$cfg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cfg) {
    echo json_encode([
        'success' => false,
        'message' => 'Site configuration incomplete'
    ]);
    exit;
}

/**
 * 2️⃣ MASUKKAN KE QUEUE (PRIORITY)
 * User tidak boleh nunggu
 */
queue_push($pdo, $user_id, $site_name, 1);

/**
 * 3️⃣ (OPSIONAL TAPI DISARANKAN)
 * HAPUS CACHE LAMA BIAR TIDAK KEPAKAI
 */
$stmt = $pdo->prepare("
    DELETE FROM spc_minmax_cache
    WHERE user_id = :uid
      AND site_name = :site
");
$stmt->execute([
    ':uid'  => $user_id,
    ':site' => $site_name
]);

echo json_encode([
    'success' => true,
    'status'  => 'queued',
    'message' => 'Recalculation scheduled'
]);
exit;
