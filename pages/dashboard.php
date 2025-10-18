<?php
require_once __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/header.php';
$_SESSION['menu'] = 'dashboard';
require __DIR__ . '/../includes/aside.php';
require __DIR__ . '/../includes/navbar.php';
$line = [
    'C1',
    'C2',
    'C3',
];
$model = [
    'Daihatsu',
    'Honda',
    'Yamaha',
];
$histogram = [
    'main',
    'aside',
    'site'
];
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
            <div class="row">
                <div class="col-sm-6 col-lg-4 mb-4">
                    <div class="bg-white  m-2 p-2 rounded shadow-sm h-100 ">
                        <div class="d-flex flex-column">
                            <p class="p-2 mb-2 font-weight-bold">Report</p>
                            <div id="chart_19"></div>
                        </div>
                    </div>
                </div>
                <!-- card -->
                <div class="col-sm-6 col-lg-4 mb-4">
                    <div class="bg-white m-2 p-2 rounded shadow-sm h-100 ">
                        <div class="d-flex flex-column">
                            <p class="p-2 mb-2 font-weight-bold">Report</p>
                            <div id="chart_20"></div>
                        </div>
                    </div>
                </div>

                <!-- end card -->

                <div class="col-sm-12 col-lg-4 mb-4">
                    <div class="bg-white m-2 p-2  rounded shadow-sm h-100 ">
                        <div class="d-flex flex-column">
                            <p class="p-2 mb-2 font-weight-bold">Report</p>
                            <div id="chart_21"></div>
                        </div>
                    </div>
                </div>
                <!-- end card -->
                <!-- begin:: card schedule -->
                <div class="col-xl-8 mt-10">
                    <div class="card shadow">
                        <div class="card-header pb-2  d-flex justify-content-between align-items-center ">
                            <h3 class="card-title d-flex align-items-center mb-2">
                                <a class="card-label text-dark">Main</a>
                                <!-- <div style="width: 25px; height: 25px;" class="ml-2 rounded-circle bg-success"></div> -->
                                <i class="flaticon-warning text-danger mb-0 font-weight-bold ml-2 h3"></i>
                            </h3>
                        </div>
                        <div class="card-body pt-2">
                            <div id="chart_2"></div>
                            <!-- select card -->
                            <select name="" id="" class="form-control text-center">
                                <option value="main">Main</option>
                                <option value="mobil">Mobil</option>
                            </select>

                        </div>
                    </div>
                </div>
                <div class="col-xl-4 row mt-10 mb-4">
                    <div class="col-12 p-0 mb-4">
                        <div class="bg-white  m-2 p-2 rounded shadow-sm h-100">
                            <div class="d-flex flex-column">
                                <p class="p-2 mb-2 font-weight-bold">Report</p>
                                <div id="chart_15"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 p-0 mb-4">
                        <div class="bg-white  m-2 p-2  rounded shadow-sm h-100 ">
                            <div class="d-flex flex-column">
                                <p class="p-2 mb-2 font-weight-bold">Report</p>
                                <div id="chart_16"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 p-0 mb-4">
                        <div class="bg-white  m-2 p-2 rounded shadow-sm h-100 ">
                            <div class="d-flex flex-column">
                                <p class="p-2 mb-2 font-weight-bold">Report</p>
                                <div id="chart_17"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 p-0 mb-4">
                        <div class="bg-white  m-2 p-2 rounded shadow-sm h-100 ">
                            <div class="d-flex flex-column">
                                <p class="p-2 mb-2 font-weight-bold">Report</p>
                                <div id="chart_18"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12 mt-5">
                    <div class="card">
                        <div class="card-body ">
                            <!-- main -->
                            <div class="row mb-7">
                                <p class="col-xl-12 h6 mb-3 text-muted">Main</p>
                                <div class="col-xl-2 mb-3 d-flex justify-content-center align-items-center">
                                    <label class="mr-2" for="">Line</label>
                                    <select name="" id="" class="form-control form-control-sm">
                                        <option value="">Select</option>
                                        <?php foreach ($line as $l): ?>
                                            <option value="<?= $l ?>"><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-xl-2 mb-3 d-flex justify-content-center align-items-center">
                                    <label class="mr-2" for="">Model</label>
                                    <select name="" id="" class="form-control form-control-sm">
                                        <option value="">Select</option>
                                        <?php foreach ($model as $m): ?>
                                            <option value="<?= $m ?>"><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-xl mb-3">
                                    <select name="" id="" class="form-control form-control-sm">
                                        <option value="">Select</option>
                                        <?php foreach ($histogram as $h): ?>
                                            <option value="<?= $h ?>"><?= $h ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-xl-3 d-flex justify-content-center align-items-center">
                                    <button class="btn btn-info mr-2">Alert</button>
                                    <span class="switch switch-outline switch-icon switch-success">
                                        <label>
                                            <input type="checkbox" checked="checked" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <!-- asite 1 -->
                            <div class="row mb-7">
                                <p class="col-xl-12 h6 mb-3 text-muted">Side 1</p>
                                <div class="col-xl-2  mb-3 d-flex justify-content-center align-items-center">
                                    <label class="mr-2" for="">Line</label>
                                    <select name="" id="" class="form-control form-control-sm">
                                        <option value="">Select</option>
                                        <?php foreach ($line as $l): ?>
                                            <option value="<?= $l ?>"><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-xl-2  mb-3 d-flex justify-content-center align-items-center">
                                    <label class="mr-2" for="">Model</label>
                                    <select name="" id="" class="form-control form-control-sm">
                                        <option value="">Select</option>
                                        <?php foreach ($model as $m): ?>
                                            <option value="<?= $m ?>"><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-xl  mb-3">
                                    <select name="" id="" class="form-control form-control-sm">
                                        <option value="">Select</option>
                                        <?php foreach ($histogram as $h): ?>
                                            <option value="<?= $h ?>"><?= $h ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-xl-3 d-flex justify-content-center align-items-center">
                                    <button class="btn btn-info mr-2">Alert</button>
                                    <span class="switch switch-outline switch-icon switch-success">
                                        <label>
                                            <input type="checkbox" checked="checked" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <!-- asite 2 -->
                            <div class="row mb-7">
                                <p class="col-xl-12 h6 mb-3 text-muted">Side 2</p>
                                <div class="col-xl-2  mb-3 d-flex justify-content-center align-items-center">
                                    <label class="mr-2" for="">Line</label>
                                    <select name="" id="" class="form-control form-control-sm">
                                        <option value="">Select</option>
                                        <?php foreach ($line as $l): ?>
                                            <option value="<?= $l ?>"><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-xl-2  mb-3 d-flex justify-content-center align-items-center">
                                    <label class="mr-2" for="">Model</label>
                                    <select name="" id="" class="form-control form-control-sm">
                                        <option value="">Select</option>
                                        <?php foreach ($model as $m): ?>
                                            <option value="<?= $m ?>"><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-xl  mb-3">
                                    <select name="" id="" class="form-control form-control-sm">
                                        <option value="">Select</option>
                                        <?php foreach ($histogram as $h): ?>
                                            <option value="<?= $h ?>"><?= $h ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-xl-3 d-flex justify-content-center align-items-center">
                                    <button class="btn btn-info mr-2">Alert</button>
                                    <span class="switch switch-outline switch-icon switch-success">
                                        <label>
                                            <input type="checkbox" checked="checked" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Entry-->
</div>

<?php
require __DIR__ . '/../includes/footer.php';
?>