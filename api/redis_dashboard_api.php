<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$queue = 'queue:add_data';

if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    $redis->del($queue);
    echo json_encode(['message' => 'Queue cleared']);
    exit;
}

echo json_encode([
    'queueLength' => $redis->lLen($queue),
    'processedCount' => (int)($redis->get('processed_count') ?? 0),
    'failedCount' => (int)($redis->get('failed_count') ?? 0)
]);
