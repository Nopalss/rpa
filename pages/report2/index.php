<?php
require_once __DIR__ . '/../../includes/config.php';
require __DIR__ . '/../../includes/header.php';

$user_id = $_SESSION['user_id'] ?? 0;
$_SESSION['halaman'] = 'report';
$_SESSION['menu'] = 'report';

$stmt = $pdo->prepare("
SELECT id, site_name, site_label 
FROM tbl_spc_model_settings
WHERE user_id = ?
");
$stmt->execute([$user_id]);
$sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../../includes/aside.php';
require __DIR__ . '/../../includes/navbar.php';
?>

<style>
    /* ===== MODERN UI ===== */
    .card-modern {
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border: none;
    }

    .stat-box {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 12px;
        text-align: center;
    }

    .stat-title {
        font-size: 12px;
        color: #888;
    }

    .stat-value {
        font-size: 18px;
        font-weight: bold;
    }

    .btn-modern {
        border-radius: 10px;
        padding: 8px 16px;
    }
</style>

<div class="container mt-4 mb-5">

    <!-- HEADER -->
    <div class="card card-modern p-4 mb-4">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label>Site</label>
                <select id="siteSelect" class="form-control">
                    <option value="">Select Site</option>
                    <?php foreach ($sites as $s): ?>
                        <option value="<?= $s['site_name'] ?>">
                            <?= $s['site_label'] ?: $s['site_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Date</label>
                <input type="date" id="dateSelect" class="form-control">
            </div>

            <div class="col-md-6 text-end">
                <button id="btnLoad" class="btn btn-primary btn-modern">
                    Load Data
                </button>
                <button id="btnDownload" class="btn btn-success btn-modern">
                    Download Excel
                </button>
            </div>
        </div>
    </div>

    <!-- CHART -->
    <div class="card card-modern p-4 mb-4">
        <h5 class="mb-3">Histogram Analysis</h5>
        <div id="chart" style="height:350px;"></div>
    </div>

    <!-- SUMMARY -->
    <div class="card card-modern p-4">
        <h5 class="mb-3">Summary</h5>

        <div class="row" id="summary">
            <!-- dynamic -->
        </div>
    </div>

</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

<script>
    let currentData = null;

    window.apexChartsInstances = {};
    window.renderingChart = {};

    // ===============================
    // HISTOGRAM (TETAP SAMA)
    // ===============================
    function renderApexHistogram(chartSelector, data, key = 'report_chart') {

        const bars = data.series_data.map(d => ({
            x: d[0],
            y: d[1]
        }));
        const curve = data.normal_curve.map(d => ({
            x: d[0],
            y: d[1]
        }));

        const midpoints = bars.map(d => d.x);
        const step = midpoints[1] - midpoints[0];

        function align(v) {
            return midpoints.reduce((a, b) => Math.abs(b - v) < Math.abs(a - v) ? b : a);
        }

        const options = {
            chart: {
                type: 'line',
                height: 350
            },
            series: [{
                    name: 'Observed',
                    type: 'column',
                    data: bars
                },
                {
                    name: 'Predicted',
                    type: 'line',
                    data: curve
                }
            ],
            colors: ['#1E88E5', '#FF0000'],
            stroke: {
                width: [0, 3],
                curve: 'smooth'
            },
            plotOptions: {
                bar: {
                    columnWidth: '100%'
                }
            },

            xaxis: {
                type: 'numeric',
                min: midpoints[0],
                max: midpoints[midpoints.length - 1],
                tickAmount: midpoints.length - 1,
                labels: {
                    rotate: -45,
                    formatter: (v) => {
                        const i = Math.round((v - midpoints[0]) / step);
                        return midpoints[i]?.toFixed(2) || '';
                    }
                }
            },

            yaxis: {
                min: data.y_axis_min,
                max: data.y_axis_max,
                labels: {
                    formatter: v => Math.round(v)
                }
            },

            annotations: {
                xaxis: [{
                        x: align(data.lsl),
                        borderColor: '#FF0000',
                        label: {
                            text: 'LSL'
                        }
                    },
                    {
                        x: align(data.usl),
                        borderColor: '#FF0000',
                        label: {
                            text: 'USL'
                        }
                    }
                ]
            }
        };

        const el = document.querySelector(chartSelector);
        if (window.apexChartsInstances[key]) {
            window.apexChartsInstances[key].destroy();
        }

        const chart = new ApexCharts(el, options);
        chart.render();
        window.apexChartsInstances[key] = chart;
    }

    // ===============================
    // SUMMARY MODERN
    // ===============================
    function renderSummary(data, cfg = {}) {

        const isOk =
            data.cp_status === "OK" &&
            data.cpk_status === "OK";

        const siteLabel = cfg.site_label || data.site_name || '-';

        const info = {
            site: siteLabel,
            line: cfg.line_name || '-',
            app: cfg.application_name || '-',
            file: cfg.file_name || '-',

            quantity: data.debug_total_data ?? '-',
            min: data.min_val ?? '-',
            max: data.max_val ?? '-',
            avg: data.rata_rata ?? '-',
            std: data.standar_deviasi ?? '-',

            cp: data.cp ? data.cp.toFixed(3) : '-',
            cpk: data.cpk ? data.cpk.toFixed(3) : '-',
            cp_status: data.cp_status ?? '-',
            cpk_status: data.cpk_status ?? '-',

            cp_limit: data.std_limit_cp ?? '-',
            cpk_limit: data.std_limit_cpk ?? '-',

            ng_estimation: data.estimated_defect_rate != null ?
                parseFloat(data.estimated_defect_rate).toFixed(5) : '-',

            ng_actual: data.out_of_control_percent != null ?
                data.out_of_control_percent.toFixed(3) : '-'
        };

        $('#summary').html(`
    
    <!-- STATUS BAR -->
    <div class="col-12 mb-3">
        <div class="p-3 rounded text-white ${isOk ? 'bg-success' : 'bg-danger'}">
            <b>Status:</b> ${isOk ? 'OK ✅' : 'NG ❌'}
        </div>
    </div>

    <!-- IDENTITAS -->
    <div class="col-md-3">
        <div class="stat-box">
            <div class="stat-title">Site</div>
            <div class="stat-value">${info.site}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <div class="stat-title">Line</div>
            <div class="stat-value">${info.line}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <div class="stat-title">Application</div>
            <div class="stat-value">${info.app}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-box">
            <div class="stat-title">File</div>
            <div class="stat-value">${info.file}</div>
        </div>
    </div>

   

    <!-- STATISTIK -->
    <div class="col-md-2 mt-3">
        <div class="stat-box">
            <div class="stat-title">Qty</div>
            <div class="stat-value">${info.quantity}</div>
        </div>
    </div>

    <div class="col-md-2 mt-3">
        <div class="stat-box">
            <div class="stat-title">Min</div>
            <div class="stat-value">${info.min}</div>
        </div>
    </div>

    <div class="col-md-2 mt-3">
        <div class="stat-box">
            <div class="stat-title">Max</div>
            <div class="stat-value">${info.max}</div>
        </div>
    </div>

    <div class="col-md-2 mt-3">
        <div class="stat-box">
            <div class="stat-title">Avg</div>
            <div class="stat-value">${parseFloat(info.avg).toFixed(3)}</div>
        </div>
    </div>

    <div class="col-md-2 mt-3">
        <div class="stat-box">
            <div class="stat-title">Std Dev</div>
            <div class="stat-value">${parseFloat(info.std).toFixed(3)}</div>
        </div>
    </div>

    <!-- CP / CPK -->
    <div class="col-md-3 mt-3">
        <div class="stat-box">
            <div class="stat-title">CP</div>
            <div class="stat-value">
                ${info.cp} 
                <br>
                <small class="${info.cp_status==='OK'?'text-success':'text-danger'}">
                    ${info.cp_status}
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mt-3">
        <div class="stat-box">
            <div class="stat-title">CP Limit</div>
            <div class="stat-value">${info.cp_limit}</div>
        </div>
    </div>

    <div class="col-md-3 mt-3">
        <div class="stat-box">
            <div class="stat-title">CPK</div>
            <div class="stat-value">
                ${info.cpk}
                <br>
                <small class="${info.cpk_status==='OK'?'text-success':'text-danger'}">
                    ${info.cpk_status}
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mt-3">
        <div class="stat-box">
            <div class="stat-title">CPK Limit</div>
            <div class="stat-value">${info.cpk_limit}</div>
        </div>
    </div>

    <!-- NG -->
    <div class="col-md-6 mt-3">
        <div class="stat-box">
            <div class="stat-title">NG Estimation</div>
            <div class="stat-value">${info.ng_estimation}%</div>
        </div>
    </div>

    <div class="col-md-6 mt-3">
        <div class="stat-box">
            <div class="stat-title">NG Actual</div>
            <div class="stat-value">${info.ng_actual}%</div>
        </div>
    </div>

    `);
    }

    // ===============================
    // LOAD
    // ===============================
    $('#btnLoad').click(async function() {

        const site = $('#siteSelect').val();
        const date = $('#dateSelect').val();

        if (!site || !date) {
            alert('Isi dulu');
            return;
        }

        const cfg = await $.getJSON(`get_site_config.php?site=${site}`);

        const res = await $.post(`chart_data_3sigma_minmax.php`, {
            ...cfg,
            production_date: date
        });

        if (!res.success) {
            alert(res.message);
            return;
        }

        currentData = res;

        renderApexHistogram('#chart', res);

        renderSummary(res, cfg); // ✅ kirim cfg
    });

    // ===============================
    // DOWNLOAD
    // ===============================
    $('#btnDownload').click(function() {
        if (!currentData) {
            alert('Load dulu');
            return;
        }

        const f = $('<form method="POST" action="export_excel_spc.php"></form>');
        f.append(`<input type="hidden" name="data" value='${JSON.stringify(currentData)}'>`);
        $('body').append(f);
        f.submit();
    });
</script>