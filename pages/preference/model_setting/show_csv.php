<?php
require_once __DIR__ . '/../../../includes/config.php';
$_SESSION['menu'] = 'model_setting';
$_SESSION['halaman'] = 'model setting';
require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/aside.php';
require __DIR__ . '/../../../includes/navbar.php';
$column = [
    [
        'name' => "Column 111",
        'value' => "value 1"
    ],
    [
        'name' => "Column 2",
        'value' => "value 2"
    ],
    [
        'name' => "Column 3",
        'value' => "value 3"
    ],
    [
        'name' => "Column 4",
        'value' => "value 4"
    ],
    [
        'name' => "Column 5",
        'value' => "value 5"
    ],
];
?>
<div class="content  d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <div class="f-flex justify-content-center align-items-center">
                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="">
                                <h5>Application: CC291JA</h5>
                                <p>Path: /csv/...</p>
                            </div>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Column</th>
                                        <th style="width: 80%;">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($column as $c): ?>
                                        <tr>
                                            <td><?= $c['name']; ?></td>
                                            <td><?= $c['value']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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