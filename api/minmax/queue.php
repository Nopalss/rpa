<?php

/**
 * ======================================================
 * SIMPLE DB QUEUE HELPER (MIN/MAX) - FINAL
 * SAFE FOR WINDOWS + PDO + CLI
 * ======================================================
 */

/**
 * ======================================================
 * PUSH JOB KE QUEUE
 * - Idempotent (berdasarkan UNIQUE user_id + site_name)
 * ======================================================
 */
function queue_push(PDO $pdo, int $user_id, string $site_name, int $priority = 5): void
{
    $stmt = $pdo->prepare("
        INSERT INTO spc_minmax_queue
            (user_id, site_name, priority, status, created_at)
        VALUES
            (:uid, :site, :priority, 'pending', NOW())
        ON DUPLICATE KEY UPDATE
            priority   = LEAST(priority, VALUES(priority)),
            status     = IF(status = 'processing', status, 'pending'),
            created_at = NOW()
    ");

    $stmt->execute([
        ':uid'      => $user_id,
        ':site'     => $site_name,
        ':priority' => $priority
    ]);
}

/**
 * ======================================================
 * AMBIL 1 JOB (PRIORITY DULU)
 * - TANPA FOR UPDATE (anti worker mati)
 * - Atomic claim via UPDATE + rowCount()
 * ======================================================
 */
function queue_pop(PDO $pdo): ?array
{
    // 1️⃣ Ambil kandidat job
    $stmt = $pdo->prepare("
        SELECT *
        FROM spc_minmax_queue
        WHERE
            status = 'pending'
            OR (
                status = 'processing'
                AND started_at < (NOW() - INTERVAL 5 MINUTE)
            )
        ORDER BY priority ASC, id ASC
        LIMIT 1
    ");
    $stmt->execute();

    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$job) {
        return null;
    }

    // 2️⃣ Atomic claim (hindari double worker)
    $stmt = $pdo->prepare("
        UPDATE spc_minmax_queue
        SET
            status = 'processing',
            started_at = NOW(),
            error_message = NULL
        WHERE
            id = :id
            AND status IN ('pending', 'processing')
    ");
    $stmt->execute([
        ':id' => $job['id']
    ]);

    // Kalau gagal claim (sudah diambil worker lain)
    if ($stmt->rowCount() === 0) {
        return null;
    }

    return $job;
}

/**
 * ======================================================
 * JOB SELESAI
 * ======================================================
 */
function queue_done(PDO $pdo, int $job_id): void
{
    $stmt = $pdo->prepare("
        UPDATE spc_minmax_queue
        SET
            status = 'done',
            finished_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([':id' => $job_id]);
}

/**
 * ======================================================
 * JOB GAGAL
 * ======================================================
 */
function queue_fail(PDO $pdo, int $job_id, string $error): void
{
    $stmt = $pdo->prepare("
        UPDATE spc_minmax_queue
        SET
            status = 'failed',
            error_message = :err,
            finished_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':id'  => $job_id,
        ':err' => substr($error, 0, 500)
    ]);
}
