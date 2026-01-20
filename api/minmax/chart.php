<?php

/**
 * ======================================================
 * MIN/MAX CHART API (CACHE FIRST)
 * NO HEAVY CALCULATION HERE
 * ======================================================
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/queue.php';

header('Content-Type: application/json');
set_time_limit(5);

$user_id   = $_SESSION['user_id'] ?? 0;
$site_name = $_POST['site_name'] ?? null;

if (!$user_id || !$site_name) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid parameter'
    ]);
    exit;
}

/**
 * ======================================================
 * 1️⃣ CARI CACHE TERBARU
 * ======================================================
 */
$stmt = $pdo->prepare("
    SELECT result_json, calculated_at
    FROM spc_minmax_cache
    WHERE user_id = :uid
      AND site_name = :site
    LIMIT 1
");
$stmt->execute([
    ':uid'  => $user_id,
    ':site' => $site_name
]);

$cache = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cache && !empty($cache['result_json'])) {

    $data = json_decode($cache['result_json'], true);

    if (is_array($data)) {
        echo json_encode([
            'success'        => true,
            'from_cache'     => true,
            'calculated_at'  => $cache['calculated_at'],
            'data'           => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * ======================================================
 * 2️⃣ CACHE BELUM ADA → MASUKKAN QUEUE (NON PRIORITY)
 * ======================================================
 */
queue_push($pdo, $user_id, $site_name, 5);

/**
 * ======================================================
 * 3️⃣ RESPON CEPAT (USER TIDAK NUNGGU)
 * ======================================================
 */
echo json_encode([
    'success'   => true,
    'status'    => 'processing',
    'message'   => 'Data sedang diproses'
]);
exit;
