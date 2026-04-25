<?php

/**
 * ==========================================================
 * SAVE SPC MODEL SETTING (MIN / MAX RANGE)
 * File : api/save_spc_model_setting.php
 *
 * NOTE :
 * - KHUSUS simpan model min / max
 * - TIDAK campur dashboard lama
 * - TIDAK sentuh perhitungan CP / CPK
 * - FIXED sesuai struktur tabel tbl_spc_model_settings
 * ==========================================================
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

try {
    // ==================================================
    // SESSION & USER
    // ==================================================
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        throw new Exception('User not authenticated');
    }

    // ==================================================
    // AMBIL JSON BODY
    // ==================================================
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    if (!is_array($data)) {
        throw new Exception("Invalid JSON payload");
    }

    // ==================================================
    // VALIDASI MINIMAL
    // ==================================================
    $required = ['site_name', 'file_id', 'agg_mode', 'start_col', 'end_col'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // ==================================================
    // BASIC INFO
    // ==================================================
    $siteName  = $data['site_name'];
    $siteLabel = $data['site_label'] ?? null;

    $lineId = $data['line_id'] ?? null;
    $appId  = $data['application_id'] ?? null;
    $fileId = $data['file_id'];

    // ==================================================
    // MODEL MIN / MAX
    // ==================================================
    $aggMode  = in_array($data['agg_mode'], ['min', 'max']) ? $data['agg_mode'] : null;
    $startCol = (int) $data['start_col'];
    $endCol   = (int) $data['end_col'];

    if ($startCol <= 0 || $endCol <= 0 || $startCol > $endCol) {
        throw new Exception("Invalid column range");
    }

    // ==================================================
    // HISTOGRAM / SPC SETTING
    // ==================================================
    $standardLower = $data['standard_lower']
        ?? $data['custom_lcl']
        ?? null;

    $standardUpper = $data['standard_upper']
        ?? $data['custom_ucl']
        ?? null;

    if ($standardLower === null || $standardUpper === null) {
        throw new Exception("Standard Lower / Upper cannot be null");
    }

    $lowerBoundary = $data['lower_boundary'] ?? null;
    $intervalWidth = $data['interval_width'] ?? null;
    $cpLimit  = $data['cp_limit'] ?? null;
    $cpkLimit = $data['cpk_limit'] ?? null;

    // ==================================================
    // UPSERT (INSERT / UPDATE)
    // ==================================================
    $sql = "
        INSERT INTO tbl_spc_model_settings (
            user_id,
            site_name,
            site_label,
            line_id,
            application_id,
            file_id,
            agg_mode,
            start_col,
            end_col,
            standard_lower,
            standard_upper,
            lower_boundary,
            interval_width,
            cp_limit,
            cpk_limit,
            updated_at
        ) VALUES (
            :user_id,
            :site_name,
            :site_label,
            :line_id,
            :application_id,
            :file_id,
            :agg_mode,
            :start_col,
            :end_col,
            :standard_lower,
            :standard_upper,
            :lower_boundary,
            :interval_width,
            :cp_limit,
            :cpk_limit,
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            site_label     = VALUES(site_label),
            line_id        = VALUES(line_id),
            application_id = VALUES(application_id),
            file_id        = VALUES(file_id),
            agg_mode       = VALUES(agg_mode),
            start_col      = VALUES(start_col),
            end_col        = VALUES(end_col),
            standard_lower = VALUES(standard_lower),
            standard_upper = VALUES(standard_upper),
            lower_boundary = VALUES(lower_boundary),
            interval_width = VALUES(interval_width),
            cp_limit       = VALUES(cp_limit),
            cpk_limit      = VALUES(cpk_limit),
            updated_at     = NOW()
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id'        => $userId,
        ':site_name'      => $siteName,
        ':site_label'     => $siteLabel,
        ':line_id'        => $lineId,
        ':application_id' => $appId,
        ':file_id'        => $fileId,
        ':agg_mode'       => $aggMode,
        ':start_col'      => $startCol,
        ':end_col'        => $endCol,
        ':standard_lower' => $standardLower,
        ':standard_upper' => $standardUpper,
        ':lower_boundary' => $lowerBoundary,
        ':interval_width' => $intervalWidth,
        ':cp_limit'       => $cpLimit,
        ':cpk_limit'      => $cpkLimit,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'SPC model setting saved successfully'
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
