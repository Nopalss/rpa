"use strict";
// Class definition
var KTDatatableLocalSortDemo = function () {

    // Pindahkan variabel ini ke scope atas
    var datatable;
    // Tambahkan "flag" untuk mengecek apakah tabel sudah diinisialisasi
    var isDatatableInitialized = false;

    // Private functions

    // Fungsi 'demo' sekarang menerima parameter filter awal
    var demo = function (initialParams) {

        datatable = $('#kt_datatable').KTDatatable({
            // datasource definition
            data: {
                type: 'remote',
                source: {
                    read: {
                        // DIPERBAIKI: Typo pada URL
                        url: `${HOST_URL}api/get_data.php`,
                        method: 'POST',
                        params: {
                            // Gunakan parameter dari klik pertama
                            query: initialParams
                        }
                    },
                },
                pageSize: 10,
                serverPaging: false,
                serverFiltering: true,
                serverSorting: false,
            },

            // layout definition
            layout: {
                scroll: false,
                footer: false,
            },

            // column sorting
            sortable: true,
            pagination: true,

            // columns definition
            // DIPERBAIKI: Menambahkan kolom create_at dan create_by (sesuai kueri PHP)
            columns: [{
                field: 'filename',
                title: 'File Name',

            }, {
                field: 'Actions',
                title: 'Actions',
                sortable: false,
                textAlign: 'center',
                width: 125,
                template: function (row) {
                    return `\
                        <a href="${HOST_URL}pages/data/data_csv.php?file_id=${row.file_id}&application_id=${row.application_id}&line_id=${row.line_id}&date=${row.date}&header_id=${row.header_id}" class="btn btn-sm btn-success btn-text-primary btn-icon mr-2" title="CSV">\
                            <span class="svg-icon svg-icon-md">\
                                <i class="fas fa-file-csv"></i>\
                            </span>\
                        </a>\
                    `;
                },
            }],
        });

        // Tandai bahwa tabel sudah dibuat
        isDatatableInitialized = true;
    };

    // Fungsi untuk menangani submit filter
    var handleFilterSubmit = function () {
        $('#kt_filter_submit').on('click', function (e) {
            e.preventDefault(); // Mencegah form submit biasa

            // Ambil nilai dari form filter
            var line_id = $('#filter_line_id').val();
            var application_id = $('#filter_application_id').val();
            var date = $('#filter_date').val();

            // DIPERBAIKI: Buat objek 'currentParams' di sini
            var currentParams = {
                line_id: line_id,
                application_id: application_id,
                date: date,
            };

            // Ini sudah benar, untuk mengisi info di tabel HTML
            var line_text = $('#filter_line_id option:selected').text();
            var application_text = $('#filter_application_id option:selected').text();
            $('#line').text(line_text);
            $('#application').text(application_text);
            $('#date').text(date);


            // LOGIKA BARU:
            if (!isDatatableInitialized) {
                // --- JIKA INI KLIK PERTAMA KALI ---

                // 1. Inisialisasi datatable DENGAN parameter filter
                // DIPERBAIKI: Menggunakan 'currentParams' yang sudah dibuat
                demo(currentParams);

                // 2. Tampilkan card tabel (dengan efek slide)
                $('#kt_datatable_card').slideDown();

            } else {
                // --- JIKA INI KLIK KEDUA, KETIGA, dst. ---

                // 1. Cukup update parameter query
                // DIPERBAIKI: Pindahkan setDataSourceParam ke dalam 'else'
                datatable.setDataSourceParam('query', currentParams);

                // 2. Muat ulang data
                datatable.load();
            }
        });
    };

    return {
        // public functions
        init: function () {
            // Kita hanya mendaftarkan event listener untuk tombol submit
            handleFilterSubmit();
        },
    };
}();

jQuery(document).ready(function () {
    KTDatatableLocalSortDemo.init();
});