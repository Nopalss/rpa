<?php
require_once __DIR__ . '/../../../includes/config.php';
$_SESSION['menu'] = 'user';
require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/aside.php';
require __DIR__ . '/../../../includes/navbar.php';

?>

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!-- Entry -->
    <div class="d-flex flex-column-fluid">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Detail Customers -->
                <div class="col-md-6 mt-5 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Create Line</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" class="form " action="<?= BASE_URL ?>controllers/setting/user/create.php">
                                <div class="form-group">
                                    <label for="name">Line Name</label>
                                    <input id="name" type="text" class="form-control" name="line_name" required>
                                </div>
                                <div class="card-footer text-right">
                                    <a href="<?= BASE_URL ?>pages/setting/user/" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Cancel</a>
                                    <button type="submit" name="submit" class="btn btn-primary font-weight-bold">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require __DIR__ . '/../../../includes/footer.php'; ?>