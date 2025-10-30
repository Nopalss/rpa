<?php

require_once __DIR__ . '/config.php';
?>


<!--begin::Footer-->
<div class="footer bg-white py-4 d-flex flex-lg-column " id="kt_footer">
    <!--begin::Container-->
    <div class=" container-fluid  d-flex flex-column flex-md-row align-items-center justify-content-end">
        <!--begin::Copyright-->
        <div class="text-dark order-2 order-md-1">
            <span class="text-muted font-weight-bold mr-2"><?= date('Y') ?>&copy;</span>
            <a href="" target="_blank" class="text-dark-75 text-hover-primary">RPA</a>
        </div>
        <!--end::Copyright-->

        <!--begin::Nav-->

        <!--end::Nav-->
    </div>
    <!--end::Container-->
</div>
<!--end::Footer-->
</div>
<!--end::Wrapper-->
</div>
<!--end::Page-->
</div>
<!--end::Main-->
<!-- end::Content -->
<!-- begin::User Panel-->
<div id="kt_quick_user" class="offcanvas offcanvas-right p-10">
    <!--begin::Header-->
    <div class="offcanvas-header d-flex align-items-center justify-content-between pb-5">
        <h3 class="font-weight-bold m-0">
            User Profile
        </h3>
        <a href="#" class="btn btn-xs btn-icon btn-light btn-hover-primary" id="kt_quick_user_close">
            <i class="ki ki-close icon-xs text-muted"></i>
        </a>
    </div>
    <!--end::Header-->

    <!--begin::Content-->
    <div class="offcanvas-content pr-5 mr-n5">
        <!--begin::Header-->
        <div class="d-flex align-items-center mt-5">
            <div class="symbol symbol-100 mr-5">
                <div class="symbol-label" style="background-image:url('<?= BASE_URL ?>assets/media/users/blank.png')"></div>
                <i class="symbol-badge bg-success"></i>
            </div>
            <div class="d-flex flex-column">
                <a href="#" class="font-weight-bold font-size-h5 text-dark-75 text-hover-primary">
                    <?= $_SESSION['username'] ?>
                </a>
                <div class="text-muted mt-1">
                    <?= $_SESSION['rule'] ?>
                </div>
                <div class="navi mt-2">
                    <a onclick="logoutConfirm()" class="btn btn-sm btn-light-primary font-weight-bolder py-2 px-5">Sign Out</a>
                </div>
            </div>
        </div>
        <!--end::Header-->

        <!--begin::Separator-->
        <div class="separator separator-dashed mt-8 mb-5"></div>
        <!--end::Separator-->

        <!--begin::Nav-->
        <div class="navi navi-spacer-x-0 p-0">
            <!--begin::Item-->
            <a href="custom/apps/user/profile-1/personal-information.html" class="navi-item">
                <div class="navi-link">
                    <div class="symbol symbol-40 bg-light mr-3">
                        <div class="symbol-label">
                            <span class="svg-icon svg-icon-md svg-icon-success"><!--begin::Svg Icon | path:assets/media/svg/icons/General/Notification2.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <rect x="0" y="0" width="24" height="24" />
                                        <path d="M13.2070325,4 C13.0721672,4.47683179 13,4.97998812 13,5.5 C13,8.53756612 15.4624339,11 18.5,11 C19.0200119,11 19.5231682,10.9278328 20,10.7929675 L20,17 C20,18.6568542 18.6568542,20 17,20 L7,20 C5.34314575,20 4,18.6568542 4,17 L4,7 C4,5.34314575 5.34314575,4 7,4 L13.2070325,4 Z" fill="#000000" />
                                        <circle fill="#000000" opacity="0.3" cx="18.5" cy="5.5" r="2.5" />
                                    </g>
                                </svg><!--end::Svg Icon--></span>
                        </div>
                    </div>
                    <div class="navi-text">
                        <div class="font-weight-bold">
                            My Profile
                        </div>
                        <div class="text-muted">
                            Account settings and more
                            <span class="label label-light-danger label-inline font-weight-bold">update</span>
                        </div>
                    </div>
                </div>
            </a>
            <!--end:Item-->

            <!--begin::Item-->

            <!--end:Item-->
        </div>
        <!--end::Nav-->
    </div>
    <!--end::Content-->
</div>
<!-- end::User Panel-->
<!-- sweetalert -->
<script>
    var HOST_URL = "<?= BASE_URL ?>";
</script>
<?php if (isset($_SESSION['alert'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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
    </script>
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>

<!--begin::Global Config(global config for global JS scripts)-->
<script>
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
<!--end::Global Config-->
<script>
    var HOST_URL = "<?= BASE_URL ?>";
</script>

<!--begin::Global Theme Bundle(used by all pages)-->
<script src="<?= BASE_URL ?>assets/plugins/global/plugins.bundle.js"></script>
<script src="<?= BASE_URL ?>assets/plugins/custom/prismjs/prismjs.bundle.js"></script>
<script src="<?= BASE_URL ?>assets/js/scripts.bundle.js"></script>

<?php if ($_SESSION['menu'] != "dashboard"): ?>
    <!-- <script src="<?= BASE_URL ?>assets/js/pages/crud/forms/widgets/bootstrap-timepicker.js"></script> -->
    <script src="<?= BASE_URL ?>assets/js/pages/crud/forms/widgets/bootstrap-datepicker.js"></script>
    <!-- <script src="<?= BASE_URL ?>assets/js/table/add_model-table.js"></script> -->
    <script src="<?= BASE_URL ?>assets/js/table/<?= $_SESSION['menu'] ?>-table.js"></script>
<?php endif; ?>
<?php if ($_SESSION['menu'] == "dashboard"): ?>
    <script src="<?= BASE_URL ?>assets/js/pages/features/charts/apexcharts.js"></script>
<?php endif; ?>
<!-- <script src="<?= BASE_URL ?>assets/js/pages/crud/ktdatatable/base/local-sort.js"></script> -->


<!-- script -->
<script>
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

    //  delete template
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
                // Munculin modal input password
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
                        // Kirim password ke backend (POST) biar bisa diverifikasi
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


    // fungsi path
    $(document).ready(function() {
        const $appName = $("#application_name");
        const $appPath = $("#application_path");

        if ($appName.length && $appPath.length) {
            // Event realtime saat user mengetik
            $appName.on("input change", function() {
                $appPath.val($(this).val());
            });
        } else {
            console.error("Elemen #application_name atau #application_path tidak ditemukan");
        }
    });

    function syncAppPath() {
        $("#application_path").val($("#application_name").val());
    }

    $(document).ready(function() {
        $("#application_name").on("input change", syncAppPath);
    });


    // fungsi create csv
    document.addEventListener('click', function(e) {
        if (e.target.closest('#addCsvBtn')) {
            e.preventDefault();

            // Ambil nilai dari input utama
            const appName = document.getElementById('application_name')?.value.trim() || '';
            const csvPath = document.getElementById('csv_path')?.value.trim() || '';

            // Validasi minimal
            if (!appName) {
                alert('Application Name harus diisi dulu.');
                return;
            }

            // Isi hidden input di form tersembunyi
            document.getElementById('application_name_hidden').value = appName;
            document.getElementById('csv_path_hidden').value = csvPath;

            // Klik input file
            const input = document.getElementById('csvFileInput');
            if (input) input.click();
            else console.error('csvFileInput not found');
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'csvFileInput') {
            const form = document.getElementById('csvForm');
            if (form) form.submit();
            else console.error('csvForm not found');
        }
    });

    // dashboard line
    $(document).ready(function() {
        // ketika line berubah
        $(document).on('change', '.line', function() {
            const site = $(this).data('site'); // ambil site name (main, site1, dsb)
            const lineId = $(this).val();

            const $application = $(`.application[data-site="${site}"]`);
            const $file = $(`.file[data-site="${site}"]`);
            const $header = $(`.headers[data-site="${site}"]`);

            $application.prop('disabled', true).html('<option value="">Loading...</option>');
            $file.prop('disabled', true).html('<option value="">Select</option>');
            $header.prop('disabled', true).html('<option value="">Select</option>');

            if (lineId) {
                $.ajax({
                    url: '<?= BASE_URL ?>api/get_applications.php',
                    type: 'POST',
                    data: {
                        line_id: lineId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $application.prop('disabled', false).html('<option value="">Select</option>');
                        $.each(response, function(i, item) {
                            $application.append(`<option value="${item.id}">${item.name}</option>`);
                        });
                    },
                    error: function() {
                        $application.html('<option value="">Error loading</option>');
                    }
                });
            } else {
                $application.html('<option value="">Select</option>');
            }
        });

        // ketika application berubah
        $(document).on('change', '.application', function() {
            const site = $(this).data('site');
            const appId = $(this).val();

            const $file = $(`.file[data-site="${site}"]`);
            const $header = $(`.header[data-site="${site}"]`);

            $file.prop('disabled', true).html('<option value="">Loading...</option>');
            $header.prop('disabled', true).html('<option value="">Select</option>');

            if (appId) {
                $.ajax({
                    url: '<?= BASE_URL ?>api/get_files.php',
                    type: 'POST',
                    data: {
                        app_id: appId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $file.prop('disabled', false).html('<option value="">Select</option>');
                        $.each(response, function(i, item) {
                            $file.append(`<option value="${item.id}">${item.name}</option>`);
                        });
                    },
                    error: function() {
                        $file.html('<option value="">Error loading</option>');
                    }
                });
            }
        });

        // ketika file berubah
        $(document).on('change', '.file', function() {
            const site = $(this).data('site');
            const fileId = $(this).val();

            const $header = $(`.headers[data-site="${site}"]`);
            $header.prop('disabled', true).html('<option value="">Loading...</option>');

            if (fileId) {
                $.ajax({
                    url: '<?= BASE_URL ?>api/get_headers.php',
                    type: 'POST',
                    data: {
                        file_id: fileId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $header.prop('disabled', false).html('<option value="">Select</option>');
                        $.each(response, function(i, item) {
                            $header.append(`<option value="${item.header_name}">${item.header_name}</option>`);
                        });
                    },
                    error: function() {
                        $header.html('<option value="">Error loading</option>');
                    }
                });
            }
        });
    });


    // fungsi untuk memilih row header
    <?php if (!empty($previewRows)): ?>
        const csvData = <?= json_encode($previewRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

        // Event ketika dropdown berubah
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

        // Auto-load baris pertama sebagai default
        $('#headerSelector').trigger('change');
    <?php endif; ?>

    // ini untuk fokus mengisi form
    <?php if (!empty($_SESSION['form_add_csv']['application_id'])): ?>
        // 1. Inisialisasi - Cukup set 'true' jika session ada.
        let formChanged = true;

        // 2. LOGIKA BARU: Tandai tombol-tombol "Aman"
        // Tambahkan class 'btn-safe-navigation' pada tombol/link "Show", "Delete", 
        // "Save", dan "Cancel" di HTML Anda.
        document.querySelectorAll('.btn-safe-navigation').forEach(button => {
            button.addEventListener('click', function() {
                // Saat tombol aman diklik, set formChanged menjadi false.
                // Ini akan mencegah 'beforeunload' dan 'menu-link' aktif.
                formChanged = false;
            });
        });

        // Ini akan dicegah jika 'btn-safe-navigation' diklik lebih dulu.
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

        // 4. LOGIKA 'beforeunload' (Disederhanakan)
        // Kita hapus pengecekan 'excludedPages' yang keliru.
        window.addEventListener("beforeunload", function(e) {
            // Ini HANYA akan aktif jika formChanged masih 'true'
            // (artinya: pengguna menutup tab, refresh, atau klik link non-aman)
            if (formChanged) {
                e.preventDefault();
                e.returnValue = "";

                // Kirim request hapus data ke server
                const formData = new FormData();
                formData.append("action", "delete_temp_data");
                formData.append("application_id", <?= json_encode($_SESSION['form_add_csv']['application_id']) ?>);

                // Gunakan sendBeacon agar tetap dikirim walau tab ditutup
                navigator.sendBeacon("<?= BASE_URL ?>controllers/preference/clear_temp_data.php", formData);
            }
        });
    <?php endif; ?>

    // fungsi add line
    $(document).ready(function() {

        // ===================================
        // FUNGSI ADD LINE (Versi jQuery)
        // ===================================
        // Listener ini tidak masalah, karena #addLineBtn ada saat halaman dimuat
        $('#addLineBtn').on('click', function() {
            Swal.fire({
                title: 'Tambahkan Data Line Baru',
                input: 'text',
                inputLabel: 'Nama Line',
                inputPlaceholder: 'Masukkan nama line...',
                showCancelButton: true,
                confirmButtonText: 'Tambahkan',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Nama line tidak boleh kosong!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const lineName = result.value;
                    Swal.fire({
                        title: 'Menyimpan...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`${HOST_URL}controllers/preference/add_line.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                line_name: lineName
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Gagal!', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Oops...', 'Terjadi kesalahan: ' + error.message, 'error');
                        });
                }
            });
        });

        // =======================================
        // FUNGSI EDIT LINE (MENGGUNAKAN EVENT DELEGATION)
        // =======================================
        // Kita pasang listener di '#kt_datatable', BUKAN di tombolnya
        $('#kt_datatable').on('click', '.editLineBtn', function() {

            // 'this' sekarang adalah tombol .editLineBtn yang diklik
            // Kita pakai $(this).data() untuk mengambil data-
            const lineId = $(this).data('id');
            const currentLineName = $(this).data('name');

            Swal.fire({
                title: 'Edit Data Line',
                input: 'text',
                inputLabel: 'Nama Line',
                inputValue: currentLineName, // Tampilkan nama lama
                inputPlaceholder: 'Masukkan nama line baru...',
                showCancelButton: true,
                confirmButtonText: 'Simpan Perubahan',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Nama line tidak boleh kosong!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const newLineName = result.value;

                    if (newLineName === currentLineName) {
                        Swal.fire('Tidak ada perubahan', '', 'info');
                        return;
                    }

                    Swal.fire({
                        title: 'Menyimpan...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`${HOST_URL}controllers/preference/edit_line.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                line_id: lineId,
                                line_name: newLineName
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Gagal!', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Oops...', 'Terjadi kesalahan: ' + error.message, 'error');
                        });
                }
            });
        });

    }); // <-- Akhir dari $(document).ready()
</script>

<!--end::Page Scripts-->
</body>
<!--end::Body-->

</html>