<?php
require_once __DIR__ . '/../../../includes/config.php';
$_SESSION['menu'] = 'add_csv_name';
require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/aside.php';
require __DIR__ . '/../../../includes/navbar.php';
?>

<div class="content  d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-6  subheader-solid " id="kt_subheader">
        <div class=" container-fluid  d-flex align-items-center flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-1">

                <!--begin::Page Heading-->
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <!--begin::Page Title-->
                    <h5 class="text-dark font-weight-bold my-1 mr-5">
                        CSV Name </h5>
                </div>
                <!--end::Page Heading-->
            </div>
            <!--end::Info-->
        </div>
    </div>
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
                            Data CSV Name
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
                                </svg><!--end::Svg Icon--></span>Add CSV Name
                        </a>
                        <!--end::Button-->
                    </div>
                </div>
                <div class="card-body">
                    <!--begin: Search Form-->
                    <!--begin::Search Form-->
                    <!-- <div class="mb-7"> -->
                    <!-- <div class="row align-items-center">
                            <div class="col-lg-12 col-xl-12">
                                <div class="row align-items-center">
                                    <div class="col-md-3 my-2 my-md-0">
                                        <div class=" d-flex align-items-center">
                                            <div class="input-group date">
                                                <input type="text" class="form-control" name="date" required name="date" readonly placeholder="mm/dd/yyyy" id="kt_datepicker_3" />
                                                <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="la la-calendar"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                        <button class="btn btn-light-primary">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <!--begin: Datatable-->



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