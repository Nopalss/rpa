<?php

require_once __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/clear_temp_session.php';
$_SESSION['halaman'] = 'dashboard';
$_SESSION['menu'] = 'dashboard';

require __DIR__ . '/../includes/aside.php';
// require __DIR__ . '/../includes/navbar.php';

// 1. Ambil Data Line untuk Dropdown
$stmt = $pdo->query("SELECT line_id AS id, line_name FROM tbl_line ORDER BY line_name ASC");
$lines = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user_id = $_SESSION['user_id'] ?? 0;

// 2. Ambil Setting User dari Database
$user_settings = [];
// Note: $user_intervals tidak lagi krusial karena pakai Global Interval, tapi dibiarkan agar tidak error jika JS lama masih baca.
$user_intervals = [];

if ($user_id > 0) {
    $stmt_settings = $pdo->prepare("SELECT * FROM tbl_user_settings WHERE user_id = :user_id");
    $stmt_settings->execute([':user_id' => $user_id]);
    $results = $stmt_settings->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $user_settings[$row['site_name']] = $row;
        $user_intervals[$row['site_name']] = 15;
    }
}

// 3. FUNGSI TEMPLATE RENDER (WAJIB ADA DI SINI)
function renderSiteSettingItem($i, $site_name, $lines, $site_settings)
{
    $is_active = $site_settings['is_active'] ?? true;
?>
    <div class="row mb-7 site-setting-row" data-site="<?= $site_name ?>" id="row_<?= $site_name ?>">
        <div class="col-xl-12 d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex justify-content-center align-items-center">
                <div class="d-flex align-items-center">
                    <h6 class="h6 site-label-text font-weight-bolder mr-2 "
                        data-site="<?= $site_name ?>">
                        <?= htmlspecialchars(
                            !empty($site_settings['site_label'])
                                ? $site_settings['site_label']
                                : ('Site ' . $i)
                        ) ?>
                    </h6>

                    <i class="fas fa-edit text-primary cursor-pointer site-label-edit ml-1"
                        data-site="<?= $site_name ?>"></i>
                    <i class="fas fa-check text-success cursor-pointer site-label-save  d-none ml-2 mr-3"
                        data-site="<?= $site_name ?>"></i>
                </div>

                <input type="text"
                    class="form-control form-control-sm mt-2 site-label-input d-none"
                    data-site="<?= $site_name ?>"
                    value="<?= htmlspecialchars($site_settings['site_label'] ?? '') ?>"
                    placeholder="Nama Site">
            </div>

            <?php if ($i > 5): // Tombol Hapus hanya untuk Site 6 ke atas 
            ?>
                <button type="button" class="btn btn-xs btn-light-danger btn-remove-site text-right" data-site="<?= $site_name ?>">
                    <i class="flaticon-delete-1"></i> Hapus
                </button>
            <?php endif; ?>
        </div>

        <div class="col-xl-2 mb-3 d-flex justify-content-center align-items-center">
            <label class="mr-2 mb-0 small">Line</label>
            <select class="form-control form-control-sm line" data-site="<?= $site_name ?>"
                data-app-id="<?= $site_settings['application_id'] ?? '' ?>"
                data-file-id="<?= $site_settings['file_id'] ?? '' ?>"
                data-header-name="<?= $site_settings['header_name'] ?? '' ?>">
                <option value="">Select</option>
                <?php foreach ($lines as $l): ?>
                    <option value="<?= $l['id'] ?>" <?= ($l['id'] == ($site_settings['line_id'] ?? null)) ? 'selected' : '' ?>>
                        <?= $l['line_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-xl-3 mb-3 d-flex justify-content-center align-items-center">
            <label class="mr-2 mb-0 small">App</label>
            <select class="form-control form-control-sm application" data-site="<?= $site_name ?>">
                <option value="">Select</option>
            </select>
        </div>

        <div class="col-xl mb-3 d-flex justify-content-center align-items-center">
            <label class="mr-2 mb-0 small">File</label>
            <select class="form-control form-control-sm file" data-site="<?= $site_name ?>">
                <option value="">Select</option>
            </select>
        </div>

        <div class="col-xl-3 mb-3 d-flex justify-content-center align-items-center">
            <label class="mr-2 mb-0 small">Header</label>
            <select class="form-control form-control-sm headers" data-site="<?= $site_name ?>">
                <option value="">Select</option>
            </select>
        </div>

        <!-- <div class="col-xl-2 mb-3 d-flex justify-content-center align-items-center">
            <button class="btn btn-info mr-2 btn-sm">Alert</button>
            <span class="switch switch-outline switch-icon switch-success dashboard-toggle" data-site="<?= $site_name ?>">
                <label>
                    <input type="checkbox" <?= $is_active ? 'checked="checked"' : '' ?> name="select" />
                    <span></span>
                </label>
            </span>
        </div> -->

        <div class="col-lg-12 d-flex justify-content-center flex-wrap align-items-center mt-2">
            <div class="row  mt-2">
                <div class="col">
                    <label class="form-label fw-bold small mb-0">Standard Lower (LCL)</label>
                    <input type="number" step="0.0001" class="form-control form-control-sm limit-input"
                        data-site="<?= $site_name ?>" data-type="lcl" placeholder="ex: 0.60">
                </div>
                <div class="col">
                    <label class="form-label fw-bold small mb-0">Standard Upper (UCL)</label>
                    <input type="number" step="0.0001" class="form-control form-control-sm limit-input"
                        data-site="<?= $site_name ?>" data-type="ucl" placeholder="ex: 2.40">
                </div>
                <div class="col">
                    <label class="form-label fw-bold small mb-0">Lower Boundary</label>
                    <input type="number" step="0.0001" class="form-control form-control-sm limit-input"
                        data-site="<?= $site_name ?>" data-type="lower" placeholder="ex: 0">
                </div>
                <div class="col">
                    <label class="form-label fw-bold small mb-0">Interval Width</label>
                    <input type="number" step="0.0001" class="form-control form-control-sm limit-input"
                        data-site="<?= $site_name ?>" data-type="interval" placeholder="ex: 1">
                </div>
                <div class="col">
                    <label class="form-label fw-bold small mb-0">Standard CP</label>
                    <input type="number" step="0.0001" class="form-control form-control-sm limit-input"
                        data-site="<?= $site_name ?>" data-type="cp_limit" placeholder="ex: 0.85">
                </div>
                <div class="col">
                    <label class="form-label fw-bold small mb-0">Standard CPK</label>
                    <input type="number" step="0.0001" class="form-control form-control-sm limit-input"
                        data-site="<?= $site_name ?>" data-type="cpk_limit" placeholder="ex: 0.85">
                </div>
            </div>
        </div>
    </div>
    <div class="separator separator-dashed my-5"></div>
<?php } ?>

<div class="d-flex flex-column flex-row-fluid wrapper pt-6" id="kt_wrapper">
    <div class="content d-flex flex-column flex-column-fluid pt-0" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container">
                <div class="row">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <div class="col-xl-6 mb-2">
                            <div class="card shadow">
                                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                    <h5 class="card-title font-weight-bolder mb-0">
                                        <span id="mainHeaderTitle_<?php echo $i ?>">Main <?php echo $i + 1 ?></span>
                                    </h5>
                                    <div>
                                        <button
                                            class="btn btn-sm btn-info"
                                            data-slot="<?php echo $i ?>"
                                            id="btnMainAlert_<?php echo $i ?>">
                                            Alert
                                        </button>
                                        <button
                                            class="btn btn-sm btn-light pause-slot-btn"
                                            data-slot="<?= $i ?>"
                                            title="Pause Slot">
                                            ⏸
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body pt-2 pb-3">
                                    <table class="table table-bordered table-sm text-center" style="font-size:10px">
                                        <tr>
                                            <th></th>
                                            <th>Standard</th>
                                            <th>Actual</th>
                                        </tr>
                                        <tr>
                                            <th>CP</th>
                                            <td id="mainCpStandard_<?php echo $i ?>">-</td>
                                            <td id="mainCpActual_<?php echo $i ?>">-</td>
                                        </tr>
                                        <tr>
                                            <th>CPK</th>
                                            <td id="mainCpkStandard_<?php echo $i ?>">-</td>
                                            <td id="mainCpkActual_<?php echo $i ?>">-</td>
                                        </tr>
                                    </table>

                                    <div id="mainChartViewer_<?php echo $i ?>" style="height:120px"></div>
                                    <div id="mainChartTitle_<?php echo $i ?>" class="text-center small mt-2"></div>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="mt-2 text-center">

                    <button id="pauseAllCarouselBtn"
                        class="btn btn-warning btn-sm text-center">
                        ⏸ Pause All
                    </button>
                </div>


                <div class="col-xl-12 mt-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="font-weight-bolder mb-0">Settings</h2>
                                <div class="d-flex align-items-center">
                                    <label class="mr-2 mb-0 text-muted font-weight-bold small">Refresh:</label>
                                    <select id="globalIntervalSelect" class="form-control form-control-sm mr-3" style="width: 110px;">
                                        <option value="10000">10 Detik</option>
                                        <option value="15000">15 Detik</option>
                                        <option value="30000" selected>30 Detik</option>
                                        <option value="60000">1 Menit</option>
                                        <option value="600000">10 Menit</option>
                                        <option value="900000">15 Menit</option>
                                        <option value="1800000">30 Menit</option>
                                        <option value="3600000">1 Jam</option>
                                        <option value="7200000">2 Jam</option>
                                        <option value="10800000">3 Jam</option>
                                    </select>
                                    <button type="button" id="btnAddSite" class="btn btn-sm btn-primary">
                                        <i class="flaticon2-plus"></i> Tambah Site
                                    </button>
                                </div>
                            </div>

                            <div id="settingsContainer">
                                <?php
                                // Logic Dynamic Loop: Cari index site terbesar (misal site7)
                                $existing_sites = array_keys($user_settings);
                                $max_index = 5; // Minimal 5 karena Site 1-5 statis
                                foreach ($existing_sites as $s_key) {
                                    $num = (int)str_replace('site', '', $s_key);
                                    if ($num > $max_index) $max_index = $num;
                                }

                                // Loop render semua site
                                for ($i = 1; $i <= $max_index; $i++):
                                    $site_name = 'site' . $i;

                                    // Jika site dinamis (>5) sudah dihapus dari DB, jangan dirender
                                    if ($i > 5 && !isset($user_settings[$site_name])) {
                                        continue;
                                    }

                                    $site_settings = $user_settings[$site_name] ?? [];
                                ?>
                                    <?php renderSiteSettingItem($i, $site_name, $lines, $site_settings); ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // Config DB ke JS untuk Hydration
    window.dbConfig = <?= json_encode($user_settings ?? [], JSON_FORCE_OBJECT); ?>;
    window.userIntervals = <?= json_encode($user_intervals); ?>;
</script>

<?php
require __DIR__ . '/../includes/footer.php';
?>