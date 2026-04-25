<?php
// redis_dashboard.php
header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Redis Queue Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #121212;
            color: #eee;
            text-align: center;
            padding: 20px;
        }

        h1 {
            color: #00e676;
            margin-bottom: 10px;
        }

        .card {
            display: inline-block;
            background: #1e1e1e;
            padding: 20px;
            border-radius: 12px;
            margin: 10px;
            width: 250px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: scale(1.05);
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            background: #ff5252;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #ff1744;
        }

        .refresh {
            background: #00e676;
        }

        .refresh:hover {
            background: #00c853;
        }

        .footer {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h1>🚀 Redis Queue Dashboard</h1>

    <div class="card">
        <h2>📦 Queue Length</h2>
        <h3 id="queueLength">0</h3>
    </div>

    <div class="card">
        <h2>✅ Processed</h2>
        <h3 id="processedCount">0</h3>
    </div>

    <div class="card">
        <h2>❌ Failed</h2>
        <h3 id="failedCount">0</h3>
    </div>

    <div style="margin-top: 30px;">
        <button class="refresh" onclick="updateStats()">🔄 Refresh</button>
        <button onclick="if(confirm('Hapus semua job dalam queue?')) clearQueue()">🧹 Clear Queue</button>
    </div>

    <div class="footer">
        Realtime update setiap 2 detik &bull; Made by Naufal 🚀
    </div>

    <script>
        async function updateStats() {
            try {
                const res = await fetch('redis_dashboard_api.php');
                const data = await res.json();
                document.getElementById('queueLength').innerText = data.queueLength;
                document.getElementById('processedCount').innerText = data.processedCount;
                document.getElementById('failedCount').innerText = data.failedCount;
            } catch (e) {
                console.error('Gagal mengambil data Redis:', e);
            }
        }

        // Auto refresh tiap 2 detik
        setInterval(updateStats, 2000);

        // Jalankan pertama kali
        updateStats();

        async function clearQueue() {
            await fetch('redis_dashboard_api.php?clear=1');
            updateStats();
        }
    </script>
</body>

</html>