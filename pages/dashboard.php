<?php
require_once __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/header.php';
$_SESSION['menu'] = 'dashboard';
require __DIR__ . '/../includes/aside.php';
require __DIR__ . '/../includes/navbar.php';
?>


<div class="content  d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-6  subheader-solid " id="kt_subheader">
        <div class=" container-fluid  d-flex align-items-center justify-conten bt-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-1">

                <!--begin::Page Heading-->
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <!--begin::Page Title-->
                    <h5 class="text-dark font-weight-bold my-1 mr-5">
                        Dashboard </h5>
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
            <div class="row d-flex align-items-stretch">
                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="bg-white  m-2 p-2 d-flex rounded shadow-sm h-100 align-items-center">
                        <div class=" w-25 p-3 text-center rounded font-weight-bold d-flex justify-content-center align-items-center mr-5">
                            <i class="flaticon2-hourglass-1 text-primary icon-2x"></i>
                        </div>
                        <div class="d-flex flex-column">
                            <p class="text-muted mb-2">Report</p>
                            <h3>20</h3>
                        </div>
                    </div>
                </div>
                <!-- card -->
                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="bg-white m-2 p-2 d-flex rounded shadow-sm h-100 align-items-center">
                        <div class="w-25 p-3 text-center rounded font-weight-bold d-flex justify-content-center align-items-center mr-5">
                            <i class="flaticon2-hourglass-1 text-info icon-2x"></i>
                        </div>
                        <div class="d-flex flex-column">
                            <p class="text-muted mb-2">Report</p>
                            <h3>9</h3>
                        </div>
                    </div>
                </div>

                <!-- end card -->

                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="bg-white m-2 p-2 d-flex rounded shadow-sm h-100 align-items-center">
                        <div class=" w-25 p-3 text-center rounded font-weight-bold d-flex justify-content-center align-items-center mr-5">
                            <i class="flaticon2-hourglass-1 text-warning icon-2x"></i>
                        </div>
                        <div class="d-flex flex-column">
                            <p class="text-muted mb-2">Report </p>
                            <h3>9</h3>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="bg-white  m-2 p-2 d-flex rounded shadow-sm h-100 align-items-center">
                        <div class=" w-25 p-3 text-center rounded font-weight-bold d-flex justify-content-center align-items-center mr-5">
                            <i class="flaticon2-hourglass-1 text-danger icon-2x"></i>
                        </div>
                        <div class="d-flex flex-column">
                            <p class="text-muted mb-2">Report</p>
                            <h3>9</h3>
                        </div>
                    </div>
                </div>
                <!-- end card -->

                <!-- begin:: card schedule -->
                <div class="col-xl-12 mt-10">
                    <div class="card shadow">
                        <div class="card-header pb-2  d-flex justify-content-between align-items-center ">
                            <h3 class="card-title mb-2">
                                <a class="card-label text-dark">Reports</a>
                            </h3>
                            <div class="card-toolbar">
                                <select name="" id="" class="form-control">
                                    <option value="">
                                        select
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <div id="chart_2"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 mt-10">
                    <div class="card shadow">
                        <div class="card-header pb-2  d-flex justify-content-between align-items-center ">
                            <h3 class="card-title mb-2">
                                <a class="card-label text-dark">Reports</a>
                            </h3>
                            <div class="card-toolbar">
                                <select name="" id="" class="form-control">
                                    <option value="">
                                        select
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <div id="chart_14"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 mt-10">
                    <div class="card shadow">
                        <div class="card-header pb-2  d-flex justify-content-between align-items-center ">
                            <h3 class="card-title mb-2">
                                <a class="card-label text-dark">Reports</a>
                            </h3>
                            <div class="card-toolbar">
                                <select name="" id="" class="form-control">
                                    <option value="">
                                        select
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <div id="chart_7"></div>
                        </div>
                    </div>
                </div>
                <!-- end: card schedule -->
                <!-- begin:: card report -->
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Entry-->
</div>

<?php
require __DIR__ . '/../includes/footer.php';
?>