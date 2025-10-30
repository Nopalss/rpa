<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../helper/redirect.php';
require_once __DIR__ . '/../../helper/sanitize.php';
require_once __DIR__ . '/../../helper/handlePdoError.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $application_name = sanitize($_POST['application_name'] ?? '');
        $application_id = sanitize($_POST['application_id'] ?? '');
        $csv_path = sanitize($_POST['csv_path'] ?? '');
        $file_name = sanitize($_POST['file_name'] ?? '');

        if (empty($application_name) || empty($application_id) || empty($csv_path) || empty($file_name)) {
            throw new Exception("Application name dan CSV path wajib diisi!");
        }

        // Kumpulkan semua kolom (max 128)
        $columns = [];
        for ($i = 1; $i <= 128; $i++) {
            $key = "column_$i";
            $columns[$key] = isset($_POST[$key]) ? $_POST[$key] : null;
        }

        $pdo->beginTransaction();
        if (!isset($_SESSION['form_add_csv']['application_id'])) {
            // insert table Application
            $sql = "INSERT INTO tbl_temp_application (id, name, path) 
                VALUES (:id, :name, :path)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":id" => $application_id,
                ":name" => $application_name,
                ":path" => $csv_path,
            ]);
        } else {
            $application_id = $_SESSION['form_add_csv']['application_id'];
        }

        // insert table File Name
        $sql = "INSERT INTO tbl_filename (filename, temp_id, create_by) 
                VALUES (:filename, :temp_id, :create_by)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":filename" => $file_name,
            ":create_by" => $_SESSION['username'],
            ":temp_id" => $application_id,
        ]);
        $file_id = $pdo->lastInsertId();

        // Buat query dinamis
        $fields = ['file_id'];
        $placeholders = [':file_id'];
        $params = [':file_id' => $file_id];
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


        $pdo->commit();

        $_SESSION['form_add_csv'] = [
            "application_name" => $application_name,
            "csv_path" => $csv_path,
            "application_id" => $application_id
        ];

        setAlert('success', "Selamat!", 'Data Berhasil Disimpan!', 'success', 'Oke');
        redirect("pages/preference/model_setting/update.php?id=$application_id");
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        handlePdoError($e, "pages/preference/model_setting/update.php?id=$application_id");
    }
} else {
    redirect("pages/preference/model_setting/");
}
