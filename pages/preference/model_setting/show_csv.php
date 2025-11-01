<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../helper/redirect.php';
$_SESSION['menu'] = 'model_setting';
$_SESSION['halaman'] = 'Model Setting';


$id = $_GET['id'] ?? null;
try {
    if (!$id) {
        $_SESSION['alert'] = [
            'icon' => 'warning',
            'title' => 'ID tidak ditemukan',
            'text' => 'Parameter ID tidak valid.',
            'button' => 'Oke',
            'style' => 'warning'
        ];
        redirect("pages/preference/model_setting/create.php");
        exit;
    }


    // 2 Gabungkan ke query utama
    $sql = "
        SELECT 
            f.*, 
            h1.*, 
            h2.*,
            COALESCE(a_temp.name, a_perm.name) AS application_name, 
            COALESCE(a_temp.path, a_perm.path) AS application_path
        FROM tbl_filename f
        JOIN tbl_header h1 ON f.file_id = h1.file_id
        JOIN tbl_header2 h2 ON f.file_id = h2.file_id
        LEFT JOIN tbl_temp_application a_temp ON f.temp_id = a_temp.id
        LEFT JOIN tbl_application a_perm ON f.application_id = a_perm.id
        WHERE f.file_id = :file_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":file_id" => $id]);
    $rows = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$rows) {
        $_SESSION['alert'] = [
            'icon' => 'warning',
            'title' => 'Data tidak ditemukan',
            'text' => 'Parameter ID tidak valid.',
            'button' => 'Oke',
            'style' => 'warning'
        ];
        redirect("pages/preference/model_setting/create.php");
    }
} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'icon' => 'danger',
        'title' => 'Oops! Ada yang Salah',
        'text' => 'Silakan coba lagi nanti. Error: ' . $e->getMessage(),
        'button' => "Coba Lagi",
        'style' => "danger"
    ];
    redirect("pages/preference/model_setting/create.php");
}
require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/aside.php';
require __DIR__ . '/../../../includes/navbar.php';
?>
<div class="content  d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <div class="f-flex justify-content-center align-items-center">
                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-body">
                            <a href="javascript:history.back()" class="btn btn-danger btn-safe-navigation"><i class="flaticon2-back"></i> Back</a>
                            <table class="table table-borderless mt-3">
                                <tr>
                                    <th class="h6" style="width:10% ;">Application</th>
                                    <td style="width:2% ;">:</td>
                                    <td class="h6 font-weight-normal" style="width: 88%"><?= $_SESSION['form_add_csv']['application_name'] ?></td>
                                </tr>
                                <tr>
                                    <th class="h6">Path</th>
                                    <td>:</td>
                                    <td class="h6 font-weight-normal"><?= $_SESSION['form_add_csv']['csv_path'] ?></td>
                                </tr>
                                <tr>
                                    <th class="h6">File Name</th>
                                    <td>:</td>
                                    <td class="h6 font-weight-normal"><?= $rows['filename'] ?></td>
                                </tr>
                            </table>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Column</th>
                                        <th style="width: 80%;">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $key => $value) :
                                        // Cek kalau key-nya mulai dengan "column_"
                                        if (strpos($key, 'column_') === 0 && trim($value ?? '') !== '') :
                                    ?>
                                            <tr>
                                                <td><?= $key; ?></td>
                                                <td><?= $value ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end::Container -->
    </div>
</div>
<!-- end::entry -->

<?php
require __DIR__ . '/../../../includes/footer.php';
?>