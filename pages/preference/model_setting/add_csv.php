<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../helper/redirect.php';
$_SESSION['menu'] = 'model_setting';
$_SESSION['halaman'] = 'add_csv';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $fileTmpPath = $_FILES['csv_file']['tmp_name'];
    $rows = [];
    $header = [];
    $previewRows = [];

    // Baca isi file mentah
    $raw = file_get_contents($fileTmpPath);

    // Deteksi encoding lebih agresif
    $encoding = mb_detect_encoding($raw, ['UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1', 'WINDOWS-1252'], true);

    // Jika bukan UTF-8, ubah ke UTF-8
    if ($encoding && $encoding !== 'UTF-8') {
        $utf8 = iconv($encoding, 'UTF-8//IGNORE', $raw);
        // Buat file temporer versi UTF-8
        $tmpUtf8 = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($tmpUtf8, $utf8);
        $fileToRead = $tmpUtf8;
    } else {
        $fileToRead = $fileTmpPath;
    }

    // Sekarang baca file UTF-8
    if (($handle = fopen($fileToRead, 'r')) !== false) {
        // Hapus BOM
        $firstLine = fgets($handle);
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $delimiter = str_contains($firstLine, ';') ? ';' : (str_contains($firstLine, ',') ? ',' : "\t");
        rewind($handle);

        // Ambil maksimal 5 baris untuk preview
        $maxPreview = 5;
        $rowIndex = 0;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty(array_filter($data))) continue;
            $previewRows[] = array_map(fn($v) => trim($v), $data);
            if (++$rowIndex >= $maxPreview) break;
        }

        fclose($handle);
    }

    // hapus file temp (jika ada)
    if (isset($tmpUtf8) && file_exists($tmpUtf8)) {
        unlink($tmpUtf8);
    }


    $i = 1;
    $application_name = $_POST['application_name'];
    $application_id = $_POST['application_id'] ?? null;
    $csv_path = $_POST['csv_path'];
    $action = $_POST['action'];
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
                                    <td class="h6 font-weight-normal" style="width: 88%"><?= $application_name ?></td>
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
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td class="d-flex justify-content-end">
                                        <select id="headerSelector" class="form-control" style="width:160px;">
                                            <?php foreach ($previewRows as $index => $row): ?>
                                                <option value="<?= $index ?>">Row <?= $index + 1 ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <form action="<?= BASE_URL ?>controllers/preference/<?= $action ?>_csv.php" method="post">
                                <input type="hidden" name="application_id" value="<?= $application_id ?>">
                                <input type="hidden" name="application_name" value="<?= $application_name ?>">
                                <input type="hidden" name="csv_path" value="<?= $csv_path ?>">
                                <input type="hidden" name="file_name" value="<?= $file_name ?>">
                                <table class="table table-striped" id="selectedHeaderTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%;">Column</th>
                                            <th style="width: 80%;">Value</th>
                                        </tr>
                                    </thead>
                                    <!-- <tbody>
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
                                    </tbody> -->
                                    <tbody></tbody>
                                </table>
                                <div class="w-full text-right">
                                    <a href="javascript:history.back()" class="btn btn-danger btn-safe-navigation">Cancel</a>
                                    <button class="btn btn-primary btn-safe-navigation" type="submit">Submit</button>
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