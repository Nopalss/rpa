<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../helper/redirect.php';
$_SESSION['menu'] = 'model_setting';
$_SESSION['halaman'] = 'model setting';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $fileTmpPath = $_FILES['csv_file']['tmp_name'];
    $rows = [];
    $header = [];
    // var_dump($_FILES['csv_file']['name']);
    if (($handle = fopen($fileTmpPath, 'r')) !== false) {

        // Baca baris pertama dan hilangkan BOM (kalau ada)
        $firstLine = fgets($handle);
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $delimiter = str_contains($firstLine, ';') ? ';' : ','; // deteksi delimiter
        rewind($handle);

        // Ambil header CSV
        $header = fgetcsv($handle, 0, $delimiter);

        // Validasi header
        if (!empty($header)) {
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                // Lewatkan baris kosong
                if (empty(array_filter($data))) continue;

                // Samakan jumlah kolom dengan header
                if (count($data) < count($header)) {
                    $data = array_pad($data, count($header), '');
                } elseif (count($data) > count($header)) {
                    $data = array_slice($data, 0, count($header));
                }

                $rows[] = array_combine($header, $data);
            }
        }

        fclose($handle);
    }

    // Kirim data header & contoh value (baris pertama)
    $columns = [];
    if (!empty($header)) {
        foreach ($header as $h) {
            $columns[] = [
                'name' => $h,
                'value' => $rows[0][$h] ?? ''
            ];
        }
    }
    $i = 1;
    $application_name = $_POST['application_name'];
    $csv_path = $_POST['csv_path'];
    $file_name = $_FILES['csv_file']['name'];
    if (!isset($_SESSION['form_add_csv'])) {
        $_SESSION['form_add_csv'] = [
            "application_name" => $application_name,
            "csv_path" => $csv_path
        ];
    }
    require __DIR__ . '/../../../includes/header.php';
    require __DIR__ . '/../../../includes/aside.php';
    require __DIR__ . '/../../../includes/navbar.php';
} else {
    redirect("pages/preference/model_setting/create.php");
}
?>
<div class="content  d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <div class="f-flex justify-content-center align-items-center">
                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-header">
                            <h4>Add CSV</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="h6" style="width:10% ;">Application</th>
                                    <td style="width:2% ;">:</td>
                                    <td class="h6 font-weight-normal"><?= $application_name ?></td>
                                </tr>
                                <tr>
                                    <th class="h6">Path</th>
                                    <td>:</td>
                                    <td class="h6 font-weight-normal"><?= $csv_path ?></td>
                                </tr>
                                <tr>
                                    <th class="h6">File Name</th>
                                    <td>:</td>
                                    <td class="h6 font-weight-normal"><?= $file_name ?></td>
                                </tr>
                            </table>
                            <form action="<?= BASE_URL ?>controllers/preference/add_csv.php" method="post">
                                <input type="hidden" name="application_name" value="<?= $application_name ?>">
                                <input type="hidden" name="csv_path" value="<?= $csv_path ?>">
                                <input type="hidden" name="file_name" value="<?= $file_name ?>">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%;">Column</th>
                                            <th style="width: 80%;">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($columns)): ?>
                                            <?php foreach ($columns as $c): ?>
                                                <tr>
                                                    <td>Column <?= $i ?></td>
                                                    <td><?= htmlspecialchars($c['name']) ?></td>
                                                    <td><input type="hidden" name="column_<?= $i ?>" value="<?= htmlspecialchars($c['name']) ?>"></td>
                                                </tr>
                                                <?php $i++ ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="2">No data found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                <div class="w-full text-right">
                                    <a href="javascript:history.back()" class="btn btn-danger">Cancel</a>
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
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