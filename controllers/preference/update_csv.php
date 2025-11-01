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

        // Kumpulkan semua kolom untuk table header1 (max 190)
        $columns1 = [];
        for ($i = 1; $i <= 190; $i++) {
            $key = "column_$i";
            // PERBAIKAN XSS: Lakukan sanitasi di sini
            $columns1[$key] = isset($_POST[$key]) ? sanitize($_POST[$key]) : null;
        }
        // Kumpulkan semua kolom untuk table header2 (max 190)
        $columns2 = [];
        for ($i = 191; $i <= 380; $i++) {
            $key = "column_$i";
            // PERBAIKAN XSS: Lakukan sanitasi di sini
            $columns2[$key] = isset($_POST[$key]) ? sanitize($_POST[$key]) : null;
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

        //  table header 1 & 2
        //  membuat record_id
        $stmt_max = $pdo->query("SELECT MAX(header_id) as max_id FROM tbl_header");
        $max_id = $stmt_max->fetchColumn();

        //  Hitung record_no 
        $record_no = (int)$max_id + 1;

        // Buat query dinamis untuk table header 1 (Ini sudah aman dari SQLi)
        $fields1 = ['file_id', 'record_no'];
        $placeholders1 = [':file_id', ':record_no'];
        $params1 = [
            ':file_id' => $file_id,
            ':record_no' => $record_no
        ];

        foreach ($columns1 as $key => $val) {
            $fields1[] = $key;
            $placeholders1[] = ":$key";
            $params1[":$key"] = $val; // $val sekarang sudah disanitasi
        }

        // Insert table header 1
        $sql = "INSERT INTO tbl_header (" . implode(',', $fields1) . ") 
                VALUES (" . implode(',', $placeholders1) . ")";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params1);

        // Buat query dinamis untuk table header 1 (Ini sudah aman dari SQLi)
        $fields2 = ['file_id', 'record_no'];
        $placeholders2 = [':file_id', ':record_no'];
        $params2 = [
            ':file_id' => $file_id,
            ':record_no' => $record_no
        ];

        foreach ($columns2 as $key => $val) {
            $fields2[] = $key;
            $placeholders2[] = ":$key";
            $params2[":$key"] = $val; // $val sekarang sudah disanitasi
        }

        // Insert table header 1
        $sql = "INSERT INTO tbl_header2 (" . implode(',', $fields2) . ") 
                VALUES (" . implode(',', $placeholders2) . ")";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params2);

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
