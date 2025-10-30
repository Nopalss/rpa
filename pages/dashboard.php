<?php
require_once __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/header.php';
$_SESSION['halaman'] = 'dashboard';
$_SESSION['menu'] = 'dashboard';

require __DIR__ . '/../includes/aside.php';
require __DIR__ . '/../includes/navbar.php';
$stmt = $pdo->query("SELECT line_id AS id, line_name FROM tbl_line ORDER BY line_name ASC");
$lines = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<div class="content  d-flex flex-column flex-column-fluid pt-0" id="kt_content">
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class=" container">
            <div class="row">
                <div class="col-sm-6 col-lg-4 mb-2">
                    <div class="bg-white  m-2 p-2 rounded shadow-sm">
                        <div class="d-flex flex-column">
                            <p class="mb-3 pl-5 font-weight-bold">Site 1</p>
                            <div id="chart_19" style="height: 50px"></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 mb-2">
                    <div class="bg-white  m-2 p-2 rounded shadow-sm">
                        <div class="d-flex flex-column">
                            <p class="mb-3 pl-5 font-weight-bold">Site 2</p>
                            <div id="chart_20" style="height: 50px"></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-4 mb-2">
                    <div class="bg-white  m-2 p-2 rounded shadow-sm">
                        <div class="d-flex flex-column">
                            <p class="mb-3 pl-5 font-weight-bold">Site 3</p>
                            <div id="chart_21" style="height: 50px"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 mb-2">
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
                <div class="col-xl-4 row pl-8 mb-4 mt-0">
                    <div class="col-12 p-0 mb-4">
                        <div class="bg-white  m-2 p-2 rounded shadow-sm h-100">
                            <div class="d-flex flex-column">
                                <p class="p-2 mb-2 font-weight-bold">Site 4</p>
                                <div id="chart_15" style="height: 50px"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 p-0 mb-4">
                        <div class="bg-white  m-2 p-2 rounded shadow-sm h-100">
                            <div class="d-flex flex-column">
                                <p class="p-2 mb-2 font-weight-bold">Site 5</p>
                                <div id="chart_16" style="height: 50px"></div>
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
                                    <label class="mr-2 small" for="">Line</label>
                                    <select name="" class="form-control form-control-sm line" data-site="main">
                                        <option value="">Select</option>
                                        <?php foreach ($lines as $l): ?>
                                            <option value="<?= $l['id'] ?>"><?= $l['line_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-xl-3 mb-3 d-flex justify-content-center align-items-center">
                                    <label class="mr-2 small" for="">Application</label>
                                    <select name="" class="form-control form-control-sm application" data-site="main">
                                        <option value="">Select</option>

                                    </select>
                                </div>
                                <div class="col-xl mb-3 d-flex justify-content-center align-items-center">
                                    <label class="mr-2 small" for="">File</label>
                                    <select name="" class="form-control form-control-sm file" data-site="main">
                                        <option value="">Select</option>

                                    </select>
                                </div>
                                <div class="col-xl-2 mb-3 d-flex justify-content-center align-items-center">
                                    <label class="mr-2 small" for="">Header</label>
                                    <select name="" class="form-control form-control-sm headers" data-site="main">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-xl-2 d-flex justify-content-center align-items-center">
                                    <button class="btn btn-info mr-2">Alert</button>
                                    <span class="switch switch-outline switch-icon switch-success">
                                        <label>
                                            <input type="checkbox" checked="checked" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <!-- site -->
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="row mb-7">
                                    <p class="col-xl-12 h6 mb-3 text-muted">Site <?= $i; ?></p>
                                    <div class="col-xl-2 mb-3 d-flex justify-content-center align-items-center">
                                        <label class="mr-2 small" for="">Line</label>
                                        <select class="form-control form-control-sm line" data-site="site<?= $i; ?>">
                                            <option value="">Select</option>
                                            <?php foreach ($lines as $l): ?>
                                                <option value="<?= $l['id'] ?>"><?= $l['line_name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 mb-3 d-flex justify-content-center align-items-center">
                                        <label class="mr-2 small" for="">Application</label>
                                        <select class="form-control form-control-sm application" data-site="site<?= $i; ?>">
                                            <option value="">Select</option>

                                        </select>
                                    </div>
                                    <div class="col-xl mb-3 d-flex justify-content-center align-items-center">
                                        <label class="mr-2 small" for="">File</label>
                                        <select class="form-control form-control-sm file" data-site="site<?= $i; ?>">
                                            <option value="">Select</option>

                                        </select>
                                    </div>
                                    <div class="col-xl-2 mb-3 d-flex justify-content-center align-items-center">
                                        <label class="mr-2 small" for="">Header</label>
                                        <select class="form-control form-control-sm headers" data-site="site<?= $i; ?>">
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-2 d-flex justify-content-center align-items-center">
                                        <button class="btn btn-info mr-2">Alert</button>
                                        <span class="switch switch-outline switch-icon switch-success">
                                            <label>
                                                <input type="checkbox" checked="checked" name="select" />
                                                <span></span>
                                            </label>
                                        </span>
                                    </div>
                                </div>
                            <?php endfor; ?>
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