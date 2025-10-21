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
                            <h5>Create User</h5>
                        </div>
                        <div class="card-body">

                            <form method="post" class="form " action="<?= BASE_URL ?>controllers/setting/user/create.php">
                                <div class="form-group">
                                    <label for="name">Username</label>
                                    <input id="name" type="text" class="form-control" name="username" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <div class="input-group">
                                        <input id="password" type="password" class="form-control" minlength="5" name="password" required>
                                        <a onclick="togglePassword('#password', this)" class="btn btn-light-primary"><i class="far fa-eye"></i></a>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <select class="form-control selectpicker" id="role" required name="role" data-size=" 7">
                                        <option value="">Select</option>
                                        <option value="admin">Admin</option>
                                        <option value="user">user</option>
                                    </select>
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