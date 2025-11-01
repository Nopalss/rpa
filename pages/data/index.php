<?php
require_once __DIR__ . '/../../includes/config.php';
$_SESSION['menu'] = 'add_data';
$_SESSION['halaman'] = 'data';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/clear_temp_session.php';
require __DIR__ . '/../../includes/aside.php';
require __DIR__ . '/../../includes/navbar.php';

$stmt = $pdo->query("SELECT line_id AS id, line_name FROM tbl_line ORDER BY line_name ASC");
$lines = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

                </div>
                <div class="card-body">
                    <!--begin: Search Form-->
                    <!--begin::Search Form-->
                    <div class="mb-7">
                        <form class="form" id="kt_form_filter">
                            <div class="row align-items-center">
                                <div class="col-lg-12 col-xl-12">
                                    <div class="row align-items-center">
                                        <div class="col-md-3 my-2 my-md-0">
                                            <div class="d-flex align-items-center">
                                                <label class="mr-3 mb-0 d-none d-md-block line-data">line:</label>
                                                <select class="form-control line2" id="filter_line_id" required>
                                                    <option value="">Select</option>
                                                    <?php foreach ($lines as $l): ?>
                                                        <option value="<?= $l['id'] ?>"><?= $l['line_name'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 my-2 my-md-0">
                                            <div class="d-flex align-items-center">
                                                <label class="mr-3 mb-0 d-none d-md-block application-data">Application:</label>
                                                <select class="form-control application2" id="filter_application_id" required>
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 my-2 my-md-0">
                                            <div class=" d-flex align-items-center">
                                                <div class="input-group date">
                                                    <input type="date" class="form-control" id="filter_date" required placeholder="mm/dd/yyyy" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 my-2 my-md-0">
                                            <button class="btn btn-light-primary" id="kt_filter_submit">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!--end::Card-->
            <div class="card card-custom gutter-b">
            </div>

            <div class="card card-custom" id="kt_datatable_card" style="display: none;">
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 10%;">
                                <h5 class="">Line</h5>
                            </th>
                            <td style="width: 5%;">
                                <h5>:</h5>
                            </td>
                            <td style="width: 85%;">
                                <h5 id="line" class="font-weight-normal"></h5>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <h5>Application</h5>
                            </th>
                            <td>
                                <h5>:</h5>
                            </td>
                            <td>
                                <h5 id="application" class="font-weight-normal"></h5>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <h5>Date</h5>
                            </th>
                            <td>
                                <h5>:</h5>
                            </td>
                            <td>
                                <h5 id="date" class="font-weight-normal"></h5>
                            </td>
                        </tr>
                    </table>
                    <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                </div>
            </div>
        </div>
        <!-- end::Container -->
    </div>
</div>
<!-- end::entry -->


<?php
require __DIR__ . '/../../includes/footer.php';
?>