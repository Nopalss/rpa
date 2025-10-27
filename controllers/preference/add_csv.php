<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../helper/redirect.php';
require_once __DIR__ . '/../../helper/sanitize.php';
require_once __DIR__ . '/../../helper/handlePdoError.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $application_name = sanitize($_POST['application_name'] ?? '');
        $csv_path = sanitize($_POST['csv_path'] ?? '');
        $file_name = sanitize($_POST['file_name'] ?? '');

        if (empty($application_name) || empty($csv_path) || empty($file_name)) {
            throw new Exception("Application name dan CSV path wajib diisi!");
        }

        // Kumpulkan semua kolom (max 128)
        $columns = [];
        for ($i = 1; $i <= 128; $i++) {
            $key = "column_$i";
            $columns[$key] = isset($_POST[$key]) ? $_POST[$key] : null;
        }

        $pdo->beginTransaction();
        // insert table Application
        if (!isset($_SESSION['form_add_csv']['application_id'])) {
            $sql = "INSERT INTO tbl_application (name, created_by, modify_by) 
                VALUES (:name, :created_by, :modify_by)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":name" => $application_name,
                ":created_by" => $_SESSION['username'],
                ":modify_by" => $_SESSION['username']
            ]);
            $application_id = $pdo->lastInsertId();
        } else {
            $application_id = $_SESSION['form_add_csv']['application_id'];
        }

        // Buat query dinamis
        $fields = ['application_id'];
        $placeholders = [':application_id'];
        $params = [':application_id' => $application_id];
        foreach ($columns as $key => $val) {
            $fields[] = $key;
            $placeholders[] = ":$key";
            $params[":$key"] = $val;
        }

        // Insert table header
        $sql = "INSERT INTO tbl_header (" . implode(',', $fields) . ") 
                VALUES (" . implode(',', $placeholders) . ")";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $header_id = $pdo->lastInsertId();
        // insert table File Name
        $sql = "INSERT INTO tbl_filename (filename, application_id, header_id, create_by) 
                VALUES (:filename, :application_id, :header_id, :create_by)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":filename" => $file_name,
            ":create_by" => $_SESSION['username'],
            ":application_id" => $application_id,
            ":header_id" => $header_id
        ]);
        $filename_id = $pdo->lastInsertId();
        $pdo->commit();

        $_SESSION['form_add_csv'] = [
            "application_name" => $application_name,
            "csv_path" => $csv_path,
            "application_id" => $application_id
        ];

        setAlert('success', "Selamat!", 'Data Berhasil Disimpan!', 'success', 'Oke');
        redirect('pages/preference/model_setting/create.php');
    } catch (Exception $e) {
        // if ($pdo->inTransaction()) {
        //     $pdo->rollBack();
        // }
        // handlePdoError($e, 'pages/preference/model_setting/create.php');
        echo $e;
    }
} else {
    redirect('pages/preference/model_setting/create.php');
}
