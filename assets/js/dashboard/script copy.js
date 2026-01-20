$(document).ready(function () {
    // -------------------------
    // 1. Config & Globals
    // -------------------------

    // Fungsi untuk mendeteksi site apa saja yang ada di halaman saat ini (Site 1...Site N)
    function getAllSites() {
        const set = new Set();
        $('.site-setting-row').each(function () {
            const s = $(this).attr('data-site');
            if (s) set.add(s);
        });
        return set.size
            ? Array.from(set)
            : ["site1", "site2", "site3", "site4", "site5"];
    }


    let SITES = getAllSites();

    const chartMapping = {
        'viewer': '#mainChartViewer',
        'site1': '#chart_19',
        'site2': '#chart_20',
        'site3': '#chart_21',
        'site4': '#chart_15',
        'site5': '#chart_16'
    };

    if (typeof HOST_URL === 'undefined') { console.warn('HOST_URL is not defined.'); }

    window.apexChartsInstances = {};
    window.cachedChartData = {};
    window.alertQueue = [];
    window.isAlertShowing = false;
    window.alertSettings = {};
    window.shownAlerts = {};
    window.loadingCharts = {};
    window.currentMainSite = window.currentMainSite || SITES[0];
    window.dbConfig = window.dbConfig || {};
    window.renderingChart = window.renderingChart || {}; // PATCH: guard render per chart key

    let globalPollingId = null;
    let carouselIntervalId = null;
    const perSiteIntervalIds = {}; // Legacy cleanup

    // -------------------------
    // 2. Utilities
    // -------------------------
    function resolveSite(site) {
        return (site === 'main' || site === 'viewer') ? window.currentMainSite : site;
    }

    function updateSiteLabel(site) {
        const cfg = window.dbConfig?.[site];
        const label = cfg?.site_label || site.toUpperCase();
        const el = document.getElementById(`${site}Label`);
        if (el) el.textContent = label;
    }

    function ensureRemoveButton($row, siteName) {
        // Site 1–5 tidak boleh ada tombol hapus
        const siteNum = parseInt(siteName.replace('site', ''));
        if (siteNum <= 5) return;

        // Cegah duplikasi
        if ($row.find('.btn-remove-site').length) return;

        const $headerCol = $row.find('.col-xl-12').first();

        const $btn = $(`
        <button type="button"
            class="btn btn-xs btn-light-danger btn-remove-site text-right"
            data-site="${siteName}">
            <i class="flaticon-delete-1"></i> Hapus
        </button>
    `);

        $headerCol.append($btn);
    }

    function safeRowForSite(site) {
        const actual = resolveSite(site);
        const $row = $(`.site-setting-row[data-site="${actual}"]`);
        return { actualSite: actual, $row: $row };
    }

    // [BARU] Fungsi Menjalankan Timer Global
    function startFocusedPolling(baseIntervalMs) {
        if (globalPollingId) clearTimeout(globalPollingId);

        function pollOnce() {
            const site = window.currentMainSite;
            if (site) {
                loadHistogramChart(site, false, true);
            }

            const jitter =
                baseIntervalMs +
                Math.floor(Math.random() * 6000) - 3000;

            globalPollingId = setTimeout(pollOnce, Math.max(8000, jitter));
        }

        pollOnce();
    }

    function initSite(siteName) {
        const $row = $(`.site-setting-row[data-site="${siteName}"]`);
        if (!$row.length) return;

        // Aktifkan dropdown line
        $row.find('.line').prop('disabled', false);

        // Dropdown lain reset & disable (menunggu cascade)
        $row.find('.application, .file, .headers')
            .prop('disabled', true)
            .html('<option value="">Select</option>');

        // Reset limit input
        $row.find('.limit-input').val('');

        // Clear cache chart (penting)
        delete window.cachedChartData[siteName];
    }

    // -------------------------
    // 3. Alert UI
    // -------------------------
    function showManualAlert(site) {
        const actual = resolveSite(site);
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

        const data = window.cachedChartData[actual];
        if (!data) {
            Swal.fire('ℹ️', 'Belum ada data CP/CPK untuk site ini.', 'info');
            return;
        }
        // const hasOOC = Number(data.out_of_control_count || 0) > 0;

        const { $row } = safeRowForSite(actual);

        const info = {
            type: "cp_result",
            site: window.dbConfig?.[actual]?.site_label || actual.toUpperCase(),
            line: $row.find('.line option:selected').text() || '-',
            app: $row.find('.application option:selected').text() || '-',
            file: $row.find('.file option:selected').text() || '-',
            header: $row.find('.headers option:selected').text() || '-',
            cp: data.cp?.toFixed(3) ?? '-',
            cpk: data.cpk?.toFixed(3) ?? '-',
            cp_status: data.cp_status ?? '-',
            cpk_status: data.cpk_status ?? '-',
            cp_limit: data.std_limit_cp ?? '-',
            cpk_limit: data.std_limit_cpk ?? '-',
            ng_estimation: data?.estimated_defect_rate != null
                ? parseFloat(data.estimated_defect_rate).toFixed(5)
                : '-',
            ng_actual: data?.out_of_control_percent !== undefined && data?.out_of_control_percent !== null
                ? Number(data.out_of_control_percent / 100).toPrecision(8) // kalau kamu simpan dalam persen, ubah ke proporsi
                : '-',

            // has_ooc: hasOOC,
            // ooc_count: data.out_of_control_count ?? 0,
            // ooc_min: data.out_of_control_min ?? '-',
            // ooc_max: data.out_of_control_max ?? '-',
            // lsl: data.lsl ?? '-',
            // usl: data.usl ?? '-',

        };
        const isOk =
            info.cp_status === "OK" &&
            info.cpk_status === "OK";
        // !hasOOC;

        window.isAlertShowing = true;

        Swal.fire({
            icon: isOk ? "success" : "error",
            title: '📊 Hasil Analisis CP / CPK',
            html: `
            <div class="text-start">
                <p><b>📍 Site:</b> ${info.site}</p>
                <p><b>🏭 Line:</b> ${info.line}</p>
                <p><b>🧩 App:</b> ${info.app}</p>
                <p><b>📂 File:</b> ${info.file}</p>
                <p><b>🧾 Header:</b> ${info.header}</p>
                <hr>
                <p><b>CP:</b> ${info.cp} (${info.cp_status})</p>
                <p><b>CP Limit:</b> ${info.cp_limit}</p>
                <p><b>CPK:</b> ${info.cpk} (${info.cpk_status})</p>
                <p><b>CPK Limit:</b> ${info.cpk_limit}</p>
                <hr>
                <p><b>NG Estimation:</b> ${info.ng_estimation}%</p>
                <p><b>NG Actual:</b> ${info.ng_actual}%</p>
                <hr>
                <p><b>Status:</b> 
                    <span class="${isOk ? 'text-success' : 'text-danger'}">
                        ${isOk ? 'OKE ✅' : 'Not Good(NG) ❌'}
                    </span>
                </p>
            </div>
        `,
            confirmButtonText: 'OK',
            confirmButtonColor: isOk ? '#28a745' : '#d33'
        }).then(() => {
            window.isAlertShowing = false;
            showNextAlert();
        });
    }



    function showNextAlert() {
        if (window.isAlertShowing || !window.alertQueue || window.alertQueue.length === 0) return;
        const info = window.alertQueue.shift();
        window.isAlertShowing = true;
        fireSwal(info);
    }

    function fireSwal(info) {
        if (info.type === "cp_result") {
            Swal.fire({
                icon: info.final_status === "OK" ? "success" : "error",

                title: '📊 Hasil Analisis CP / CPK',
                html: `
                <div class="text-start">
                    <p><b>📍 Site:</b> ${info.site}</p>
                    <p><b>🏭 Line:</b> ${info.line}</p>
                    <p><b>🧩 App:</b> ${info.app}</p>
                    <p><b>📂 File:</b> ${info.file}</p>
                    <p><b>🧾 Header:</b> ${info.header}</p>
                    <hr>
                    <p><b>CP:</b> ${info.cp ?? '-'} (${info.cp_status ?? '-'})</p>
                    <p><b>CPK:</b> ${info.cpk ?? '-'} (${info.cpk_status ?? '-'})</p>
                </div>
            `,
                confirmButtonText: 'OK',
                confirmButtonColor: info.final_status === "OK" ? '#28a745' : '#d33',
            }).then(() => {
                window.isAlertShowing = false;
                showNextAlert();
            });
            return;
        }

        // Default alert untuk data out of control
        Swal.fire({
            icon: 'warning',
            title: '⚠️ Data di luar batas kendali!',
            html: `
            <div class="text-start">
                <p><b>📍 Site:</b> ${info.site}</p>
                <p><b>🏭 Line:</b> ${info.line}</p>
                <p><b>🧩 App:</b> ${info.app}</p>
                <p><b>📂 File:</b> ${info.file}</p>
                <p><b>🧾 Header:</b> ${info.header}</p>
                <hr>
                <p>Ada <b>${info.count}</b> titik out of control.</p>
                <p><b>LCL:</b> ${info.lcl} | <b>UCL:</b> ${info.ucl}</p>
                <p><b>Range:</b> ${info.min} - ${info.max}</p>
            </div>
        `,
            confirmButtonText: 'Lihat Grafik',
            confirmButtonColor: '#007bff'
        }).then(() => {
            window.isAlertShowing = false;
            showNextAlert();
        });
    }


    // -------------------------
    // 4. Render Chart (Style: Bar Histogram + Line Curve)
    // -------------------------
    function renderApexHistogram(chartSelector, data, siteName, instanceKey) {
        if (!chartSelector || !data || !Array.isArray(data.series_data)) {
            if (chartSelector) $(chartSelector).html('<div class="text-danger small">Data tidak valid.</div>');
            return;
        }

        const isViewer = (chartSelector === chartMapping['viewer']);
        const chartHeight = isViewer ? 350 : 150;

        //--------------------------------------------------------------
        // 1. Build Excel-like boundary axis
        //--------------------------------------------------------------
        const boundaries = [
            parseFloat(data.debug_lower_boundary),
            ...(Array.isArray(data.debug_upper_boundaries) ? data.debug_upper_boundaries.map(v => parseFloat(v)) : [])
        ];
        const formattedBoundaries = boundaries.map(v => (Number.isFinite(v) ? v.toFixed(2) : v));

        //--------------------------------------------------------------
        // 2. Bars + Curve (uses midpoint)
        //--------------------------------------------------------------
        const bars = (Array.isArray(data.series_data) ? data.series_data : []).map(item => ({
            x: parseFloat(item[0]),
            y: item[1]
        }));

        const curve = (Array.isArray(data.normal_curve) ? data.normal_curve : []).map(item => ({
            x: parseFloat(item[0]),
            y: item[1]
        }));

        const yMin = parseFloat(data.y_axis_min) || 0;
        //--------------------------------------------------------------
        // 3. ApexCharts Options
        //--------------------------------------------------------------
        // --- Helper: geser LSL/USL ke midpoint terdekat agar sejajar dengan batang ---
        function alignLimitToMidpoint(value, data) {
            if (!data || !Array.isArray(data.series_data) || data.series_data.length === 0) {
                return value;
            }
            const mids = data.series_data.map(d => d[0]).sort((a, b) => a - b);
            const step = mids.length > 1 ? (mids[1] - mids[0]) : 1;

            // Jika nilai di bawah range
            if (value <= mids[0]) return mids[0] - step / 2;
            // Jika nilai di atas range
            if (value >= mids[mids.length - 1]) return mids[mids.length - 1] + step / 2;

            // Jika di tengah, geser ke midpoint terdekat
            let nearest = mids[0];
            let diff = Math.abs(value - nearest);
            for (const m of mids) {
                const d = Math.abs(value - m);
                if (d < diff) {
                    diff = d;
                    nearest = m;
                }
            }
            return nearest;
        }

        const midpoints = data.series_data.map(d => Number(d[0]));
        const step = midpoints.length > 1 ? (midpoints[1] - midpoints[0]) : 1;

        const options = {
            chart: {
                height: chartHeight,
                type: 'line',
                toolbar: { show: isViewer },
                animations: { enabled: false },
                zoom: { enabled: false },

            },



            series: [
                { name: 'Observed values', type: 'column', data: bars },
                { name: 'Predicted Value', type: 'line', data: curve }
            ],

            colors: ['#1E88E5', '#FF0000'],

            plotOptions: {
                bar: {
                    borderRadius: 0,
                    columnWidth: '100%',
                    barHeight: '100%',
                    dataLabels: { enabled: false }
                }
            },

            stroke: {
                width: [0, 3],
                curve: 'smooth'
            },

            //----------------------------------------------------------
            // 4. X-axis is CATEGORY OF BOUNDARIES (Excel style)
            //----------------------------------------------------------
            //----------------------------------------------------------
            // 4. X-axis pakai label midpoint (bukan auto numeric)
            //----------------------------------------------------------
            xaxis: {
                type: 'numeric',
                tickAmount: midpoints.length - 1,
                min: midpoints[0],
                max: midpoints[midpoints.length - 1],

                labels: {
                    show: true,
                    rotate: -45,
                    rotateAlways: true,
                    formatter: function (val) {
                        // Snap val ke grid midpoint
                        const idx = Math.round((val - midpoints[0]) / step);

                        if (idx >= 0 && idx < midpoints.length) {
                            return midpoints[idx].toFixed(2);
                        }
                        return '';
                    },
                    style: { fontSize: '7.9px' }
                }
            },
            //----------------------------------------------------------
            // 5. Tooltip uses midpoint instead of category label
            //----------------------------------------------------------
            tooltip: {
                shared: true,
                x: {
                    formatter: (value, opts) => {
                        const dp = opts.dataPointIndex;
                        if (dp >= 0 && dp < bars.length && Number.isFinite(bars[dp].x)) {
                            return "Midpoint: " + bars[dp].x.toFixed(3);
                        }
                        return value;
                    }
                },
                y: {
                    formatter: function (val, opts) {
                        const seriesName = opts.seriesIndex === 1 ? 'Predicted' : 'Observed';
                        if (seriesName === 'Predicted') {
                            return val.toFixed(3); // tampilkan 3 digit desimal untuk Predicted
                        }
                        return Math.round(val);    // Observed tetap bulat
                    }
                }
            },

            yaxis: {
                min: Number.isFinite(data.y_axis_min) ? data.y_axis_min : undefined,
                max: Number.isFinite(data.y_axis_max) ? data.y_axis_max : undefined,
                labels: {
                    formatter: function (val) {
                        return Math.round(val);   // hilangkan koma/desimal
                    },
                    style: { fontSize: '8px' }
                },
                forceNiceScale: true,     // biar jarak antar ticks rapi
                stepSize: 100,            // setiap 100 satu label
            },
            annotations: {
                xaxis: [
                    (Number.isFinite(data.lsl) ? {
                        x: alignLimitToMidpoint(data.lsl, data),
                        strokeDashArray: 4,
                        borderColor: '#ff0000',
                        label: {
                            text: 'LSL',
                            style: { background: '#ff0000', color: '#fff', fontSize: '9px' },
                            orientation: 'vertical',  // biar rapi
                            offsetY: -10
                        }
                    } : null),
                    (Number.isFinite(data.usl) ? {
                        x: alignLimitToMidpoint(data.usl, data),
                        strokeDashArray: 4,
                        borderColor: '#ff0000',
                        label: {
                            text: 'USL',
                            style: { background: '#ff0000', color: '#fff', fontSize: '9px' },
                            orientation: 'vertical',
                            offsetY: -10
                        }
                    } : null)
                ].filter(Boolean)
            },



            legend: { show: isViewer }
        };

        //--------------------------------------------------------------
        // RENDER (with guards)
        //--------------------------------------------------------------
        const key = instanceKey || siteName;

        // Pastikan elemen masih ada di DOM
        const el = document.querySelector(chartSelector);
        if (!el || !document.body.contains(el)) {
            console.warn("Chart element not found or removed:", chartSelector);
            return;
        }

        // Hindari double-render race (PATCH)
        if (window.renderingChart[key]) {
            // console.log('Skip render (still rendering):', key);
            return;
        }
        window.renderingChart[key] = true;

        // Hapus chart lama dengan aman
        if (window.apexChartsInstances[key]) {
            try {
                const oldEl = document.querySelector(chartSelector);
                if (oldEl && document.body.contains(oldEl)) {
                    window.apexChartsInstances[key].destroy();
                } else {
                    console.warn(`Skip destroy: element for ${key} not found in DOM`);
                }
            } catch (err) {
                console.warn("Destroy chart failed:", key, err);
            }
        }

        // Bersihkan isi chart
        el.innerHTML = "";

        // Delay kecil agar DOM stabil (hindari error style)
        setTimeout(() => {
            if (!document.body.contains(el)) {
                window.renderingChart[key] = false;
                return;
            }
            try {
                const chart = new ApexCharts(el, options);
                chart.render();
                // Tambah label info di bawah mini chart
                if (!isViewer) {
                    const { $row } = safeRowForSite(siteName);
                    const infoHtml = `
        <div class="text-center mt-1 small">
            <span class="text-primary">Line: ${$row.find('.line option:selected').text() || '-'}</span> |
            <span class="text-success">App: ${$row.find('.application option:selected').text() || '-'}</span> |
            <span class="text-info">File: ${$row.find('.file option:selected').text() || '-'}</span> |
            <span class="text-warning">Header: ${$row.find('.headers option:selected').text() || '-'}</span>
        </div>
    `;
                    const parent = el.closest('.card-body') || el.parentElement;
                    if (parent && !parent.querySelector('.chart-info')) {
                        const div = document.createElement('div');
                        div.className = 'chart-info';
                        div.innerHTML = infoHtml;
                        parent.appendChild(div);
                    } else if (parent && parent.querySelector('.chart-info')) {
                        parent.querySelector('.chart-info').innerHTML = infoHtml;
                    }
                }

                window.apexChartsInstances[key] = chart;
            } catch (err) {
                console.error("Render chart failed:", err);
            } finally {
                window.renderingChart[key] = false;
            }
        }, 50);
    }

    // -------------------------
    // PATCH: Viewer Only Render From Cache (copy)
    // -------------------------
    function deepCopy(obj) {
        if (typeof structuredClone === 'function') return structuredClone(obj);
        try { return JSON.parse(JSON.stringify(obj)); } catch { return null; }
    }

    function renderViewerFromCache(site) {
        const raw = window.cachedChartData[site];
        const cached = raw ? deepCopy(raw) : null; // PATCH: hindari race dengan polling
        if (cached) {
            renderApexHistogram('#mainChartViewer', cached, site, 'viewer');
        } else {
            $('#mainChartViewer').html(`
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="spinner-border text-primary"></div>
                </div>
            `);
        }
    }

    // ==========================
    // PATCH: SAFE CHART SELECTOR
    // ==========================
    function getChartSelectorForSite(site, isMainCarousel) {
        if (isMainCarousel) return chartMapping['viewer'];
        // site6+ tidak punya mini chart → lewati saja
        return chartMapping[site] || null;
    }
    // function getChartSelectorForSite(site, isMainCarousel) {
    //     if (isMainCarousel) return chartMapping['viewer'];
    //     if (chartMapping[site]) return chartMapping[site];
    //     return null; // site6+ tidak ada mini chart
    // }

    // -------------------------
    // 5. Load Logic
    // -------------------------
    function loadHistogramChart(site, isMainCarousel = false, forceRefresh = false) {
        // Viewer hanya dari cache
        if (isMainCarousel) {
            renderViewerFromCache(site);
            return;
        }

        const actual = resolveSite(site);
        const chartId = getChartSelectorForSite(actual, isMainCarousel);

        // Kalau tidak punya mini chart, tetap render ke viewer (main carousel)

        // Kalau site6+ (tidak punya mini chart) → tetap boleh load data dan tampil di viewer
        if (!chartId && !isMainCarousel) {
            // tetap ambil data ke API, tapi tidak render mini chart
            console.log(`[INFO] ${actual} tidak punya mini chart, hanya tampil di viewer`);
        }


        const instanceKey = isMainCarousel ? `viewer` : actual;
        const { $row } = safeRowForSite(actual);

        // Row tidak ditemukan
        if (!$row || $row.length === 0) {
            if (window.cachedChartData[actual] && !forceRefresh) {
                renderApexHistogram(chartId, window.cachedChartData[actual], actual, instanceKey);
            } else {
                $(chartId).html('<div class="text-muted small">Site configuration tidak ditemukan.</div>');
            }
            return;
        }

        // Sedang loading dan tidak force
        if (window.loadingCharts[actual] && !forceRefresh) return;

        // Pakai cache bila ada & tidak force
        if (window.cachedChartData[actual] && !forceRefresh) {
            renderApexHistogram(chartId, window.cachedChartData[actual], actual, instanceKey);
            return;
        }

        // Ambil dropdown
        let valLine = $row.find('.line').val();
        let valApp = $row.find('.application').val();
        let valFile = $row.find('.file').val();
        let valHead = $row.find('.headers').val();

        // Ambil input user
        let stdLower = parseFloat($row.find('input[data-type="lcl"]').val());
        let stdUpper = parseFloat($row.find('input[data-type="ucl"]').val());
        let lowBoundary = parseFloat($row.find('input[data-type="lower"]').val());
        let intWidth = parseFloat($row.find('input[data-type="interval"]').val());

        // Normalize NaN -> undefined
        stdLower = Number.isFinite(stdLower) ? stdLower : undefined;
        stdUpper = Number.isFinite(stdUpper) ? stdUpper : undefined;
        lowBoundary = Number.isFinite(lowBoundary) ? lowBoundary : undefined;
        intWidth = Number.isFinite(intWidth) ? intWidth : undefined;

        // DB fallback
        if (window.dbConfig && window.dbConfig[actual]) {
            const db = window.dbConfig[actual];
            if (!valLine && db.line_id) valLine = db.line_id;
            if (!valApp && db.application_id) valApp = db.application_id;
            if (!valFile && db.file_id) valFile = db.file_id;
            if (!valHead && db.header_name) valHead = db.header_name;

            if (stdLower === undefined && db.custom_lcl !== undefined) {
                const v = parseFloat(db.custom_lcl); if (Number.isFinite(v)) stdLower = v;
            }
            if (stdUpper === undefined && db.custom_ucl !== undefined) {
                const v = parseFloat(db.custom_ucl); if (Number.isFinite(v)) stdUpper = v;
            }
            if (lowBoundary === undefined && db.lower_boundary !== undefined) {
                const v = parseFloat(db.lower_boundary); if (Number.isFinite(v)) lowBoundary = v;
            }
            if (intWidth === undefined && db.interval_width !== undefined) {
                const v = parseFloat(db.interval_width); if (Number.isFinite(v)) intWidth = v;
            }
        }

        // File/Header belum ada
        if (!valFile || !valHead) {
            if (window.cachedChartData[actual]) {
                renderApexHistogram(chartId, window.cachedChartData[actual], actual, instanceKey);
                return;
            }
            $(chartId).html(`
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="spinner-border text-primary"></div>
                    <div class="text-muted small ms-2">Pilih File & Header.</div>
                </div>
            `);
            return;
        }

        // Validasi parameter histogram
        const missingParams = (stdLower === undefined) || (stdUpper === undefined) || (lowBoundary === undefined) || (intWidth === undefined);
        if (missingParams) {
            if (window.cachedChartData[actual] && !forceRefresh) {
                renderApexHistogram(chartId, window.cachedChartData[actual], actual, instanceKey);
                return;
            }
            $(chartId).html(`
                <div class="d-flex flex-column justify-content-center align-items-center h-100">
                    <div class="spinner-border text-primary"></div>
                    <div class="text-danger mt-2 small">Pengaturan histogram belum lengkap: isi LCL, UCL, Lower Boundary, Interval Width.</div>
                </div>
            `);
            return;
        }

        // Panggil API
        window.loadingCharts[actual] = true;

        const postData = {
            site_name: actual,
            line_id: valLine,
            application_id: valApp,
            file_id: valFile,
            header_name: valHead,
            table_type: $row.find('.headers').data('table-type') || 'type1',
            standard_upper: stdUpper,
            standard_lower: stdLower,
            lower_boundary: lowBoundary,
            interval_width: intWidth
        };

        $.ajax({
            url: `${HOST_URL}api/chart_data_3sigma.php`,
            type: 'POST',
            data: postData,
            dataType: 'json',
            timeout: 60000,
            success: function (response) {
                // PATCH: robust validate
                if (typeof response !== 'object' || response === null) {
                    if (window.cachedChartData[actual]) {
                        renderApexHistogram(chartId, window.cachedChartData[actual], actual, instanceKey);
                    } else {
                        $(chartId).html('<div class="text-danger small">Invalid API response.</div>');
                    }
                    return;
                }
                if (!response.success) {
                    if (window.cachedChartData[actual]) {
                        renderApexHistogram(chartId, window.cachedChartData[actual], actual, instanceKey);
                    } else {
                        $(chartId).html('<div class="text-danger small">' + (response && response.message ? response.message : 'Error API') + '</div>');
                    }
                    return;
                }

                // Save cache
                window.cachedChartData[actual] = response;

                // Save minimal config
                if (!window.dbConfig[actual]) window.dbConfig[actual] = {};
                window.dbConfig[actual].line_id = valLine;
                window.dbConfig[actual].application_id = valApp;
                window.dbConfig[actual].file_id = valFile;
                window.dbConfig[actual].header_name = valHead;
                window.dbConfig[actual].custom_lcl = stdLower;
                window.dbConfig[actual].custom_ucl = stdUpper;
                window.dbConfig[actual].lower_boundary = lowBoundary;
                window.dbConfig[actual].interval_width = intWidth;
                window.dbConfig[actual].cp_limit =
                    $row.find('input[data-type="cp_limit"]').val();

                window.dbConfig[actual].cpk_limit =
                    $row.find('input[data-type="cpk_limit"]').val();

                // render
                renderApexHistogram(chartId, response, actual, instanceKey);
                // 🧩 Jika site6+ (tidak punya mini chart), tetap render ke viewer
                if (!chartMapping[actual]) {
                    console.log(`[INFO] Render viewer dari site tanpa mini chart: ${actual}`);
                    window.cachedChartData[actual] = response;
                    renderViewerFromCache(actual);
                }

                if (actual === window.currentMainSite) {
                    updateMainCpCpkTable(actual);
                }
                // 🔔 Jika CP/CPK NG, tampilkan alert
                // const hasOOC = Number(response.out_of_control_count || 0) > 0;
                // const finalStatus =
                //     response.cp_status === "OK" &&
                //         response.cpk_status === "OK" &&
                //         !hasOOC
                //         ? "OK"
                //         : "NG";
                const finalStatus =
                    response.cp_status === "OK" &&
                        response.cpk_status === "OK"
                        ? "OK"
                        : "NG";

                if (finalStatus === "NG") {
                    const { $row } = safeRowForSite(actual);
                    const info = {
                        type: "cp_result",
                        site: window.dbConfig?.[actual]?.site_label || actual.toUpperCase(),
                        line: $row.find('.line option:selected').text() || '-',
                        app: $row.find('.application option:selected').text() || '-',
                        file: $row.find('.file option:selected').text() || '-',
                        header: $row.find('.headers option:selected').text() || '-',
                        cp: response.cp?.toFixed(3),
                        cpk: response.cpk?.toFixed(3),
                        cp_status: response.cp_status,
                        cpk_status: response.cpk_status,
                        final_status: finalStatus
                    };

                    // ✅ Tambahkan pengecekan supaya tidak muncul berulang
                    const lastAlert = window.lastCpCpkAlert?.[actual];
                    const currentKey =
                        `${info.cp_status}_${info.cpk_status}_${info.cp}_${info.cpk}`;


                    if (!lastAlert || lastAlert !== currentKey) {
                        window.lastCpCpkAlert = window.lastCpCpkAlert || {};
                        window.lastCpCpkAlert[actual] = currentKey;
                        if (info.final_status === "NG") {
                            window.alertQueue.push(info);
                            showNextAlert();
                        }
                    }
                }

                // ✅ Update status icon berdasarkan hasil CP/CPK
                const statusIcon = document.getElementById(`${actual}StatusIcon`);
                const alertIcon = document.getElementById(`${actual}AlertIcon`);

                if (finalStatus === "OK") {
                    statusIcon.style.display = "inline-block";
                    statusIcon.style.backgroundColor = "green";
                    alertIcon.style.display = "none";
                } else {
                    statusIcon.style.display = "none";
                    alertIcon.style.display = "inline-block";
                }

                // update icons / alerts
                // const alertIcon = document.getElementById(`${actual}AlertIcon`);
                // const statusIcon = document.getElementById(`${actual}StatusIcon`);
                // if (alertIcon && statusIcon) {
                //     if (response.out_of_control) {
                //         alertIcon.style.display = "inline-block";
                //         statusIcon.style.display = "none";
                //     } else {
                //         alertIcon.style.display = "none";
                //         statusIcon.style.display = "inline-block";
                //     }
                // }
                // if (response.out_of_control && !window.shownAlerts[actual]) {
                //     window.shownAlerts[actual] = true;
                //     window.alertQueue.push({
                //         site: actual.toUpperCase(),
                //         line: $row.find('.line option:selected').text(),
                //         app: $row.find('.application option:selected').text(),
                //         file: $row.find('.file option:selected').text(),
                //         header: $row.find('.headers option:selected').text(),
                //         count: response.out_of_control_count,
                //         lcl: response.lsl,
                //         ucl: response.usl,
                //         min: response.out_of_control_min,
                //         max: response.out_of_control_max
                //     });
                //     showNextAlert();
                // }
            },
            error: function (xhr, status) {
                if (window.cachedChartData[actual]) {
                    renderApexHistogram(chartId, window.cachedChartData[actual], actual, instanceKey);
                } else if (status === 'timeout') {
                    $(chartId).html('<div class="text-danger small">Request timeout.</div>');
                } else {
                    $(chartId).html('<div class="text-danger small">Error API.</div>');
                }
            },
            complete: function () {
                window.loadingCharts[actual] = false;
            }
        });
    }

    // -------------------------
    // 6. Title Update
    // -------------------------
    function updateMainTitle(site) {
        const actual = resolveSite(site);
        const siteLabel =
            window.dbConfig?.[actual]?.site_label || actual.toUpperCase();
        $('#mainHeaderTitle').text(siteLabel);
        const { $row } = safeRowForSite(actual);
        if (!$row || $row.length === 0) {
            const label = window.dbConfig?.[actual]?.site_label || actual.toUpperCase();

            $("#mainChartTitle").html(`<div class="fs-6 text-dark"><span class="fw-bold">📊 ${label}</span><br><small>Site config belum tersedia</small></div>`);
            return;
        }
        const lineText = $row.find('.line option:selected').text() || '-';
        const appText = $row.find('.application option:selected').text() || '-';
        const fileText = $row.find('.file option:selected').text() || '-';
        const headerText = $row.find('.headers option:selected').text() || '-';

        const titleHTML = `
            <div class="fs-6 text-dark">
                <span class="fw-bold">📊 ${window.dbConfig?.[actual]?.site_label || actual.toUpperCase()}</span>
                <small>Line: <span class="text-primary">${lineText}</span> |
                App: <span class="text-success">${appText}</span> |
                File: <span class="text-info">${fileText}</span> |
                Header: <span class="text-warning">${headerText}</span></small>
            </div>
        `;
        $("#mainChartTitle").html(titleHTML);
    }

    function updateMainCpCpkTable(site) {
        const actual = resolveSite(site);

        const data = window.cachedChartData[actual];
        const cfg = window.dbConfig?.[actual];

        // Jika belum ada data
        if (!data) {
            $('#mainCpStandard, #mainCpActual, #mainCpkStandard, #mainCpkActual').text('-');
            return;
        }

        // Standard (limit)
        const cpLimit = cfg?.cp_limit ? parseFloat(cfg.cp_limit) : '-';
        const cpkLimit = cfg?.cpk_limit ? parseFloat(cfg.cpk_limit) : '-';

        // Actual (hasil perhitungan)
        const cpActual = Number.isFinite(data.cp) ? data.cp.toFixed(3) : '-';
        const cpkActual = Number.isFinite(data.cpk) ? data.cpk.toFixed(3) : '-';

        $('#mainCpStandard').text(cpLimit !== '-' ? cpLimit.toFixed(3) : '-');
        $('#mainCpkStandard').text(cpkLimit !== '-' ? cpkLimit.toFixed(3) : '-');

        $('#mainCpActual').text(cpActual);
        $('#mainCpkActual').text(cpkActual);
    }

    // -------------------------
    // 7. Settings & Events
    // -------------------------
    function saveSiteSettings(site) {
        const actual = resolveSite(site);
        const { $row } = safeRowForSite(actual);
        if (!$row || $row.length === 0) return;

        const $header = $row.find('.headers');

        const settingsData = {
            site_name: actual,
            line_id: $row.find('.line').val(),
            application_id: $row.find('.application').val(),
            file_id: $row.find('.file').val(),
            header_name: $header.val(),
            is_active: $row.find('.dashboard-toggle input').is(':checked'),
            table_type: $header.data('table-type') || 'type1'
        };

        // Tambahkan semua limit yang disimpan ke DB
        settingsData.custom_lcl = $row.find('input[data-type="lcl"]').val();
        settingsData.custom_ucl = $row.find('input[data-type="ucl"]').val();
        settingsData.lower_boundary = $row.find('input[data-type="lower"]').val();
        settingsData.interval_width = $row.find('input[data-type="interval"]').val();
        settingsData.cp_limit = $row.find('input[data-type="cp_limit"]').val();
        settingsData.cpk_limit = $row.find('input[data-type="cpk_limit"]').val();
        settingsData.site_label = $row.find('.site-label-input').val() || null;
        $.ajax({
            url: `${HOST_URL}api/save_dashboard_setting.php`,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(settingsData),
            dataType: 'json',
            timeout: 10000
        });
    }

    // Event: Tambah Site
    $('#btnAddSite').click(function () {
        let lastNum = 0;

        $('.site-setting-row').each(function () {
            const num = parseInt($(this).data('site').replace('site', ''));
            if (num > lastNum) lastNum = num;
        });

        const newNum = lastNum + 1;
        const newSiteName = 'site' + newNum;

        // 1. Clone dari site1
        const $clone = $('.site-setting-row[data-site="site1"]').clone(true, true);

        // 2. Update row
        $clone.attr('id', 'row_' + newSiteName);
        $clone.attr('data-site', newSiteName);
        $clone.find('[data-site]').each(function () {
            $(this)
                .attr('data-site', newSiteName)
                .data('site', newSiteName); // 🔥 INI YANG KRUSIAL
        });


        // 3. Reset label
        $clone.find('.site-label-text').text('Site ' + newNum).removeClass('d-none');
        $clone.find('.site-label-input').val('').addClass('d-none');
        $clone.find('.site-label-save').addClass('d-none');
        $clone.find('.site-label-edit').removeClass('d-none');

        // 4. Reset dropdown & input
        $clone.find('select').val('');
        $clone.find('.application, .file, .headers')
            .html('<option value="">Select</option>')
            .prop('disabled', true);

        $clone.find('.limit-input').val('');

        // 5. Append
        $('#settingsContainer').append($clone);
        $('#settingsContainer').append('<div class="separator separator-dashed my-5"></div>');
        ensureRemoveButton($clone, newSiteName);


        // 6. INIT STATE (🔥 PALING PENTING)
        window.dbConfig[newSiteName] = {
            site_label: 'Site ' + newNum
        };
        initSite(newSiteName);

        // 7. Set jadi main site
        window.currentMainSite = newSiteName;

        // 8. Update UI (SETELAH state siap)
        updateSiteLabel(newSiteName);
        updateMainTitle(newSiteName);
        updateMainCpCpkTable(newSiteName);

        $('#mainChartViewer').html(`
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="text-muted small">Pilih Line untuk mulai</div>
        </div>
    `);



        SITES = getAllSites();
    });


    // Event: Hapus Site
    $(document).on('click', '.btn-remove-site', function () {
        const siteName = $(this).data('site');
        const $row = $(this).closest('.site-setting-row');
        const $separator = $row.next('.separator');

        Swal.fire({
            title: 'Hapus Site?',
            text: `Konfigurasi ${siteName.toUpperCase()} akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $row.fadeOut(300, function () {
                    $(this).remove();
                    if ($separator.length) $separator.remove();
                    SITES = getAllSites();
                });

                $.ajax({
                    url: `${HOST_URL}api/delete_dashboard_setting.php`,
                    type: 'POST',
                    data: { site_name: siteName },
                    success: function (res) {
                        if (res.success) {
                            delete window.cachedChartData[siteName];
                            delete window.dbConfig[siteName];
                            Swal.fire({ icon: 'success', title: 'Terhapus', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                        }
                    }
                });
            }
        });
    });

    // Event: Ganti Global Interval
    $(document).on('change', '#globalIntervalSelect', function () {
        const newInterval = parseInt($(this).val());
        startGlobalPolling(newInterval);
    });

    // Event: Input LCL/UCL/Boundary/Interval Change (PATCH: selalu refresh mini chart)
    let debounceTimers = {};

    $(document).on('input change', '.limit-input', function () {
        const site = $(this).attr('data-site');
        const chartId = getChartSelectorForSite(site, false);

        // Hentikan timer sebelumnya kalau user masih mengetik
        clearTimeout(debounceTimers[site]);

        // Tunda 400 ms sebelum kirim request baru
        debounceTimers[site] = setTimeout(() => {
            // Tampilkan indikator loading kecil
            if (chartId && $(chartId).length) {
                $(chartId).empty().append(`
        <div class="d-flex flex-column justify-content-center align-items-center h-100 text-muted small">
            <div class="spinner-border text-primary mb-2" role="status" style="width: 1.5rem; height: 1.5rem;"></div>
            <div>Updating chart...</div>
        </div>
    `);
            } else {
                console.warn("Chart element not found for", site, chartId);
            }

            // Simpan ke database dan muat ulang chart
            saveSiteSettings(site);
            loadHistogramChart(site, false, true);
        }, 400); // debounce 400 ms
    });

    // Event Listener Dropdown (Cascade)
    $(document).on('change', '.line', function () {
        const site = $(this).attr('data-site');
        const lineId = $(this).val();
        const $application = $(`.application[data-site="${site}"]`);
        $application.prop('disabled', true).html('<option value="">Select</option>');
        if (!lineId) return;
        $.ajax({
            url: `${HOST_URL}api/get_applications.php`,
            type: 'POST',
            data: { line_id: lineId },
            dataType: 'json',
            success: function (response) {
                $application.prop('disabled', false).html('<option value="">Select</option>');
                $.each(response, function (i, item) { $application.append(`<option value="${item.id}">${item.name}</option>`); });
                const savedAppId = $(`.line[data-site="${site}"]`).data('app-id');
                if (savedAppId) {
                    $application.val(savedAppId);
                    $application.trigger('change');
                } else {
                    // 🔥 SITE BARU: pastikan dropdown aktif & bisa dipilih
                    $application.prop('disabled', false);
                }
            }
        });
    });

    $(document).on('change', '.application', function () {
        const site = $(this).attr('data-site');
        const appId = $(this).val();
        const $file = $(`.file[data-site="${site}"]`);
        $file.prop('disabled', true).html('<option value="">Select</option>');
        if (!appId) return;
        $.ajax({
            url: `${HOST_URL}api/get_files.php`,
            type: 'POST',
            data: { app_id: appId },
            dataType: 'json',
            success: function (response) {
                $file.prop('disabled', false).html('<option value="">Select</option>');
                $.each(response, function (i, item) { $file.append(`<option value="${item.id}">${item.name}</option>`); });
                const savedFileId = $(`.line[data-site="${site}"]`).data('file-id');
                if (savedFileId) { $file.val(savedFileId); $file.trigger('change'); }
                $(`.line[data-site="${site}"]`).data('file-id', null);
            }
        });
    });

    $(document).on('change', '.file', function () {
        const site = $(this).attr('data-site');
        const fileId = $(this).val();
        const $header = $(`.headers[data-site="${site}"]`);
        $header.prop('disabled', true).html('<option value="">Select</option>');
        if (!fileId) return;
        $.ajax({
            url: `${HOST_URL}api/get_headers.php`,
            type: 'POST',
            data: { file_id: fileId },
            dataType: 'json',
            success: function (response) {
                $header.prop('disabled', false).html('<option value="">Select</option>');
                const tableType = response.type || 'type1';
                $header.data('table-type', tableType);
                if (response.headers && Array.isArray(response.headers)) {
                    $.each(response.headers, function (i, item) {
                        const type = item.table_type || 'type1';
                        $header.append(`<option value="${item.header_name}" data-table-type="${type}">${item.header_name}</option>`);
                    });

                    // simpan default type dari header pertama
                    const firstType = response.headers[0]?.table_type || 'type1';
                    $header.data('table-type', firstType);
                }

                const savedHeaderName = $(`.line[data-site="${site}"]`).data('header-name');
                if (savedHeaderName) {
                    $header.val(savedHeaderName);
                    $header.trigger('change');
                }
                $(`.line[data-site="${site}"]`).data('header-name', null);
            }
        });
    });

    $(document).on('change', '.headers', function () {
        const site = $(this).attr('data-site');
        const selectedType = $(this).find('option:selected').data('table-type') || 'type1';
        $(this).data('table-type', selectedType); // ✅ baris tambahan penting

        if (site) window.shownAlerts[site] = false;
        saveSiteSettings(site);
        if ($(this).val()) loadHistogramChart(site, false, true);
    });

    $(document).on('change', '.dashboard-toggle input', function () {
        const site = $(this).closest('.dashboard-toggle').attr('data-site');
        saveSiteSettings(site);
        window.alertSettings[site] = $(this).is(':checked');
        if (!window.alertSettings[site]) window.shownAlerts[site] = false;
    });

    $(document).on('click', '[id$="InfoIcon"]', function () {
        let site = $(this).attr('id').replace("InfoIcon", "");
        if (!site) return;
        if (site === 'main') site = window.currentMainSite;

        const data = window.cachedChartData[site];
        if (!data) {
            Swal.fire('ℹ️', 'Belum ada data untuk site ini.', 'info');
            return;
        }
        const isOk =
            data.cp_status === "OK" &&
            data.cpk_status === "OK";


        Swal.fire({
            icon: 'info',
            title: `📊 Detail CP/CPK — ${site.toUpperCase()}`,
            html: `
        <div class="text-start">
            <p><b>Quantity:</b> ${data.debug_total_data ?? '-'}</p>
            <p><b>Min:</b> ${data.min_val ?? '-'}</p>
            <p><b>Max:</b> ${data.max_val ?? '-'}</p>
            <p><b>Average:</b> ${data.rata_rata ?? '-'}</p>
            <p><b>Stdev:</b> ${data.standar_deviasi ?? '-'}</p>
            <hr>
        <p><b>CP:</b> ${data.cp ?? '-'} (${data.cp_status ?? '-'})</p>
            <p><b>CP Limit:</b> ${data.std_limit_cp ?? '-'}</p>
            <p><b>CPK:</b> ${data.cpk ?? '-'} (${data.cpk_status ?? '-'})</p>
            <p><b>CPK Limit:</b> ${data.std_limit_cpk ?? '-'}</p>
            <p><b>NG Estimation:</b> ${parseFloat(data.estimated_defect_rate).toFixed(5)}</p>
            <p><b>NG Actual:</b> ${(data.out_of_control_percent).toFixed(3)}%</p>
            <hr>
            <p><b>Status:</b> 
                <span class="${isOk ? 'text-success' : 'text-danger'}">
                    ${isOk ? 'OK ✅' : 'NG ❌'}
                </span>
            </p>
        </div>
    `,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#007bff'
        });
    });

    // Event: Manual alert dari viewer utama (carousel)
    $(document).on('click', '#btnMainAlert', function () {
        const site = window.currentMainSite;
        if (!site) {
            Swal.fire('ℹ️', 'Belum ada site aktif di carousel.', 'info');
            return;
        }
        showManualAlert(site);
    });

    // Event: Info icon di viewer utama (sama seperti mini chart)
    $(document).on('click', '#mainInfoIcon', function () {
        const site = window.currentMainSite;
        if (!site) {
            Swal.fire('ℹ️', 'Belum ada site aktif di carousel.', 'info');
            return;
        }

        const data = window.cachedChartData[site];
        if (!data) {
            Swal.fire('ℹ️', 'Belum ada data untuk site ini.', 'info');
            return;
        }
        // const hasOOC = Number(data.out_of_control_count || 0) > 0;
        // const isOk =
        //     data.cp_status === "OK" &&
        //     data.cpk_status === "OK" &&
        //     !hasOOC;
        const isOk =
            data.cp_status === "OK" &&
            data.cpk_status === "OK";

        Swal.fire({
            icon: 'info',
            title: `📊 Detail CP/CPK — ${site.toUpperCase()}`,
            html: `
        <div class="text-start">
            <p><b>Quantity:</b> ${data.debug_total_data ?? '-'}</p>
            <p><b>Min:</b> ${data.min_val ?? '-'}</p>
            <p><b>Max:</b> ${data.max_val ?? '-'}</p>
            <p><b>Average:</b> ${data.rata_rata ?? '-'}</p>
            <p><b>Stdev:</b> ${data.standar_deviasi ?? '-'}</p>
            <hr>
            <p><b>CP:</b> ${data.cp ?? '-'} (${data.cp_status ?? '-'})</p>
            <p><b>CP Limit:</b> ${data.std_limit_cp ?? '-'}</p>
            <p><b>CPK:</b> ${data.cpk ?? '-'} (${data.cpk_status ?? '-'})</p>
            <p><b>CPK Limit:</b> ${data.std_limit_cpk ?? '-'}</p>
          <p><b>NG Estimation:</b> ${parseFloat(data.estimated_defect_rate).toFixed(5)}</p>
            <p><b>NG Actual:</b> ${(data.out_of_control_percent).toFixed(3)}%</p>
            <hr>
            <p><b>Status:</b> 
                <span class="${isOk ? 'text-success' : 'text-danger'}">
                    ${isOk ? 'OK ✅' : 'NG ❌'}
                </span>
            </p>
        </div>
        `,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#007bff'
        });
    });

    // -------------------------
    // 8. Initialization
    // -------------------------
    (function initCarousel() {
        let currentIndex = 0;
        let paused = false;
        const btn = document.getElementById("toggleCarousel");

        function nextChart() {
            if (paused) return;

            SITES = getAllSites(); // Refresh List
            if (SITES.length === 0) return;

            if (currentIndex >= SITES.length) currentIndex = 0;
            const site = SITES[currentIndex];

            // Tampilkan SEMUA (tidak skip toggle off)
            window.currentMainSite = site;
            updateSiteLabel(site);
            updateMainTitle(site);
            updateMainCpCpkTable(site);

            // Indikator Loading Main
            if (!window.cachedChartData[site]) {
                $('#mainChartViewer').html('<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary"></div></div>');
            }
            renderViewerFromCache(site);
            currentIndex = (currentIndex + 1) % SITES.length;
        }

        if (btn) {
            btn.addEventListener('click', function () {
                paused = !paused;
                btn.innerHTML = paused ? "▶️ Play" : "⏸️ Pause";
                if (!paused) nextChart();
            });
        }
        nextChart();
        carouselIntervalId = setInterval(nextChart, 5000);
    })();

    // Init Polling Global
    (function initGlobalPolling() {
        const defaultInterval =
            parseInt($('#globalIntervalSelect').val()) || 20000;
        startFocusedPolling(defaultInterval);
    })();


    (function initPageLoad() {
        $('.dashboard-toggle input').each(function () {
            const site = $(this).closest('.dashboard-toggle').attr('data-site');
            window.alertSettings[site] = $(this).is(':checked');
        });
        // 🔥 TAMBAHAN INI
        Object.keys(window.dbConfig || {}).forEach(site => {
            if (window.dbConfig[site]?.site_label) {
                const el = document.getElementById(`${site}Label`);
                if (el) el.textContent = window.dbConfig[site].site_label;
            }
        });

        $('.line').each(function () { if ($(this).val()) $(this).trigger('change'); });
        $('.site-setting-row').each(function () {
            const site = $(this).attr('data-site');
            const cfg = window.dbConfig[site];
            if (!cfg) return;

            $(this).find('input[data-type="lcl"]').val(cfg.custom_lcl);
            $(this).find('input[data-type="ucl"]').val(cfg.custom_ucl);
            $(this).find('input[data-type="lower"]').val(cfg.lower_boundary);
            $(this).find('input[data-type="interval"]').val(cfg.interval_width);
            $(this).find('input[data-type="cp_limit"]').val(cfg.cp_limit);
            $(this).find('input[data-type="cpk_limit"]').val(cfg.cpk_limit);
        });
    })();

    $(document).on('click', '[id$="AlertIcon"]', function () {
        let site = $(this).attr('id').replace("AlertIcon", "");
        if (!site) return;
        if (site === 'main') site = window.currentMainSite;
        showManualAlert(site);
    });

    // ===============================
    // SITE LABEL EDIT / SAVE
    // ===============================
    $(document).on('click', '.site-label-edit', function () {
        const site = $(this).attr('data-site');
        const $row = $(`.site-setting-row[data-site="${site}"]`);

        $row.find('.site-label-text').addClass('d-none');
        $row.find('.site-label-edit').addClass('d-none');

        $row.find('.site-label-input').removeClass('d-none').focus();
        $row.find('.site-label-save').removeClass('d-none');
    });

    $(document).on('click', '.site-label-save', function () {
        const site = $(this).attr('data-site');
        const $row = $(`.site-setting-row[data-site="${site}"]`);

        const newLabel =
            $row.find('.site-label-input').val().trim() || site.toUpperCase();

        // Update Settings UI
        $row.find('.site-label-text')
            .text(newLabel)
            .removeClass('d-none');

        $row.find('.site-label-input').addClass('d-none');
        $row.find('.site-label-save').addClass('d-none');
        $row.find('.site-label-edit').removeClass('d-none');

        // 🔥 SIMPAN KE STATE
        window.dbConfig[site] = window.dbConfig[site] || {};
        window.dbConfig[site].site_label = newLabel;

        // 🔥 UPDATE MINI CARD
        const labelEl = document.getElementById(`${site}Label`);
        if (labelEl) labelEl.textContent = newLabel;

        // 🔥 UPDATE MAIN TITLE JIKA AKTIF
        if (site === window.currentMainSite) {
            updateMainTitle(site);
        }

        // 🔥 SAVE KE DB
        saveSiteSettings(site);
    });

    $(window).on('beforeunload unload', function () {
        if (globalPollingId) clearInterval(globalPollingId);
        if (carouselIntervalId) clearInterval(carouselIntervalId);
    });
});
