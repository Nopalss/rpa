<?php
// api/delete_dashboard_setting.php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('User not authenticated.');
        }

        $user_id = $_SESSION['user_id'];
        $site_name = $_POST['site_name'] ?? '';

        // Validasi: Jangan biarkan menghapus site1-site5 (Hard guard)
        if (in_array($site_name, ['site1', 'site2', 'site3', 'site4', 'site5'])) {
            throw new Exception('Site utama tidak boleh dihapus.');
        }

        if (empty($site_name)) {
            throw new Exception('Site name required.');
        }

        // Hapus dari database
        $stmt = $pdo->prepare("DELETE FROM tbl_user_settings WHERE user_id = :uid AND site_name = :site");
        $stmt->execute([':uid' => $user_id, ':site' => $site_name]);

        echo json_encode(['success' => true, 'message' => 'Site deleted.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
