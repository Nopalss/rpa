$(document).ready(function () {
    function showManualAlert(site) {
        // 1. Cek jika alert lain sedang aktif
        if (window.isAlertShowing) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: 'Harap tunggu, alert lain sedang tampil.',
                showConfirmButton: false,
                timer: 1500
            });
            return;
        }

        // 2. Ambil data dari cache
        const alertData = window.cachedChartData[site];
        if (!alertData || !alertData.out_of_control) {
            console.warn("Tidak ada data alert di cache untuk:", site);
            return;
        }

        // 3. Ambil info text (line, app, file, header)
        const $row = $(`.line[data-site="${site}"]`).closest('.row');
        const lineText = $row.find('.line option:selected').text() || '-';
        const appText = $row.find('.application option:selected').text() || '-';
        const fileText = $row.find('.file option:selected').text() || '-';
        const headerText = $row.find('.headers option:selected').text() || '-';

        // 4. Set lock dan tampilkan Swal
        window.isAlertShowing = true;

        Swal.fire({
            icon: 'warning',
            title: '⚠️ Data di luar batas kendali!',
            html: `
        <div class="text-start">
            <p><b>📍 Site:</b> ${site.toUpperCase()}</p>
            <p><b>🏭 Line:</b> ${lineText}</p>
            <p><b>🧩 Application:</b> ${appText}</p>
            <p><b>📂 File:</b> ${fileText}</p>
            <p><b>🧾 Header:</b> ${headerText}</p>
            <hr>
            <p>Ada <b>${alertData.out_of_control_count}</b> titik melewati batas kontrol.</p>
            <p><b>LCL:</b> ${alertData.batas_bawah.toFixed(2)} | <b>UCL:</b> ${alertData.batas_atas.toFixed(2)}</p>
            <p><b>Nilai ekstrem:</b> ${alertData.min_out?.toFixed(2) || 'N/A'} - ${alertData.max_out?.toFixed(2) || 'N/A'}</p>
        </div>
        `,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#007bff'
        }).then(() => {
            window.isAlertShowing = false;
            // PENTING: Panggil showNextAlert() untuk melanjutkan antrian otomatis (jika ada)
            showNextAlert();
        });
    }
    $(document).on('click', '[id$="AlertIcon"]', function () {
        let site = $(this).attr('id').replace("AlertIcon", "");

        // Logika khusus untuk 'mainAlertIcon'
        if (site === 'main') {
            // 'main' icon mewakili site yang sedang di carousel
            site = window.currentMainSite;
        }

        showManualAlert(site);
    });
    // 1. MAPPING ID CHART dan INSTANCE CHART GLOBAL
    const chartMapping = {
        'main': '#chart_2',
        'site1': '#chart_19',
        'site2': '#chart_20',
        'site3': '#chart_21',
        'site4': '#chart_15',
        'site5': '#chart_16'
    };
    window.apexChartsInstances = {};

    // --- FUNGSI RENDER APEXCHART HISTOGRAM ---
    function renderApexHistogram(chartSelector, data, siteName, instanceKey) {
        if (!data || !Array.isArray(data.series_data)) {
            console.error("Data tidak valid:", data);
            $(chartSelector).html('<div class="text-danger small">Data tidak valid.</div>');
            return;
        }
        // 🟩 Tentukan tinggi chart berdasarkan target element
        const isMainChart = chartSelector === "#chart_2";
        const chartHeight = isMainChart ? 300 : 150;
        const options = {
            chart: {
                type: 'area',
                height: chartHeight,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                },
            },
            series: [{
                name: 'Frekuensi',
                data: data.series_data.map(([x, y]) => ({
                    x,
                    y
                })),
            },

            ],
            stroke: {
                curve: 'smooth',
                width: 3,
                colors: ['#007bff']
            },
            fill: {
                type: 'gradient',

                colors: ['#007bff'],
            },
            dropShadow: {
                enabled: true,
                color: '#009ef7',
                top: 2,
                blur: 4,
                opacity: 0.25
            },
            markers: {
                size: 4,
                colors: ['#007bff'],
                strokeWidth: 2,
                hover: {
                    sizeOffset: 4
                }
            },
            dataLabels: {
                enabled: false
            },
            grid: {
                borderColor: '#e0e0e0',
                strokeDashArray: 4,
                padding: {
                    left: 10,
                    right: 10
                }
            },
            xaxis: {
                type: 'numeric',
                tickAmount: 8,
                min: data.batas_bawah - (data.standar_deviasi * 0.5),
                max: data.batas_atas + (data.standar_deviasi * 0.5),
                labels: {
                    formatter: (val) => val.toFixed(1),
                    style: {
                        fontSize: '12px'
                    }
                },

            },
            yaxis: {
                title: {
                    text: 'Frekuensi',
                    style: {
                        fontWeight: 600
                    }
                },
                labels: {
                    style: {
                        fontSize: '7px', // 🟩 perkecil angka 300, 200, dll
                        colors: '#666' // opsional, bikin sedikit lebih halus warnanya
                    },
                    offsetX: -5
                }
            },
            tooltip: {
                x: {
                    show: true,
                    formatter: (val) => `Nilai: ${val.toFixed(2)}`
                },
                y: {
                    formatter: (val, {
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) => {
                        const freq = w.globals.series[seriesIndex][dataPointIndex];
                        return `Frekuensi: ${freq} Data`;
                    }
                }
            },
            annotations: {
                xaxis: [{
                    x: data.batas_bawah,
                    borderColor: '#dc3545',
                    label: {
                        borderColor: '#dc3545',
                        style: {
                            color: '#fff',
                            background: '#dc3545',
                            fontSize: '10px'
                        },
                        text: 'LCL',
                        offsetY: -10,
                        position: 'top'
                    }
                },
                {
                    x: data.batas_atas,
                    borderColor: '#dc3545',
                    label: {
                        borderColor: '#dc3545',
                        style: {
                            color: '#fff',
                            background: '#dc3545',
                            fontSize: '10px'
                        },
                        text: 'UCL',
                        offsetY: -10,
                        position: 'top'
                    }
                }
                ]
            },
            title: {
                text: `Rata-rata: ${data.rata_rata.toFixed(3)} | SD: ${data.standar_deviasi.toFixed(3)}`,
                align: 'center',
                style: {
                    fontSize: '14px',
                    fontWeight: 600
                }
            }
        };

        // instanceKey menentukan nama penyimpanan instance (default: siteName)
        const key = instanceKey || siteName;

        if (window.apexChartsInstances[key]) {
            try {
                window.apexChartsInstances[key].destroy();
            } catch (e) {
                console.warn("Destroy failed", key, e);
            }
            window.apexChartsInstances[key] = null;
        }

        // 🧹 Tambahkan force cleanup DOM — ini penting banget!
        const el = document.querySelector(chartSelector);
        if (!el) {
            console.error("Chart element tidak ditemukan:", chartSelector);
            return;
        }

        // Hapus semua node anak termasuk wrapper SVG lama
        while (el.firstChild) {
            el.removeChild(el.firstChild);
        }

        // Pastikan tinggi konsisten
        el.style.height = "auto"; // reset supaya gak nambah terus

        // Render chart baru
        const chart = new ApexCharts(el, options);
        chart.render();

        // 🟩 Update judul di atas chart
        const titleId = chartSelector.replace("#chart", "#chart_title");
        const titleEl = document.querySelector(titleId);
        if (titleEl) {
            titleEl.innerHTML = `
        <span class="text-dark fw-bold">${data.line_name || siteName}</span> |
        <span class="text-muted">App: ${data.application_name || '-'}</span> |
        <span class="text-muted">File: ${data.file_name || '-'}</span> |
        <span class="text-muted">Header: ${data.header_name || '-'}</span>
    `;
        }
        // Simpan instance
        window.apexChartsInstances[key] = chart;
    }

    window.cachedChartData = {};
    // 🟡 Sistem Antrian Alert dengan Info Detail Lengkap
    window.alertQueue = [];
    window.isAlertShowing = false;

    function showNextAlert() {
        if (window.isAlertShowing || window.alertQueue.length === 0) return;

        const alertData = window.alertQueue.shift();
        window.isAlertShowing = true;

        Swal.fire({
            icon: 'warning',
            title: '⚠️ Data di luar batas kendali!',
            html: `
            <div class="text-start">
                <p><b>📍 Site:</b> ${alertData.site}</p>
                <p><b>🏭 Line:</b> ${alertData.line}</p>
                <p><b>🧩 Application:</b> ${alertData.app}</p>
                <p><b>📂 File:</b> ${alertData.file}</p>
                <p><b>🧾 Header:</b> ${alertData.header}</p>
                <hr>
                <p>Ada <b>${alertData.count}</b> titik melewati batas kontrol.</p>
                <p><b>LCL:</b> ${alertData.lcl} | <b>UCL:</b> ${alertData.ucl}</p>
                <p><b>Nilai ekstrem:</b> ${alertData.min} - ${alertData.max}</p>
            </div>
        `,
            confirmButtonText: 'Lihat Grafik',
            confirmButtonColor: '#007bff'
        }).then(() => {
            window.isAlertShowing = false;
            showNextAlert(); // lanjutkan alert berikutnya
        });
    }

    // --- FUNGSI LOAD DATA CHART HISTOGRAM (API CALL) ---
    function loadHistogramChart(site, isMainCarousel = false, forceRefresh = false) {
        const chartId = isMainCarousel ? chartMapping['main'] : chartMapping[site];
        if (!chartId) return;

        const instanceKey = isMainCarousel ? 'main' : site;

        // 🟡 Pastikan objek cache global ada
        if (!window.cachedChartData) window.cachedChartData = {};
        if (!window.shownAlerts) window.shownAlerts = {}; // untuk mencegah spam alert

        // Jika sudah ada di cache dan tidak perlu refresh
        if (window.cachedChartData[site] && !forceRefresh) {
            renderApexHistogram(chartId, window.cachedChartData[site], site, instanceKey);
            return;
        }

        // 🔵 Kalau belum ada cache, baru panggil API
        const $row = $(`.line[data-site="${site}"]`).closest('.row');
        const $header = $row.find('.headers');

        const settingsData = {
            file_id: $row.find('.file').val(),
            header_name: $header.val(),
            table_type: $header.data('table-type') || 'type1',
            line_id: $row.find('.line').val()
        };

        if (!settingsData.file_id || !settingsData.header_name) {
            $(chartId).html('<div class="text-muted small">Pilih Header.</div>');
            return;
        }

        $.ajax({
            url: `${HOST_URL}api/chart_data_3sigma.php`,
            type: 'POST',
            data: settingsData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // 🟢 Simpan ke cache
                    window.cachedChartData[site] = response;

                    // 🟠 Cek dan tampilkan alert (hanya sekali per site)
                    const isAlertEnabled = window.alertSettings && window.alertSettings[site];

                    if (isAlertEnabled && response.out_of_control && !window.shownAlerts[site]) {
                        window.shownAlerts[site] = true;

                        const $row = $(`.line[data-site="${site}"]`).closest('.row');

                        const lineText = $row.find('.line option:selected').text() || '-';
                        const appText = $row.find('.application option:selected').text() || '-';
                        const fileText = $row.find('.file option:selected').text() || '-';
                        const headerText = $row.find('.headers option:selected').text() || '-';

                        window.alertQueue.push({
                            site: site.toUpperCase(),
                            line: lineText,
                            app: appText,
                            file: fileText,
                            header: headerText,
                            count: response.out_of_control_count,
                            lcl: response.batas_bawah.toFixed(2),
                            ucl: response.batas_atas.toFixed(2),
                            min: response.min_out?.toFixed(2),
                            max: response.max_out?.toFixed(2)
                        });

                        showNextAlert();
                    }

                    // 🟩 Render chart seperti biasa
                    renderApexHistogram(chartId, response, site, instanceKey);
                    if (isMainCarousel || site === "main") {
                        const alertIcon = document.getElementById("mainAlertIcon");
                        const statusIcon = document.getElementById("mainStatusIcon");

                        // 🧩 Hindari render duplikat
                        if (!alertIcon || !statusIcon) return;

                        // ⚙️ Tampilkan hanya tanda seru kalau bermasalah
                        if (response.out_of_control && response.out_of_control_count > 0) {
                            alertIcon.style.display = "inline-block"; // muncul tanda seru
                            statusIcon.style.display = "none";
                        } else {
                            alertIcon.style.display = "none"; // sembunyiin tanda seru
                            statusIcon.style.display = "inline-block";
                        }
                    }
                    if (!isMainCarousel && site !== "main") {
                        const alertIcon = document.getElementById(`${site}AlertIcon`);
                        const statusIcon = document.getElementById(`${site}StatusIcon`);

                        if (alertIcon && statusIcon) {
                            if (response.out_of_control && response.out_of_control_count > 0) {
                                alertIcon.style.display = "inline-block";
                                statusIcon.style.display = "none";
                            } else {
                                alertIcon.style.display = "none";
                                statusIcon.style.display = "inline-block";
                            }
                        }
                    }
                } else {
                    $(chartId).html('<div class="text-danger small">' + response.message + '</div>');
                }
            },
            error: function () {
                $(chartId).html('<div class="text-danger small">Error API.</div>');
            }
        });
    }
    function updateMainTitle(site) {
        const $row = $(`.line[data-site="${site}"]`).closest('.row');
        const lineText = $row.find('.line option:selected').text() || '-';
        const appText = $row.find('.application option:selected').text() || '-';
        const fileText = $row.find('.file option:selected').text() || '-';
        const headerText = $row.find('.headers option:selected').text() || '-';

        const titleHTML = `
        <div class="fs-6 text-dark">
            <span class="fw-bold">📊 ${site.toUpperCase()}</span><br>
            <small>Line: <span class="text-primary">${lineText}</span> |
            App: <span class="text-success">${appText}</span> |
            File: <span class="text-info">${fileText}</span> |
            Header: <span class="text-warning">${headerText}</span></small>
        </div>
    `;

        $("#mainChartTitle").html(titleHTML);
    }


    // ---------------------------------
    // FUNGSI PENGATURAN DASHBOARD (MODEL SETTING)
    // ---------------------------------

    function saveSiteSettings(site) {
        const $row = $(`.line[data-site="${site}"]`).closest('.row');
        const $header = $row.find('.headers');
        const tableType = $header.data('table-type') || 'type1';

        const settingsData = {
            site_name: site,
            line_id: $row.find('.line').val(),
            application_id: $row.find('.application').val(),
            file_id: $row.find('.file').val(),
            header_name: $header.val(),
            is_active: $row.find('.dashboard-toggle input').is(':checked'),
            table_type: tableType
        };

        $.ajax({
            url: `${HOST_URL}api/save_dashboard_setting.php`,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(settingsData),
            dataType: 'json',
            success: function (response) {
                if (!response.success) {
                    console.error('Failed to save settings:', response.message);
                }
            },
            error: function () {
                console.error('AJAX error saving settings.');
            }
        });
    }

    // FUNGSI 2: EVENT LISTENER UNTUK MEMUAT (LOAD) DROPDOWN (Dipotong)
    $(document).on('change', '.line', function () {
        const site = $(this).data('site');
        const lineId = $(this).val();
        const $application = $(`.application[data-site="${site}"]`);
        const $file = $(`.file[data-site="${site}"]`);
        const $header = $(`.headers[data-site="${site}"]`);

        $application.prop('disabled', true).html('<option value="">Select</option>');
        $file.prop('disabled', true).html('<option value="">Select</option>');
        $header.prop('disabled', true).html('<option value="">Select</option>');
        $header.removeData('table-type');

        if (lineId) {
            $.ajax({
                url: `${HOST_URL}api/get_applications.php`,
                type: 'POST',
                data: {
                    line_id: lineId
                },
                dataType: 'json',
                success: function (response) {
                    $application.prop('disabled', false).html('<option value="">Select</option>');
                    $.each(response, function (i, item) {
                        $application.append(`<option value="${item.id}">${item.name}</option>`);
                    });
                    const savedAppId = $(`.line[data-site="${site}"]`).data('app-id');
                    if (savedAppId) {
                        $application.val(savedAppId);
                        $application.trigger('change');
                    }
                },
                error: function () {
                    $application.html('<option value="">Error loading</option>');
                }
            });
        }
    });

    $(document).on('change', '.application', function () {
        const site = $(this).data('site');
        const appId = $(this).val();
        const $file = $(`.file[data-site="${site}"]`);
        const $header = $(`.headers[data-site="${site}"]`);

        $file.prop('disabled', true).html('<option value="">Loading...</option>');
        $header.prop('disabled', true).html('<option value="">Select</option>');
        $header.removeData('table-type');

        if (appId) {
            $.ajax({
                url: `${HOST_URL}api/get_files.php`,
                type: 'POST',
                data: {
                    app_id: appId
                },
                dataType: 'json',
                success: function (response) {
                    $file.prop('disabled', false).html('<option value="">Select</option>');
                    $.each(response, function (i, item) {
                        $file.append(`<option value="${item.id}">${item.name}</option>`);
                    });
                    const savedFileId = $(`.line[data-site="${site}"]`).data('file-id');
                    if (savedFileId) {
                        $file.val(savedFileId);
                        $file.trigger('change');
                    }
                },
                error: function () {
                    $file.html('<option value="">Error loading</option>');
                }
            });
        }
    });

    $(document).on('change', '.file', function () {
        const site = $(this).data('site');
        const fileId = $(this).val();
        const $header = $(`.headers[data-site="${site}"]`);

        $header.prop('disabled', true).html('<option value="">Loading...</option>');
        $header.removeData('table-type');

        const chartId = chartMapping[site];
        $(chartId).html('<div class="text-muted small">Menunggu Header dipilih...</div>');

        if (fileId) {
            $.ajax({
                url: `${HOST_URL}api/get_headers.php`,
                type: 'POST',
                data: {
                    file_id: fileId
                },
                dataType: 'json',
                success: function (response) {
                    $header.prop('disabled', false).html('<option value="">Select</option>');

                    const tableType = response.type || 'type1';
                    $header.data('table-type', tableType);

                    const headers = response.headers || response;

                    $.each(headers, function (i, item) {
                        $header.append(`<option value="${item.header_name}">${item.header_name}</option>`);
                    });
                    const savedHeaderName = $(`.line[data-site="${site}"]`).data('header-name');
                    if (savedHeaderName) {
                        $header.val(savedHeaderName);
                    }
                    $header.trigger('change');
                },
                error: function () {
                    $header.html('<option value="">Error loading</option>');
                }
            });
        }
    });


    // FUNGSI 3: SIMPAN DAN MUAT CHART SAAT AKSI TERAKHIR
    $(document).on('change', '.headers', function () {
        const site = $(this).data('site');
        saveSiteSettings(site);

        if ($(this).val()) {
            loadHistogramChart(site, false, true);
        }
    });

    $(document).on('change', '.dashboard-toggle input', function () {
        const site = $(this).closest('.dashboard-toggle').data('site');
        const isAlertEnabled = $(this).is(':checked');
        saveSiteSettings(site);

        // Simpan status alert per site secara global
        if (!window.alertSettings) window.alertSettings = {};
        window.alertSettings[site] = isAlertEnabled;

        // 🟢 Chart tetap tampil selalu
        // loadHistogramChart(site, false, true);
    });

    // FUNGSI 4: TRIGGER AWAL SAAT HALAMAN DIMUAT (DISIMPLIFIKASI)
    $(document).ready(function () {
        if (!window.alertSettings) window.alertSettings = {};
        $('.dashboard-toggle input').each(function () {
            const site = $(this).closest('.dashboard-toggle').data('site');
            const isAlertEnabled = $(this).is(':checked');
            window.alertSettings[site] = isAlertEnabled;
        });
        // Hanya memicu event di dropdown .line yang memiliki nilai tersimpan.
        // Ini akan memulai rantai cascading dan memuat chart hanya sekali.
        $('.line').each(function () {
            if ($(this).val()) {
                $(this).trigger('change');
            }
        });
    });

    // ---------------------------------------------
    // CAROUSEL UNTUK CHART MAIN
    // ---------------------------------------------
    $(document).ready(function () {
        // === AUTO CAROUSEL UNTUK CHART MAIN ===
        const sites = ["main", "site1", "site2", "site3", "site4", "site5"];
        let currentIndex = 0;
        let isPaused = false;
        let carouselInterval;
        window.currentMainSite = "main";

        // Tombol Play/Pause
        const btn = document.getElementById("toggleCarousel");

        // Fungsi untuk ganti chart di card main
        function updateMainChart(siteName) {
            loadHistogramChart(siteName, true); // <- tambahkan true di sini
        }

        // Fungsi jalan otomatis tiap 5 detik
        function nextChart() {
            if (isPaused) return;
            window.currentMainSite = sites[currentIndex];
            const site = sites[currentIndex];
            updateMainTitle(site); // 🟢 tampilkan judul baru
            loadHistogramChart(site, true);
            currentIndex = (currentIndex + 1) % sites.length;
        }

        // Inisialisasi awal (chart pertama = main)
        window.currentMainSite = sites[currentIndex];
        updateMainChart(window.currentMainSite);

        // Jalankan auto-rotate
        carouselInterval = setInterval(nextChart, 5000);

        // Tombol toggle pause/play
        btn.addEventListener("click", function () {
            isPaused = !isPaused;
            btn.innerHTML = isPaused ? "▶️ Play" : "⏸️ Pause";

            if (!isPaused) {
                // lanjut lagi dari posisi terakhir
                nextChart();
            }
        });

    });


    const sites = ["site1", "site2", "site3", "site4", "site5"];

    sites.forEach(site => {
        // Ambil interval masing-masing site (default 15 detik kalau tidak ada)
        const interval = (window.userIntervals && window.userIntervals[site])
            ? window.userIntervals[site] * 1000
            : 15000;

        setInterval(() => {
            // window.shownAlerts = {};
            loadHistogramChart(site, false, true);
        }, interval);
    });
});