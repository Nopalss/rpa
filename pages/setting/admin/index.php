<?php
require_once __DIR__ . '/../../../includes/config.php';
$_SESSION['menu'] = 'admin';
$_SESSION['halaman'] = 'admin';
require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/aside.php';
require __DIR__ . '/../../../includes/navbar.php';

$user_id = $_SESSION['user_id'] ?? 0;

$user_settings = [];
if ($user_id > 0) {
    // 1. Ambil semua pengaturan untuk user ini dari DB
    $stmt_settings = $pdo->prepare("
    SELECT 
        u.header_name,
        u.second,
        u.site_name, 
        l.line_name, 
        a.name AS application_name, 
        f.filename
    FROM tbl_user_settings u
    JOIN tbl_line l ON u.line_id = l.line_id
    JOIN tbl_application a ON u.application_id = a.id
    JOIN tbl_filename f ON u.file_id = f.file_id
    WHERE u.user_id = :user_id
");
    $stmt_settings->execute([':user_id' => $user_id]);
    $results = $stmt_settings->fetchAll(PDO::FETCH_ASSOC);
    $color = [
        "main" => "primary",
        "site1" => "success",
        "site2" => "danger",
        "site3" => "warning",
        "site4" => "info",
        "site5" => "secondary",
    ];
}
?>

<div class="content  d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class=" container ">
            <!--begin::Card-->
            <div class="card mb-6 shadow-sm ">
                <div class="card-header flex-wrap border-0 pt-6 pb-0">
                    <div class="card-title">
                        <h3 class="card-label font-weight-bolder">
                            <span class="svg-icon svg-icon-primary svg-icon-2x"><!--begin::Svg Icon | path:C:\wamp64\www\keenthemes\themes\metronic\theme\html\demo1\dist/../src/media/svg/icons\General\Settings-2.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <rect x="0" y="0" width="24" height="24" />
                                        <path d="M5,8.6862915 L5,5 L8.6862915,5 L11.5857864,2.10050506 L14.4852814,5 L19,5 L19,9.51471863 L21.4852814,12 L19,14.4852814 L19,19 L14.4852814,19 L11.5857864,21.8994949 L8.6862915,19 L5,19 L5,15.3137085 L1.6862915,12 L5,8.6862915 Z M12,15 C13.6568542,15 15,13.6568542 15,12 C15,10.3431458 13.6568542,9 12,9 C10.3431458,9 9,10.3431458 9,12 C9,13.6568542 10.3431458,15 12,15 Z" fill="#000000" />
                                    </g>
                                </svg><!--end::Svg Icon--></span>
                            Settings
                        </h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php foreach ($results as $r): ?>
                    <div class="col-lg-6 mb-7">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <span class="bullet bullet-bar bg-<?= $color[$r['site_name']] ?> align-self-stretch"></span>
                                    <h4 class="text-capitalize font-weight-bolder m-0 ml-5"><?= $r['site_name'] ?></h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <tr>
                                        <th class="align-middle">Line</th>
                                        <td class="align-middle">:</td>
                                        <td class="align-middle"><?= $r['line_name'] ?></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle">Application</th>
                                        <td class="align-middle">:</td>
                                        <td class="align-middle"><?= $r['application_name'] ?></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle">File Name</th>
                                        <td class="align-middle">:</td>
                                        <td class="align-middle"><?= $r['filename'] ?></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle">Header Name</th>
                                        <td class="align-middle">:</td>
                                        <td class="align-middle"><?= $r['header_name'] ?></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle">Interval</th>
                                        <td class="align-middle">:</td>
                                        <td class="d-flex align-middle align-items-center"><input type="text" style="width: 40%;" class="form-control input-interval-second" value="<?= $r['second'] ?>"><span class="btn btn-success mr-2">S</span><span class="btn btn-primary btn-simpan-interval" data-site-name="<?= $r['site_name'] ?>">Update</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!--end::Card-->
        </div>
        <!-- end::Container -->
    </div>
</div>
<!-- end::entry -->
<script>
    // 1. Gunakan event listener JavaScript murni
    // Ini akan menunggu seluruh halaman (termasuk footer.php) selesai di-load
    document.addEventListener('DOMContentLoaded', function() {

        // 2. Sekarang jQuery ($) sudah pasti tersedia
        // Kita pakai jQuery di dalam sini
        $(document).on('click', '.btn-simpan-interval', function() {
            var $btn = $(this); // Tombol "Simpan" yang di-klik

            // Temukan input terdekat
            var $td = $btn.closest('td');
            var $input = $td.find('.input-interval-second');

            // Ambil nilainya
            var newSecond = $input.val();
            var siteName = $btn.data('site-name');

            // Kirim data via AJAX
            $.ajax({
                url: '<?= BASE_URL ?>/api/update_interval.php',
                method: 'POST',
                data: {
                    site_name: siteName,
                    second: newSecond
                },
                dataType: 'json',
                beforeSend: function() {
                    // Ubah teks tombol & disable saat proses kirim
                    $btn.text('Menyimpan...').attr('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        alert('Berhasil disimpan!');
                    } else {
                        alert('Gagal menyimpan: ' + response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan koneksi.');
                },
                complete: function() {
                    // Kembalikan tombol ke kondisi semula
                    $btn.text('Simpan').attr('disabled', false);
                }
            });
        });

    });
</script>

<?php
require __DIR__ . '/../../../includes/footer.php';
?>