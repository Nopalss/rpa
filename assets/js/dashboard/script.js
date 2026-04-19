$(document).ready(function () {
    // ===============================
    // MAIN CAROUSEL CONFIG
    // ===============================
    const isViewer = false;
    const MAIN_SLOTS = 4;
    window.mainSlots = Array.from({ length: MAIN_SLOTS }, (_, i) => ({
        slot: i,
        site: null
    }));


    async function runMainCarousel() {
        if (window.carouselPausedAll) {
            console.log('[CAROUSEL] Paused ALL');
            return;
        }

        // pastikan grup selalu up-to-date
        window.mainSiteGroups = buildMainSiteGroups();

        for (let mainIdx = 0; mainIdx < MAIN_SLOTS; mainIdx++) {
            if (window.pausedSlots[mainIdx]) {
                console.log(`[CAROUSEL] Slot ${mainIdx} paused`);
                continue;
            }

            let sites = window.mainSiteGroups[mainIdx] || [];

            if (!sites || sites.length === 0) continue;
            // 🔥 APPLY FILTER DI SINI
            sites = sites.filter(site => {
                const data = window.cachedChartData[site];

                if (!data) return true; // biar tetap bisa fetch

                if (window.siteFilterMode === 'all') return true;

                if (data.insufficient_data) return false;

                const isOk =
                    data.cp_status === 'OK' &&
                    data.cpk_status === 'OK';

                if (window.siteFilterMode === 'ok') return isOk;
                if (window.siteFilterMode === 'ng') return !isOk;

                return true;
            });

            const idx = window.mainCarouselIndex[mainIdx] % sites.length;
            const site = sites[idx];

            window.mainCarouselIndex[mainIdx] =
                (window.mainCarouselIndex[mainIdx] + 1) % sites.length;

            const currentSite = window.mainSlots[mainIdx].site;

            if (
                currentSite === site &&
                window.loadingCharts[site]
            ) {
                continue;
            }
            window.mainSlots[mainIdx].site = site;

            const selector = `#mainChartViewer_${mainIdx}`;

            // 🔒 MAIN CHART HANYA BOLEH PAKAI CACHE
            const data = window.cachedChartData[site];

            if (data) {
                renderApexHistogram(
                    selector,
                    data,
                    site,
                    `main_slot_${mainIdx}`
                );

                updateMainCpCpkTable(mainIdx, site);
                updateMainHeaderTitle(mainIdx, site);

            } else {
                enqueueChartRequest(site, false);

                const hasChart =
                    window.apexChartsInstances[`main_slot_${mainIdx}`];

                if (!hasChart) {
                    $(selector).html(`
            <div class="d-flex flex-column justify-content-center align-items-center h-100">
                <div class="spinner-border text-primary mb-2"></div>
                <div class="text-muted small">Loading data...</div>
            </div>
        `);
                }
            }
            // jeda kecil antar slot (aman)
            await new Promise(r => setTimeout(r, 300));
        }
    }

    function getSiteNumber(site) {
        return parseInt(site.replace('site', ''), 10);
    }

    // mainIndex: 0 = Main1, 1 = Main2, 2 = Main3, 3 = Main4
    function getMainIndexForSite(site) {
        const num = getSiteNumber(site);
        if (!Number.isFinite(num)) return null;
        return (num - 1) % MAIN_SLOTS;
    }

    // Kelompok site per main
    function buildMainSiteGroups() {
        const groups = Array.from({ length: MAIN_SLOTS }, () => []);
        const sites = getAllSites();

        sites.forEach(site => {
            const idx = getMainIndexForSite(site);
            if (idx !== null) groups[idx].push(site);
        });

        return groups;
    }

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

    if (typeof HOST_URL === 'undefined') { console.warn('HOST_URL is not defined.'); }


    // ===============================
    // CAROUSEL STATE PER MAIN
    // ===============================
    window.mainSiteGroups = buildMainSiteGroups();

    // index carousel untuk tiap main
    window.mainCarouselIndex = Array.from({ length: MAIN_SLOTS }, () => 0);

    window.carouselPausedAll = false;
    window.pausedSlots = {}; // { 0: true, 2: true }

    window.apexChartsInstances = {};
    window.cachedChartData = {};
    window.alertQueue = [];
    window.isAlertShowing = false;
    window.alertSettings = {};
    window.shownAlerts = {};
    window.apiFailCount = {};
    window.loadingCharts = {};
    window.isInitializingPage = true;
    // ===============================
    // GLOBAL API QUEUE (STEP 2)
    // ===============================
    window.chartApiQueue = [];
    window.chartApiBusy = false;

    window.siteFilterMode = 'all';

    function enqueueChartRequest(site, forceRefresh = false) {
        // 🔥 SKIP kalau sudah ada data & bukan force
        if (!forceRefresh && window.cachedChartData[site]) {
            return;
        }
        if (window.loadingCharts[site]) return;

        // 🔥 FAILSAFE RESET (WAJIB)
        setTimeout(() => {
            window.loadingCharts[site] = false;
        }, 30000);

        const existing = window.chartApiQueue.find(q => q.site === site);
        if (existing) {
            existing.forceRefresh = existing.forceRefresh || forceRefresh;
            return;
        }

        window.chartApiQueue.push({ site, forceRefresh });
        processChartQueue();
    }


    window.chartApiWorkers = 0;
    const MAX_WORKERS = 6;

    async function processChartQueue() {
        if (window.chartApiWorkers >= MAX_WORKERS) return;
        if (window.chartApiQueue.length === 0) return;

        const { site, forceRefresh } = window.chartApiQueue.shift();
        window.chartApiWorkers++;

        try {
            await loadHistogramChartInternal(site, forceRefresh);
            window.apiFailCount[site] = 0;

        } catch (e) {
            window.apiFailCount[site] =
                (window.apiFailCount[site] || 0) + 1;

            console.warn(`Retry ${site} attempt ${window.apiFailCount[site]}`);

            // 🔥 retry max 3x
            if (window.apiFailCount[site] < 3) {
                window.chartApiQueue.push({
                    site,
                    forceRefresh: true
                });
            }

        } finally {
            window.chartApiWorkers--;

            setTimeout(() => {
                processChartQueue();
            }, 300);
        }

    }



    window.currentMainSite = window.currentMainSite || SITES[0];
    window.dbConfig = window.dbConfig || {};
    window.renderingChart = window.renderingChart || {}; // PATCH: guard render per chart key

    let carouselIntervalId = null;

    // -------------------------
    // 2. Utilities
    // -------------------------
    function resolveSite(site) {
        // Di arsitektur baru, site carousel sudah final
        // Tidak ada lagi 'main' / 'viewer' mapping
        return site;
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

    function initSite(siteName) {
        if (window.dbConfig?.[siteName]) return; // ⛔ JANGAN reset site existing

        const $row = $(`.site-setting-row[data-site="${siteName}"]`);
        if (!$row.length) return;

        $row.find('.line').prop('disabled', false);
        $row.find('.application, .file, .headers')
            .prop('disabled', true)
            .html('<option value="">Select</option>');

        $row.find('.limit-input').val('');
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
        if (data.insufficient_data) {
            Swal.fire('ℹ️', 'Data belum cukup (minimal 20).', 'info');
            return;
        }
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
            quantity: data.debug_total_data ?? '-',
            min: data.min_val ?? '-',
            max: data.max_val ?? '-',
            average: data.rata_rata ?? '-',
            std: data.standar_deviasi ?? '-',
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
                <p><b>Quantity:</b> ${info.quantity ?? '-'}</p>
                <p><b>Min:</b> ${info.min ?? '-'}</p>
                <p><b>Max:</b> ${info.max ?? '-'}</p>
                <p><b>Average:</b> ${info.average ?? '-'}</p>
                <p><b>Stdev:</b> ${info.std ?? '-'}</p>
                <hr></hr>
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
        if (data.insufficient_data) {
            $(chartSelector).html(`
        <div class="d-flex flex-column justify-content-center align-items-center h-100 text-warning">
            <div class="fw-bold">⚠ Data Belum Cukup</div>
            <div class="small">Minimal 20 data</div>
            <div class="small">Sekarang: ${data.debug_total_data}</div>
        </div>
    `);
            return;
        }
        if (!chartSelector || !data || !Array.isArray(data.series_data)) {
            if (chartSelector) $(chartSelector).html('<div class="text-danger small">Data tidak valid.</div>');
            return;
        }

        const chartHeight = 225;

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
    // 5. Load Logic
    // -------------------------
    function loadHistogramChartInternal(site, forceRefresh = false) {
        return new Promise((resolve, reject) => {
            const actual = site;
            const instanceKey = actual;
            const { $row } = safeRowForSite(actual);

            // Row tidak ditemukan
            if (!$row || $row.length === 0) {
                resolve();
                return;
            }

            // Sedang loading dan tidak force
            if (window.loadingCharts[actual] && !forceRefresh) {
                resolve();
                return;
            };


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

            // Normalize NaN → undefined
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
                    const v = parseFloat(db.custom_lcl);
                    if (Number.isFinite(v)) stdLower = v;
                }
                if (stdUpper === undefined && db.custom_ucl !== undefined) {
                    const v = parseFloat(db.custom_ucl);
                    if (Number.isFinite(v)) stdUpper = v;
                }
                if (lowBoundary === undefined && db.lower_boundary !== undefined) {
                    const v = parseFloat(db.lower_boundary);
                    if (Number.isFinite(v)) lowBoundary = v;
                }
                if (intWidth === undefined && db.interval_width !== undefined) {
                    const v = parseFloat(db.interval_width);
                    if (Number.isFinite(v)) intWidth = v;
                }
            }

            // File/Header belum ada
            if (!valFile || !valHead) {
                resolve();
                return;
            }

            // Validasi parameter histogram
            const missingParams =
                stdLower === undefined ||
                stdUpper === undefined ||
                lowBoundary === undefined ||
                intWidth === undefined;

            if (missingParams) {
                resolve();
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
                    if (typeof response !== 'object' || response === null) {
                        resolve();
                        return;
                    }
                    if (!response.success) {
                        resolve();
                        return;
                    }


                    // 🔥 FIX: BLOCK ALERT JIKA DATA < 20
                    if (response.insufficient_data) {
                        window.cachedChartData[actual] = response;
                        resolve();
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

                    // Render

                    const finalStatus =
                        response.cp_status === "OK" &&
                            response.cpk_status === "OK"
                            ? "OK"
                            : "NG";

                    if (finalStatus === "NG") {
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

                        const lastAlert = window.lastCpCpkAlert?.[actual];
                        const currentKey =
                            `${info.cp_status}_${info.cpk_status}_${info.cp}_${info.cpk}`;

                        if (!lastAlert || lastAlert !== currentKey) {
                            window.lastCpCpkAlert = window.lastCpCpkAlert || {};
                            window.lastCpCpkAlert[actual] = currentKey;
                            window.alertQueue.push(info);
                            showNextAlert();
                        }
                    }

                    const statusIcon = document.getElementById(`${actual}StatusIcon`);
                    const alertIcon = document.getElementById(`${actual}AlertIcon`);

                    if (finalStatus === "OK") {
                        if (statusIcon) {
                            statusIcon.style.display = "inline-block";
                            statusIcon.style.backgroundColor = "green";
                        }
                        if (alertIcon) alertIcon.style.display = "none";
                    } else {
                        if (statusIcon) statusIcon.style.display = "none";
                        if (alertIcon) alertIcon.style.display = "inline-block";
                    }
                },
                error: function (xhr, status) {
                    reject(new Error('API failed'));
                },
                complete: function () {
                    window.loadingCharts[actual] = false;
                    resolve(); // 🔥 INI KUNCI
                }
            });
        });
    }

    function loadHistogramChart(site, isMainCarousel = false, forceRefresh = false) {
        // Carousel & UI TIDAK BOLEH langsung panggil API
        // Semua HARUS lewat QUEUE
        enqueueChartRequest(site, forceRefresh);
    }

    function updateMainHeaderTitle(slotIndex, site) {
        const cfg = window.dbConfig?.[site];
        const label = cfg?.site_label || site.toUpperCase();

        const statusIcon = getCpCpkStatusIcon(site);

        $(`#mainHeaderTitle_${slotIndex}`).html(`
        <div class="fw-bold d-flex align-items-center gap-1">
            <span class="pr-2">${label}</span>
            ${statusIcon}
        </div>
    `);
        const mode = window.siteFilterMode?.toUpperCase();

        $(`#mainHeaderTitle_${slotIndex}`).append(`
    <span class="ml-2 badge badge-light" style="font-size:10px; padding:2px 6px;">
    ${mode}
</span>
`);
    }

    function getCpCpkStatusIcon(site) {
        const data = window.cachedChartData?.[site];
        if (!data) return '';

        if (data.insufficient_data) {
            return `<span class="text-warning">⚠</span>`;
        }

        const isOk =
            data.cp_status === 'OK' &&
            data.cpk_status === 'OK';

        if (isOk) {
            return `
            <span class="ms-1 pl-2"
                  style="display:inline-block;
                         width:10px;
                         height:10px;
                         border-radius:50%;
                         background:#28a745;"
                  title="OK"></span>
        `;
        }

        return `
        <span class="ms-1 text-danger"
              title="NG"
              style="font-size:14px;">&#9888;</span>
    `;
    }


    function updateMainCpCpkTable(slotIndex, site) {
        const data = window.cachedChartData?.[site];
        const cfg = window.dbConfig?.[site];

        if (!data) {
            $(`#mainCpStandard_${slotIndex},
           #mainCpActual_${slotIndex},
           #mainCpkStandard_${slotIndex},
           #mainCpkActual_${slotIndex}`).text('-');
            return;
        }

        const cpLimit = cfg?.cp_limit ? parseFloat(cfg.cp_limit).toFixed(3) : '-';
        const cpkLimit = cfg?.cpk_limit ? parseFloat(cfg.cpk_limit).toFixed(3) : '-';

        const cpActual = Number.isFinite(data.cp) ? data.cp.toFixed(3) : '-';
        const cpkActual = Number.isFinite(data.cpk) ? data.cpk.toFixed(3) : '-';

        $(`#mainCpStandard_${slotIndex}`).text(cpLimit);
        $(`#mainCpActual_${slotIndex}`).text(cpActual);
        $(`#mainCpkStandard_${slotIndex}`).text(cpkLimit);
        $(`#mainCpkActual_${slotIndex}`).text(cpkActual);
    }


    $(document).on('change', '#filterModeSelect', function () {
        window.siteFilterMode = $(this).val();

        console.log('[FILTER]', window.siteFilterMode);

        // reset carousel biar langsung apply
        window.mainCarouselIndex = [0, 0, 0, 0];
    });
    // -------------------------
    // 7. Settings & Events
    // -------------------------
    window.savingSite = window.savingSite || {};
    function saveSiteSettings(site) {
        if (window.isInitializingPage) return;
        if (window.savingSite[site]) return;

        window.savingSite[site] = true;

        const actual = resolveSite(site);
        const { $row } = safeRowForSite(actual);
        if (!$row || $row.length === 0) {
            window.savingSite[site] = false;
            return;
        }

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
        window.dbConfig[actual] = window.dbConfig[actual] || {};
        Object.assign(window.dbConfig[actual], settingsData);

        // baru kirim AJAX

        $.ajax({
            url: `${HOST_URL}api/save_dashboard_setting.php`,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(settingsData),
            dataType: 'json',
            timeout: 10000,
            complete: function () {
                window.savingSite[site] = false;
            }
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

        // ✅ CLONE TANPA EVENT & DATA
        const $clone = $('.site-setting-row[data-site="site1"]').clone(false, false);

        // ✅ BERSIHKAN SEMUA CACHE JQUERY
        $clone.removeData();
        $clone.find('*').removeData();

        // ✅ UPDATE ATTRIBUTE + DATA CACHE
        $clone.attr('data-site', newSiteName).attr('id', 'row_' + newSiteName);
        $clone.find('[data-site]').each(function () {
            $(this)
                .attr('data-site', newSiteName)
                .data('site', newSiteName);
        });

        // Reset label
        $clone.find('.site-label-text').text('Site ' + newNum).removeClass('d-none');
        $clone.find('.site-label-input').val('').addClass('d-none');
        $clone.find('.site-label-save').addClass('d-none');
        $clone.find('.site-label-edit').removeClass('d-none');

        // Reset dropdown
        $clone.find('select').each(function () {
            this.selectedIndex = 0;
            $(this).prop('disabled', true);
        });
        $clone.find('.line').prop('disabled', false);

        // Reset inputs
        $clone.find('.limit-input').val('');

        // Append
        $('#settingsContainer').append($clone);
        $('#settingsContainer').append('<div class="separator separator-dashed my-5"></div>');
        ensureRemoveButton($clone, newSiteName);

        // Init state
        window.dbConfig[newSiteName] = { site_label: 'Site ' + newNum };
        initSite(newSiteName);

        // window.currentMainSite = newSiteName;
        updateSiteLabel(newSiteName);
        // updateMainTitle(newSiteName);
        // updateMainCpCpkTable(newSiteName);

        SITES = getAllSites();
        window.mainSiteGroups = buildMainSiteGroups();
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
                            window.mainSiteGroups = buildMainSiteGroups();
                            Swal.fire({ icon: 'success', title: 'Terhapus', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                        }
                    }
                });
            }
        });
    });

    // Event: Ganti Global Interval
    $(document).on('change', '#globalIntervalSelect', function () {
        const newInterval = parseInt($(this).val()) || 120000;
        // startGlobalScheduler(newInterval);
    });

    // Event: Input LCL/UCL/Boundary/Interval Change (PATCH: selalu refresh mini chart)
    let debounceTimers = {};

    $(document).on('change', '.limit-input', function () {
        const site = $(this).attr('data-site');

        saveSiteSettings(site);
        loadHistogramChart(site, false, true);
    });

    // Event Listener Dropdown (Cascade)
    $(document).on('change', '.line', function () {
        const site = $(this).data('site');
        const lineId = $(this).val();
        const $app = $(`.application[data-site="${site}"]`);

        $app.prop('disabled', true).html('<option value="">Select</option>');
        if (!lineId) return;

        $.post(`${HOST_URL}api/get_applications.php`, { line_id: lineId }, function (res) {
            $app.prop('disabled', false).html('<option value="">Select</option>');
            res.forEach(it => $app.append(`<option value="${it.id}">${it.name}</option>`));

            const savedAppId = window.dbConfig?.[site]?.application_id;
            if (savedAppId && res.some(x => String(x.id) === String(savedAppId))) {
                $app.val(savedAppId).trigger('change');
            }
        }, 'json');
    });


    $(document).on('change', '.application', function () {
        const site = $(this).data('site');
        // 🔥 UPDATE STATE
        window.dbConfig[site] = window.dbConfig[site] || {};
        const appId = $(this).val();
        window.dbConfig[site].application_id = appId;
        const $file = $(`.file[data-site="${site}"]`);


        $file.prop('disabled', true).html('<option value="">Select</option>');
        if (!appId) return;

        $.post(`${HOST_URL}api/get_files.php`, { app_id: appId }, function (res) {
            $file.prop('disabled', false).html('<option value="">Select</option>');
            res.forEach(it => $file.append(`<option value="${it.id}">${it.name}</option>`));

            const savedFileId = window.dbConfig?.[site]?.file_id;
            if (savedFileId && res.some(x => String(x.id) === String(savedFileId))) {
                $file.val(savedFileId).trigger('change');
            }
        }, 'json');
    });


    $(document).on('change', '.file', function () {
        const site = $(this).data('site');
        const fileId = $(this).val();
        const $hdr = $(`.headers[data-site="${site}"]`);

        // 🔥 UPDATE STATE
        window.dbConfig[site] = window.dbConfig[site] || {};
        window.dbConfig[site].file_id = fileId;

        $hdr.prop('disabled', true).html('<option value="">Select</option>');
        if (!fileId) return;

        $.post(`${HOST_URL}api/get_headers.php`, { file_id: fileId }, function (res) {
            $hdr.prop('disabled', false).html('<option value="">Select</option>');
            (res.headers || []).forEach(h =>
                $hdr.append(`<option value="${h.header_name}">${h.header_name}</option>`)
            );

            const savedHeader = window.dbConfig?.[site]?.header_name;
            if (savedHeader) $hdr.val(savedHeader).trigger('change');
        }, 'json');
    });


    $(document).on('change', '.headers', function () {
        const site = $(this).data('site');
        const header = $(this).val();

        // 🔥 UPDATE STATE
        window.dbConfig[site] = window.dbConfig[site] || {};
        window.dbConfig[site].header_name = header;

        saveSiteSettings(site);
        if (header) loadHistogramChart(site, false, true);
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
        // if (site === 'main') site = window.currentMainSite;

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


    $(document).on('click', '[id^="btnMainAlert_"]', function () {
        const slot = $(this).data('slot');
        const site = window.mainSlots[slot]?.site;
        if (site) showManualAlert(site);
    });


    // Event: Info icon di viewer utama (sama seperti mini chart)
    $(document).on('click', '#mainInfoIcon', function () {
        const slot = $(this).data('slot');
        const site = window.mainSlots[slot]?.site;

        if (!site) {
            Swal.fire('ℹ️', 'Belum ada site aktif di carousel.', 'info');
            return;
        }
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

    // -------------------------
    // 8. Initialization
    // -------------------------


    // Init Polling Global
    (function initGlobalScheduler() {
        const defaultInterval =
            parseInt($('#globalIntervalSelect').val()) || 120000; // 2 menit
        // startGlobalScheduler(defaultInterval);
    })();

    (function initPageLoadSafe() {
        Object.keys(window.dbConfig || {}).forEach(site => {
            const cfg = window.dbConfig[site];
            if (cfg?.line_id) {
                $(`.line[data-site="${site}"]`)
                    .val(cfg.line_id)
                    .trigger('change');
            }
        });

        // restore limit inputs
        $('.site-setting-row').each(function () {
            const site = $(this).attr('data-site');
            const cfg = window.dbConfig?.[site];
            if (!cfg) return;

            $(this).find('input[data-type="lcl"]').val(cfg.custom_lcl);
            $(this).find('input[data-type="ucl"]').val(cfg.custom_ucl);
            $(this).find('input[data-type="lower"]').val(cfg.lower_boundary);
            $(this).find('input[data-type="interval"]').val(cfg.interval_width);
            $(this).find('input[data-type="cp_limit"]').val(cfg.cp_limit);
            $(this).find('input[data-type="cpk_limit"]').val(cfg.cpk_limit);
        });
        setTimeout(() => {
            window.isInitializingPage = false;
            console.log('[INIT] selesai, save API aktif kembali');
        }, 10000);
    })();


    $(document).on('click', '[id$="AlertIcon"]', function () {
        let site = $(this).attr('id').replace("AlertIcon", "");
        if (!site) return;
        // if (site === 'main') site = window.currentMainSite;
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


        // 🔥 SAVE KE DB
        saveSiteSettings(site);
    });
    $(document).on('click', '.pause-slot-btn', function () {
        const slot = $(this).data('slot');

        window.pausedSlots[slot] = !window.pausedSlots[slot];

        // Update icon
        $(this).toggleClass('btn-danger btn-light');

        if (window.pausedSlots[slot]) {
            $(this).text('▶'); // resume icon
            console.log(`Slot ${slot} paused`);
        } else {
            $(this).text('⏸');
            console.log(`Slot ${slot} resumed`);
        }
    });

    $('#pauseAllCarouselBtn').on('click', function () {
        window.carouselPausedAll = !window.carouselPausedAll;

        if (window.carouselPausedAll) {
            $(this).text('▶ Resume All').removeClass('btn-warning').addClass('btn-success');
            console.log('[CAROUSEL] Paused ALL');
        } else {
            $(this).text('⏸ Pause All').removeClass('btn-success').addClass('btn-warning');
            console.log('[CAROUSEL] Resumed ALL');
        }
    });

    $(window).on('beforeunload unload', function () {
        // stopGlobalScheduler();
        if (carouselIntervalId) clearInterval(carouselIntervalId);
    });

    carouselIntervalId = setInterval(runMainCarousel, 8000);

    primeCarouselSites();

    setTimeout(() => {
        runMainCarousel();
    }, 500);

    function primeCarouselSites() {
        window.mainSiteGroups = buildMainSiteGroups();

        window.mainSiteGroups.forEach(group => {
            if (!group || !group.length) return;

            group.forEach(site => {
                if (!window.cachedChartData[site]) {
                    enqueueChartRequest(site, false);
                }
            });
        });
    }

    function staggerRefreshAllSites(intervalMs = 180000) {

        function runCycle() {
            const sites = getAllSites();
            let index = 0;

            function processNext() {
                if (index >= sites.length) {
                    console.log('[REFRESH] cycle done');
                    setTimeout(runCycle, intervalMs);
                    return;
                }

                const site = sites[index];

                enqueueChartRequest(site, false);

                index++;

                // 🔥 DELAY ANTAR SITE (KUNCI ANTI BANJIR)
                setTimeout(processNext, 400);
            }

            processNext();
        }

        runCycle();
    }


    setTimeout(() => {
        staggerRefreshAllSites();
    }, 15000);
});