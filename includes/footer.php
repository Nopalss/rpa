<?php
require_once __DIR__ . '/config.php';

// AMANKAN SEMUA AKSES SESSION DI SATU TEMPAT
$menu = $_SESSION['menu'] ?? null;
$username = $_SESSION['username'] ?? null;
$rule = $_SESSION['rule'] ?? null;
?>

<div class="footer bg-white py-4 d-flex flex-lg-column " id="kt_footer">
    <div class=" container-fluid  d-flex flex-column flex-md-row align-items-center justify-content-end">
        <div class="text-dark order-2 order-md-1">
            <span class="text-muted font-weight-bold mr-2">&copy;<?= date('Y') ?> PT. Muramoto Elektronika Indonesia</span>
        </div>
    </div>
</div>
</div>
</div>
</div>
<div id="kt_quick_user" class="offcanvas offcanvas-right p-10">
    <div class="offcanvas-header d-flex align-items-center justify-content-between pb-5">
        <h3 class="font-weight-bold m-0">User Profile</h3>
        <a href="#" class="btn btn-xs btn-icon btn-light btn-hover-primary" id="kt_quick_user_close">
            <i class="ki ki-close icon-xs text-muted"></i>
        </a>
    </div>
    <div class="offcanvas-content pr-5 mr-n5">
        <div class="d-flex align-items-center mt-5">
            <div class="symbol symbol-100 mr-5">
                <div class="symbol-label" style="background-image:url('<?= BASE_URL ?>assets/media/users/blank.png')"></div>
                <i class="symbol-badge bg-success"></i>
            </div>
            <div class="d-flex flex-column">
                <a href="#" class="font-weight-bold font-size-h5 text-dark-75 text-hover-primary">
                    <?= $username ?>
                </a>
                <div class="text-muted mt-1">
                    <?= $rule ?>
                </div>
                <div class="navi mt-2">
                    <a onclick="logoutConfirm()" class="btn btn-sm btn-light-primary font-weight-bolder py-2 px-5">Sign Out</a>
                </div>
            </div>
        </div>
        <div class="separator separator-dashed mt-8 mb-5"></div>

    </div>
</div>
<script>
    // PERBAIKAN: Definisikan HOST_URL hanya sekali
    var HOST_URL = "<?= BASE_URL ?>";

    var KTAppSettings = {
        "breakpoints": {
            "sm": 576,
            "md": 768,
            "lg": 992,
            "xl": 1200,
            "xxl": 1400
        },
        "colors": {
            "theme": {
                "base": {
                    "white": "#ffffff",
                    "primary": "#3699FF",
                    "secondary": "#E5EAEE",
                    "success": "#1BC5BD",
                    "info": "#8950FC",
                    "warning": "#FFA800",
                    "danger": "#F64E60",
                    "light": "#E4E6EF",
                    "dark": "#181C32"
                },
                "light": {
                    "white": "#ffffff",
                    "primary": "#E1F0FF",
                    "secondary": "#EBEDF3",
                    "success": "#C9F7F5",
                    "info": "#EEE5FF",
                    "warning": "#FFF4DE",
                    "danger": "#FFE2E5",
                    "light": "#F3F6F9",
                    "dark": "#D6D6E0"
                },
                "inverse": {
                    "white": "#ffffff",
                    "primary": "#ffffff",
                    "secondary": "#3F4254",
                    "success": "#ffffff",
                    "info": "#ffffff",
                    "warning": "#ffffff",
                    "danger": "#ffffff",
                    "light": "#464E5F",
                    "dark": "#ffffff"
                }
            },
            "gray": {
                "gray-100": "#F3F6F9",
                "gray-200": "#EBEDF3",
                "gray-300": "#E4E6EF",
                "gray-400": "#D1D3E0",
                "gray-500": "#B5B5C3",
                "gray-600": "#7E8299",
                "gray-700": "#5E6278",
                "gray-800": "#3F4254",
                "gray-900": "#181C32"
            }
        },
        "font-family": "Poppins"
    };
</script>

<script src="<?= BASE_URL ?>assets/plugins/global/plugins.bundle.js"></script>
<script src="<?= BASE_URL ?>assets/plugins/custom/prismjs/prismjs.bundle.js"></script>
<script src="<?= BASE_URL ?>assets/js/scripts.bundle.js"></script>
<script src="<?= BASE_URL ?>assets/js/pages/features/miscellaneous/sweetalert2.js"></script>

<?php if ($menu != "dashboard"): ?>

    <?php if ($menu): // Hanya muat jika $menu tidak null 
    ?>
        <script src="<?= BASE_URL ?>assets/js/table/<?= $menu ?>-table.js"></script>
    <?php endif; ?>

<?php endif; ?>
<?php if ($menu == "dashboard"): ?>
    <script src="<?= BASE_URL ?>assets/js/pages/features/charts/apexcharts.js"></script>
    <script src="<?= BASE_URL ?>assets/js/dashboard/script.js"></script>
<?php endif; ?>
<?php if ($menu == "minmax"): ?>
    <script src="<?= BASE_URL ?>assets/js/pages/features/charts/apexcharts.js"></script>
    <script src="<?= BASE_URL ?>assets/js/minmax/script.js"></script>
<?php endif; ?>
<?php if ($menu == "add_data"): ?>
    <script src="<?= BASE_URL ?>assets/js/data-page/script.js"></script>
<?php endif; ?>
<?php if ($menu == "add_line"): ?>
    <script src="<?= BASE_URL ?>assets/js/line/script.js"></script>
<?php endif; ?>
<script>
    // ==================================================================
    // BAGIAN 1: DEFINISI FUNGSI
    // ==================================================================

    function logoutConfirm() {
        Swal.fire({
            title: 'Logout?',
            text: 'Anda yakin ingin keluar dari aplikasi?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?= BASE_URL . "includes/signout.php" ?>";
            }
        });
    }

    function togglePassword(selector, btn) {
        const input = $(selector);
        const icon = $(btn).find("i");

        if (input.attr("type") === "password") {
            input.attr("type", "text");
            icon.removeClass("far fa-eye").addClass("far fa-eye-slash");
        } else {
            input.attr("type", "password");
            icon.removeClass("far fa-eye-slash").addClass("far fa-eye");
        }
    }

    function confirmDeleteTemplate(id, url, title = "Yakin mau hapus?", text = "Data akan dihapus permanen!") {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Lanjut',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Masukkan Password',
                    input: 'password',
                    inputPlaceholder: 'Password Anda',
                    inputAttributes: {
                        maxlength: 50,
                        autocapitalize: 'off',
                        autocorrect: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal',
                    preConfirm: (password) => {
                        if (!password) {
                            Swal.showValidationMessage('Password wajib diisi!');
                            return false;
                        }
                        return password;
                    }
                }).then((res) => {
                    if (res.isConfirmed) {
                        const form = document.createElement("form");
                        form.method = "POST";
                        form.action = `${HOST_URL}${url}`;

                        const inputId = document.createElement("input");
                        inputId.type = "hidden";
                        inputId.name = "id";
                        inputId.value = id;

                        const inputPw = document.createElement("input");
                        inputPw.type = "hidden";
                        inputPw.name = "password";
                        inputPw.value = res.value;

                        form.appendChild(inputId);
                        form.appendChild(inputPw);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        });
    }

    function confirmCancel(id, url, title = "Yakin mau membatalkan?", text = "Data akan dihapus!") {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Batal!',
            cancelButtonColor: '#3085d6',
            cancelButtonText: 'Lanjut'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement("form");
                form.method = "POST";
                form.action = `${HOST_URL}${url}`;

                const inputId = document.createElement("input");
                inputId.type = "hidden";
                inputId.name = "id";
                inputId.value = id;

                form.appendChild(inputId);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function syncAppPath() {
        $("#application_path").val($("#application_name").val());
    }

    // ==================================================================
    // BAGIAN 2: EVENT LISTENERS (HANYA SATU DOCUMENT READY)
    // ==================================================================
    $(document).ready(function() {

        // ---------------------------------
        // FLASH MESSAGE (SWEETALERT)
        // ---------------------------------
        <?php if (isset($_SESSION['alert'])): ?>
            Swal.fire({
                icon: "<?= $_SESSION['alert']['icon'] ?>",
                title: "<?= $_SESSION['alert']['title'] ?>",
                text: "<?= $_SESSION['alert']['text'] ?>",
                confirmButtonText: "<?= $_SESSION['alert']['button'] ?> ",
                heightAuto: false,
                customClass: {
                    confirmButton: "btn font-weight-bold btn-<?= $_SESSION['alert']['style'] ?>",
                    icon: "m-auto"
                }
            });
            <?php unset($_SESSION['alert']); ?>
        <?php endif; ?>

        // ---------------------------------
        // FUNGSI SINKRONISASI PATH APLIKASI
        // ---------------------------------
        const $appName = $("#application_name");
        const $appPath = $("#application_path");

        if ($appName.length && $appPath.length) {
            $appName.on("input change", syncAppPath);
        }

        // ---------------------------------
        // FUNGSI UPLOAD CSV
        // ---------------------------------
        $(document).on('click', '#addCsvBtn', function(e) {
            e.preventDefault();
            const appName = document.getElementById('application_name')?.value.trim() || '';
            const csvPath = document.getElementById('csv_path')?.value.trim() || '';

            if (!appName) {
                alert('Application Name harus diisi dulu.');
                return;
            }

            document.getElementById('application_name_hidden').value = appName;
            document.getElementById('csv_path_hidden').value = csvPath;

            const input = document.getElementById('csvFileInput');
            if (input) input.click();
            else console.error('csvFileInput not found');
        });

        $(document).on('change', '#csvFileInput', function(e) {
            const form = document.getElementById('csvForm');
            if (form) form.submit();
            else console.error('csvForm not found');
        });

        // ---------------------------------
        // FUNGSI PREVIEW HEADER CSV
        // ---------------------------------
        <?php if (!empty($previewRows)): ?>
            const csvData = <?= json_encode($previewRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            $('#headerSelector').on('change', function() {
                const rowIndex = parseInt($(this).val());
                const selectedHeader = csvData[rowIndex];
                let html = '';
                selectedHeader.forEach((col, i) => {
                    html += `
                        <tr>
                            <td>Column ${i + 1}</td>
                            <td>${col}</td>
                            <input type="hidden" name="column_${i + 1}" value="${col}">
                        </tr>
                    `;
                });
                $('#selectedHeaderTable tbody').html(html);
            });
            $('#headerSelector').trigger('change');
        <?php endif; ?>

        // ---------------------------------
        // FUNGSI PROTEKSI NAVIGASI FORM
        // ---------------------------------
        <?php if (!empty($_SESSION['form_add_csv']['application_id'])): ?>
            let formChanged = true;

            document.querySelectorAll('.btn-safe-navigation').forEach(button => {
                button.addEventListener('click', function() {
                    formChanged = false;
                });
            });

            document.querySelectorAll(".menu-link").forEach(link => {
                link.addEventListener("click", function(e) {
                    if (formChanged) {
                        e.preventDefault();
                        Swal.fire({
                            icon: "warning",
                            title: "Form Sedang Diisi",
                            text: "Anda tidak bisa berpindah halaman sebelum menyimpan atau membatalkan form.",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#3085d6"
                        });
                    }
                });
            });

            window.addEventListener("beforeunload", function(e) {
                if (formChanged) {
                    // Ini akan memicu popup "Are you sure?"
                    e.preventDefault();
                    e.returnValue = "";
                }
                // JANGAN kirim beacon di sini
            });

            // 2. Event 'pagehide' untuk membersihkan data jika user BENAR-BENAR pergi
            window.addEventListener("pagehide", function(e) {
                // 'e.persisted' bernilai false jika halaman benar-benar ditutup (bukan disimpan di back/forward cache)
                // Tapi untuk beacon, kita kirim saja jika form berubah.
                if (formChanged) {
                    const formData = new FormData();
                    formData.append("action", "delete_temp_data");
                    formData.append("application_id", <?= json_encode($_SESSION['form_add_csv']['application_id']) ?>);

                    // sendBeacon aman digunakan di sini dan tidak akan memblokir penutupan halaman
                    navigator.sendBeacon("<?= BASE_URL ?>controllers/preference/clear_temp_data.php", formData);
                }
            });
        <?php endif; ?>

    }); // <-- AKHIR DARI $(document).ready()
</script>

</body>

</html>