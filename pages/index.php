<?php
require_once __DIR__ . '/../includes/config.php';

$stmt = $pdo->query("SELECT line_id AS id, line_name AS name FROM tbl_line ORDER BY name ASC");
$lines = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Monitoring Dashboard</title>

  <style>
    /* === TIDAK DIUBAH === */
    html,
    body {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      background: #000;
      overflow: hidden;
      font-family: Arial
    }

    iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: 0;
      opacity: 0;
      pointer-events: none;
      transition: opacity 1.2s
    }

    iframe.active {
      opacity: 1;
      pointer-events: auto;
      z-index: 1
    }

    .menu-toggle {
      position: fixed;
      top: 12px;
      right: 12px;
      z-index: 10000;
      background: rgba(0, 0, 0, .75);
      color: #fff;
      border: 1px solid #444;
      width: 34px;
      height: 34px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer
    }

    .page-indicator {
      position: fixed;
      top: 52px;
      right: 12px;
      z-index: 9998;
      background: rgba(0, 0, 0, .6);
      color: #fff;
      border-radius: 6px;
      padding: 4px 10px;
      font-size: 11px
    }

    .control-panel {
      position: fixed;
      top: 82px;
      right: 12px;
      z-index: 9999;
      background: rgba(0, 0, 0, .75);
      border-radius: 8px;
      padding: 10px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      min-width: 180px;
      max-height: 80vh;
      overflow: auto;
      transform: translateY(-10px);
      opacity: 0;
      pointer-events: none;
      transition: .25s
    }

    .control-panel.show {
      transform: translateY(0);
      opacity: 1;
      pointer-events: auto
    }

    .control-panel button {
      background: #222;
      color: #fff;
      border: 1px solid #444;
      padding: 6px 10px;
      font-size: 12px;
      border-radius: 5px;
      cursor: pointer;
      text-align: left
    }

    .control-panel button.active {
      background: #007bff;
      border-color: #007bff
    }

    /* ===== PANEL LEBIH LEBAR ===== */
    .control-panel {
      min-width: 280px;
      max-width: 320px;
    }

    /* ===== FIX Z-INDEX (INI KUNCI UTAMA) ===== */
    .control-panel {
      z-index: 999999 !important;
    }

    /* ===== MODERN LINE SELECT ===== */
    .line-list {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      max-height: 500px;
      /* overflow-y: auto; */
      padding: 6px;
      border-radius: 6px;
      background: #111;
      border: 1px solid #333;
    }

    /* ===== HIDE CHECKBOX ASLI ===== */
    .line-list input {
      display: none;
    }

    /* ===== CHIP STYLE ===== */
    .line-chip {
      padding: 6px 10px;
      background: #222;
      border: 1px solid #444;
      border-radius: 20px;
      font-size: 12px;
      color: #aaa;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    /* ===== SELECTED ===== */
    .line-list input:checked+.line-chip {
      background: #007bff;
      border-color: #007bff;
      color: #fff;
    }

    /* ===== HOVER ===== */
    .line-chip:hover {
      background: #333;
    }

    /* ===== SCROLL ===== */
    .line-list::-webkit-scrollbar {
      width: 5px;
    }

    .line-list::-webkit-scrollbar-thumb {
      background: #555;
      border-radius: 3px;
    }
  </style>
</head>

<body>

  <div class="menu-toggle" id="menuToggle">⋮</div>

  <div class="page-indicator" id="pageIndicator"></div>

  <div class="control-panel" id="controlPanel">

    <button id="btnAuto" class="active">🔄 Auto (Mix)</button>

    <hr>

    <button data-filter="all" class="active">ALL</button>
    <button data-filter="ok">OK</button>
    <button data-filter="ng">NG</button>

    <hr>

    <div style="color:#fff;font-size:12px;">Filter Line:</div>
    <div class="line-list">
      <?php foreach ($lines as $l): ?>
        <label>
          <input type="checkbox" class="lineCheck" value="<?= htmlspecialchars($l['name']) ?>" checked>
          <span class="line-chip"><?= htmlspecialchars($l['name']) ?></span>
        </label>
      <?php endforeach; ?>
    </div>
    <button id="applyLineBtn">Apply Line</button>

    <hr>

    <button id="btnMain">📈 Dashboard</button>
    <button id="btnSub">📊 MinMax</button>
    <button id="btnBack">↩ Kembali</button>

    <hr>

    <!-- 🔥 SPEED BALIK -->
    <button id="speedFast">⚡ Fast (30s)</button>
    <button id="speedNormal" class="active">⏱ Normal (5m)</button>
    <button id="speedSlow">🐢 Slow (10m)</button>

    <hr>

    <button id="btnPause">⏸ Pause</button>
    <button id="btnResume">▶ Resume</button>

  </div>

  <iframe id="main" src="dashboard.php" class="active"></iframe>
  <iframe id="sub" src="dashboard2.php"></iframe>

  <script>
    const DASHBOARDS = ['main', 'sub'];

    const SPEEDS = {
      fast: 30000,
      normal: 300000,
      slow: 600000
    };

    let currentIndex = 0;
    let currentFilter = 'all';
    let currentPage = 'main';
    let currentMode = 'auto';
    let currentSpeed = 'normal';
    let timer = null;
    let isPaused = false;

    // ================= SEND =================
    function send(data) {
      document.getElementById('main').contentWindow.postMessage(data, '*');
      document.getElementById('sub').contentWindow.postMessage(data, '*');
    }

    // ================= FILTER =================
    document.querySelectorAll('[data-filter]').forEach(btn => {
      btn.onclick = () => {
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.dataset.filter;

        send({
          type: 'SET_FILTER',
          mode: currentFilter
        });
        updateIndicator();
      };
    });

    // ================= LINE =================
    document.getElementById('applyLineBtn').onclick = () => {
      const lines = [...document.querySelectorAll('.lineCheck:checked')].map(x => x.value);
      send({
        type: 'SET_LINE_FILTER',
        lines
      });
    };

    // ================= SWITCH =================
    function switchFrame(id) {
      DASHBOARDS.forEach(d => document.getElementById(d).classList.remove('active'));
      document.getElementById(id).classList.add('active');
      currentPage = id;
      currentMode = 'manual';
      updateIndicator();
    }

    document.getElementById('btnMain').onclick = () => switchFrame('main');
    document.getElementById('btnSub').onclick = () => switchFrame('sub');

    // ================= SPEED =================
    function setSpeed(mode) {
      currentSpeed = mode;
      document.querySelectorAll('#speedFast,#speedNormal,#speedSlow')
        .forEach(b => b.classList.remove('active'));

      document.getElementById(
        mode === 'fast' ? 'speedFast' : mode === 'slow' ? 'speedSlow' : 'speedNormal'
      ).classList.add('active');

      schedule();
      updateIndicator();
    }

    document.getElementById('speedFast').onclick = () => setSpeed('fast');
    document.getElementById('speedNormal').onclick = () => setSpeed('normal');
    document.getElementById('speedSlow').onclick = () => setSpeed('slow');

    // ================= AUTO =================
    document.getElementById('btnAuto').onclick = () => {
      currentMode = 'auto';
      isPaused = false;
      schedule();
      updateIndicator();
    };

    function schedule() {
      clearTimeout(timer);
      if (isPaused || currentMode !== 'auto') return;

      timer = setTimeout(() => {
        currentIndex = (currentIndex + 1) % DASHBOARDS.length;
        switchFrame(DASHBOARDS[currentIndex]);
        schedule();
      }, SPEEDS[currentSpeed]);
    }

    // ================= PAUSE =================
    document.getElementById('btnPause').onclick = () => {
      isPaused = true;
      clearTimeout(timer);
      updateIndicator();
    };

    document.getElementById('btnResume').onclick = () => {
      isPaused = false;
      schedule();
      updateIndicator();
    };

    // ================= MENU =================
    document.getElementById('menuToggle').onclick = () => {
      document.getElementById('controlPanel').classList.toggle('show');
    };

    // ================= INDICATOR =================
    function updateIndicator() {
      const page = currentPage === 'main' ? 'Dashboard' : 'MinMax';
      const mode = isPaused ? 'Paused' : (currentMode === 'auto' ? 'Auto' : 'Manual');

      document.getElementById('pageIndicator').textContent =
        `Mode: ${mode} • ${currentSpeed.toUpperCase()} • ${currentFilter.toUpperCase()} • ${page}`;
    }

    // ================= INIT =================
    updateIndicator();
    schedule();
  </script>

</body>

</html>