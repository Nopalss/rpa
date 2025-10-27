<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../helper/handlePdoError.php';
$_SESSION['menu'] = 'model_setting';
$_SESSION['halaman'] = 'model setting';

if (isset($_SESSION['form_add_csv'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tbl_filename WHERE application_id = :application_id");
        $stmt->execute([":application_id" => (int) $_SESSION['form_add_csv']['application_id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        handlePdoError($e, "pages/preference/model_setting/create.php");
    }
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
                        <div class="card-header mb-2">
                            <h1 class="card-title mb-0">
                                Add Model
                            </h1>
                        </div>
                        <div class="card-body">
                            <form action="" class="form">
                                <div class="form-group">
                                    <label for="application_name">Application Name <small class="ml-2 text-muted">*Case Sensitive</small></label>
                                    <input type="text" id="application_name" class="form-control" name="aplication_name" value="<?= $_SESSION['form_add_csv']["application_name"] ?? "" ?>">
                                </div>
                                <div class="form-group">
                                    <label for="csv_path">CSV Path </label>
                                    <div class="form-row">
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="application_path" disabled="disabled" value="<?= $_SESSION['form_add_csv']["application_name"] ?? "" ?>">
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" id="csv_path" class="form-control" name="csv_path" placeholder="/csv/..../...(example)" value="<?= $_SESSION['form_add_csv']["csv_path"] ?? "" ?>">
                                        </div>
                            </form>
                        </div>
                        <div class="form-group mt-10">
                            <div class="d-flex justify-content-between align-items-center">

                                <!-- Tombol -->
                                <a href="#" id="addCsvBtn" class="btn btn-primary font-weight-bolder">
                                    Add CSV
                                </a>

                                <!-- Input file tersembunyi -->
                                <form id="csvForm" action="<?= BASE_URL ?>pages/preference/model_setting/add_csv.php" method="post" enctype="multipart/form-data" style="display: none">
                                    <input type="hidden" name="application_name" id="application_name_hidden">
                                    <input type="hidden" name="csv_path" id="csv_path_hidden">
                                    <input type="file" name="csv_file" id="csvFileInput" accept=".csv" />
                                </form>
                            </div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>CSV Name</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($rows)): ?>
                                        <?php if (count($rows) > 0): ?>
                                            <?php foreach ($rows as $r): ?>
                                                <tr>
                                                    <td><?= $r['filename'] ?></td>
                                                    <td><?= $r['create_by'] ?></td>
                                                    <td><?= $r['create_at'] ?></td>
                                                    <td>
                                                        <a href="<?= BASE_URL ?>pages/preference/model_setting/show_csv.php?id=1" class="btn btn-sm btn-primary btn-text-primary " title="Show">
                                                            <div class="d-flex justify-content-center align-items-center">
                                                                <span class="svg-icon svg-icon-md">
                                                                    <i class="flaticon-eye"></i>
                                                                </span>
                                                                <span class="text-white">
                                                                    Show
                                                                </span>
                                                            </div>
                                                        </a>
                                                        <a href="javascript:;" class="btn btn-sm btn-danger btn-text-primary " title="Delete">
                                                            <div class="d-flex justify-content-center align-items-center">
                                                                <span class="svg-icon svg-icon-md">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                            <rect x="0" y="0" width="24" height="24" />
                                                                            <path d="M6,8 L6,20.5 C6,21.3284271 6.67157288,22 7.5,22 L16.5,22 C17.3284271,22 18,21.3284271 18,20.5 L18,8 L6,8 Z" fill="#000000" fill-rule="nonzero" />
                                                                            <path d="M14,4.5 L14,4 C14,3.44771525 13.5522847,3 13,3 L11,3 C10.4477153,3 10,3.44771525 10,4 L10,4.5 L5.5,4.5 C5.22385763,4.5 5,4.72385763 5,5 L5,5.5 C5,5.77614237 5.22385763,6 5.5,6 L18.5,6 C18.7761424,6 19,5.77614237 19,5.5 L19,5 C19,4.72385763 18.7761424,4.5 18.5,4.5 L14,4.5 Z" fill="#000000" opacity="0.3" />
                                                                        </g>
                                                                    </svg>
                                                                </span>
                                                                <span class="text-white">
                                                                    Delete
                                                                </span>
                                                            </div>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4">Tidak ada Csv</td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted h6">Tidak ada Csv</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <table>

                    </table>
                    </form>
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