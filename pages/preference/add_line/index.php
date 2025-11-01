<?php
require_once __DIR__ . '/../../../includes/config.php';
$_SESSION['menu'] = 'add_line';
$_SESSION['halaman'] = 'line';
require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/clear_temp_session.php';
require __DIR__ . '/../../../includes/aside.php';
require __DIR__ . '/../../../includes/navbar.php';
?>

<div class="content  d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class=" container ">
            <!--begin::Card-->
            <div class="card card-custom">
                <div class="card-header flex-wrap border-0 pt-6 pb-0">
                    <div class="card-title">
                        <h3 class="card-label">
                            Data Line
                        </h3>
                    </div>
                    <div class="card-toolbar">
                        <!--begin::Button-->
                        <button class="btn btn-primary font-weight-bolder" id="addLineBtn">
                            <span class="svg-icon svg-icon-md"><!--begin::Svg Icon | path:C:\wamp64\www\keenthemes\themes\metronic\theme\html\demo1\dist/../src/media/svg/icons\Code\Plus.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <rect x="0" y="0" width="24" height="24" />
                                        <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10" />
                                        <path d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z" fill="#000000" />
                                    </g>
                                </svg><!--end::Svg Icon--></span>Add Line
                        </button>
                        <!--end::Button-->
                    </div>



                </div>
                <div class="card-body">
                    <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    <div class="text-right">
                        <a href="<?= BASE_URL ?>pages/registrasi/export_excel.php" class="btn mt-5 mr-0 btn-light-success  font-weight-bolder">
                            <span class="svg-icon svg-icon-md text-center"><!--begin::Svg Icon | path:assets/media/svg/icons/Design/Flatten.svg-->
                                <i class="fas fa-file-csv"></i>
                            </span>Import CSV
                        </a>
                    </div>

                    <!--end: Datatable-->
                </div>
            </div>
            <!--end::Card-->
        </div>
        <!-- end::Container -->
    </div>
</div>
<!-- end::entry -->




<?php
require __DIR__ . '/../../../includes/footer.php';
?>