<?php
require_once __DIR__ . '/../../includes/config.php';
// require_once __DIR__ . '/../../helper/redirect.php';
// require_once __DIR__ . '/../../helper/sanitize.php';
// require_once __DIR__ . '/../../helper/handlePdoError.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        $date = sanitize($_POST['date'] ?? '');
        $time = sanitize($_POST['time'] ?? '');
        $line_id = sanitize($_POST['line_id'] ?? '');
        $model_id = sanitize($_POST['model_id'] ?? '');
        $file_id = sanitize($_POST['file_id'] ?? '');
        $header_id = sanitize($_POST['header_id'] ?? '');

        if (empty($line_id) || empty($model_id) || empty($file_id) || empty($header_id) || empty($date) || empty($time)) {
            throw new Exception("Tolongg isi form dengan benar");
        }

        // Kumpulkan semua data (max 128)
        $columns = [];
        for ($i = 1; $i <= 128; $i++) {
            $key = "data_$i";
            $columns[$key] = isset($_POST[$key]) ? $_POST[$key] : null;
        }

        // Buat query dinamis
        $fields = ['date', 'time', 'line_id', 'model_id', 'file_id', 'header_id'];
        $placeholders = [':date', ':time', ':line_id', ':model_id', ':file_id', ':header_id'];
        $params = [
            ':date' => $date,
            ':time' => $time,
            ':line_id' => $line_id,
            ':model_id' => $model_id,
            ':file_id' => $file_id,
            ':header_id' => $header_id
        ];
        foreach ($columns as $key => $val) {
            $fields[] = $key;
            $placeholders[] = ":$key";
            $params[":$key"] = $val;
        }

        // Insert table header
        $sql = "INSERT INTO tbl_data (" . implode(',', $fields) . ") 
                VALUES (" . implode(',', $placeholders) . ")";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Selamat, data berhasil ditambahkan"
        ]);

        // setAlert('success', "Selamat!", 'Data Berhasil Disimpan!', 'success', 'Oke');
        // redirect('pages/preference/model_setting/create.php');
    } catch (Exception $e) {
        // if ($pdo->inTransaction()) {
        //     $pdo->rollBack();
        // }
        // handlePdoError($e, 'pages/preference/model_setting/create.php');
        http_response_code(400);
        echo json_encode([
            "error" => true,
            "message" => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "error" => true,
        "message" => "Invalid request method. Only POST allowed."
    ]);
}
