<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../helper/redirect.php';
require_once __DIR__ . '/../../helper/sanitize.php';
require_once __DIR__ . '/../../helper/handlePdoError.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $application_name = sanitize($_POST['application_name'] ?? '');
        $csv_path = sanitize($_POST['csv_path'] ?? '');
        $application_id = sanitize($_POST['application_id'] ?? '');

        if (empty($application_name) || empty($csv_path) || empty($application_id)) {
            setAlert('error', "Oops!", 'Application name dan CSV path wajib diisi!', 'danger', 'Coba Lagi');
            redirect('pages/preference/model_setting/create.php');
        }


        $pdo->beginTransaction();
        $sql = "INSERT INTO tbl_application (id, name, path,created_by, modify_by) 
                VALUES (:id, :name, :path,:created_by, :modify_by)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":id" => $application_id,
            ":name" => $application_name,
            ":path" => $csv_path,
            ":created_by" => $_SESSION['username'],
            ":modify_by" => $_SESSION['username']
        ]);

        $stmtDeleteApp = $pdo->prepare("DELETE FROM tbl_temp_application WHERE id = ?");
        $stmtDeleteApp->execute([$application_id]);

        $pdo->commit();

        unset($_SESSION['form_add_csv']);

        setAlert('success', "Selamat!", 'Data Berhasil Disimpan!', 'success', 'Oke');
        redirect('pages/preference/model_setting/');
    } catch (PDOException $e) {
        // if ($pdo->inTransaction()) {
        //     $pdo->rollBack();
        // }
        // handlePdoError($e, 'pages/preference/model_setting/create.php');
        echo $e;
    }
} else {
    redirect('pages/preference/model_setting/create.php');
}
