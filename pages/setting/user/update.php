<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../helper/handlePdoError.php';
require_once __DIR__ . '/../../../helper/setAlert.php';
$_SESSION['menu'] = 'user';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id) {
    redirect("pages/setting/user/");
}

try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_user WHERE user_id = :user_id LIMIT 1");
    $stmt->execute(([':user_id' => $id]));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        setAlert('error', "Oops!", 'User Tidak Ditemukan', 'danger', 'Kembali');
        redirect("pages/setting/user/");
    }
} catch (PDOException $e) {
    handlePdoError($e, "pages/setting/user");
}


require __DIR__ . '/../../../includes/header.php';
require __DIR__ . '/../../../includes/aside.php';
require __DIR__ . '/../../../includes/navbar.php';
$role = ['admin', 'user']

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
                            <h5>Update User</h5>
                        </div>
                        <div class="card-body">

                            <form method="post" class="form " action="<?= BASE_URL ?>controllers/setting/user/update.php">
                                <div class="form-group">
                                    <label for="name">Username</label>
                                    <input id="name" type="text" class="form-control" name="username" value="<?= $user['username'] ?>" required>
                                    <input id="id" type="hidden" class="form-control" name="user_id" value="<?= $user['user_id'] ?>" required>
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
                                        <?php foreach ($role as $r): ?>
                                            <?php $selected = $user['rule'] == $r ? "selected" : '' ?>
                                            <option value="<?= $r ?>" <?= $selected ?> class="text-cappitilize"><?= $r ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="card-footer text-right">
                                    <a href="<?= BASE_URL ?>pages/setting/user/" class="btn btn-light-danger font-weight-bold">Cancel</a>
                                    <button type="submit" name="submit" class="btn btn-primary font-weight-bold">Update</button>
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