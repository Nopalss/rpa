<?php
require_once __DIR__ . '/../../includes/config.php';
$_SESSION['menu'] = 'data';
$_SESSION['halaman'] = 'data';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/aside.php';
require __DIR__ . '/../../includes/navbar.php';

$line = '';
$date = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $line = $_POST['line'];
    $date = $_POST['date'];
}

?>

<div class="content  d-flex flex-column flex-column-fluid pt-5" id="kt_content">
    <!--begin::Subheader-->
    <!--end::Subheader-->

    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class=" container ">

            <!--begin::Card-->
            <div class="card card-custom">
                <div class="card-header flex-wrap border-0 pt-6 pb-0">
                    <div class="card-title">
                        <h3 class="card-label">
                            Report Data
                        </h3>
                    </div>
                    <div class="card-toolbar">
                        <!--begin::Button-->
                        <a href="<?= BASE_URL ?>pages/registrasi/create.php" class="btn btn-primary font-weight-bolder">
                            <span class="svg-icon svg-icon-md"><!--begin::Svg Icon | path:C:\wamp64\www\keenthemes\themes\metronic\theme\html\demo1\dist/../src/media/svg/icons\Code\Plus.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <rect x="0" y="0" width="24" height="24" />
                                        <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10" />
                                        <path d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z" fill="#000000" />
                                    </g>
                                </svg><!--end::Svg Icon--></span>New Data
                        </a>
                        <!--end::Button-->
                    </div>
                </div>
                <div class="card-body">
                    <!--begin: Search Form-->
                    <!--begin::Search Form-->
                    <div class="mb-7">


                        <form method="post">
                            <div class="row align-items-center">
                                <div class="col-lg-12 col-xl-12">
                                    <div class="row align-items-center">
                                        <div class="col-md-3 my-2 my-md-0">
                                            <div class="d-flex align-items-center">
                                                <label class="mr-3 mb-0 d-none d-md-block">line:</label>
                                                <select class="form-control" required name="line">
                                                    <option value="">All</option>
                                                    <option value="C12">C12</option>
                                                    <option value="C15">C15</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 my-2 my-md-0">
                                            <div class="d-flex align-items-center">
                                                <label class="mr-3 mb-0 d-none d-md-block">Application:</label>
                                                <select class="form-control" required name="application">
                                                    <option value="">All</option>
                                                    <option value="C12">C12</option>
                                                    <option value="C15">C15</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 my-2 my-md-0">
                                            <div class=" d-flex align-items-center">
                                                <div class="input-group date">
                                                    <input type="date" class="form-control" name="date" required name="date" placeholder="mm/dd/yyyy" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 my-2 my-md-0">
                                            <button class="btn btn-light-primary">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!--begin: Datatable-->

                    <?php if ($_SERVER['REQUEST_METHOD'] === "POST"): ?>
                        <h4 class="mb-3">Data <?= $line . " " . $date ?> </h4>
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                        <div class="text-right">
                            <a href="<?= BASE_URL ?>pages/registrasi/export_excel.php" class="btn mt-5 mr-0 btn-light-success  font-weight-bolder">
                                <span class="svg-icon svg-icon-md text-center"><!--begin::Svg Icon | path:assets/media/svg/icons/Design/Flatten.svg-->
                                    <i class="fas fa-file-csv"></i>
                                </span>Import CSV
                            </a>
                        </div>
                    <?php endif; ?>
                    <!--end: Datatable-->
                </div>
            </div>
            <!--end::Card-->
        </div>
        <!-- end::Container -->
    </div>
</div>
<!-- end::entry -->
<!-- modal detail registrasi-->
<div class="modal fade" id="detailModalRegistrasi" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg">
            <div class="modal-header">
                <h4 class="modal-title"><i class="la la-info-circle text-info"></i> Detail Registrasi</h4>
                <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb-2 pl-2">
                    <div class="col-4 font-weight-bold">Registrasi ID</div>
                    <div class="col-8" id="detail_registrasiId"></div>
                </div>
                <div class="row mb-2 pl-2">
                    <div class="col-4 font-weight-bold">Name</div>
                    <div class="col-8" id="detail_name"></div>
                </div>
                <div class="row mb-2 pl-2">
                    <div class="col-4 font-weight-bold">phone</div>
                    <div class="col-8" id="detail_phone"></div>
                </div>
                <div class="row mb-2 pl-2">
                    <div class="col-4 font-weight-bold">Paket Internet</div>
                    <div class="col-8">
                        <div id="detail_paketInternet"></div>
                    </div>
                </div>
                <div class="row mb-2 pl-2">
                    <div class="col-4 font-weight-bold">Is Verified</div>
                    <div class="col-8">
                        <div id="detail_isVerified"></div>
                    </div>
                </div>
                <div class="row mb-2 pl-2">
                    <div class="col-4 font-weight-bold">Request Schedule</div>
                    <div class="col-8" id="detail_requestSchedule"></div>
                </div>
                <div class="row mb-2 pl-2">
                    <div class="col-4 font-weight-bold">Request Jam</div>
                    <div class="col-8" id="detail_requestJam"></div>
                </div>
                <div class="row mb-2 pl-2">
                    <div class="col-4 font-weight-bold">Location</div>
                    <div class="col-8" id="detail_location"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-dismiss="modal">
                    <i class="la la-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<?php
require __DIR__ . '/../../includes/footer.php';
?>