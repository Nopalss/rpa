<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download MyApp</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f4f4f9;
            font-family: Arial, sans-serif;
        }

        .download-container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 450px;
        }

        .download-btn {
            display: inline-block;
            padding: 15px 30px;
            background: #007bff;
            color: white;
            text-decoration: none;
            font-size: 18px;
            border-radius: 8px;
            transition: background 0.3s ease;
            margin-top: 10px;
        }

        .download-btn:hover {
            background: #0056b3;
        }

        .steps {
            text-align: left;
            margin-top: 25px;
            font-size: 15px;
            color: #333;
        }

        .steps ol {
            padding-left: 20px;
        }

        .steps li {
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="download-container">
        <h2>Download RPA Client Installer</h2>
        <p>Klik tombol di bawah untuk mengunduh aplikasi.</p>
        <a href="download.php" class="download-btn">Download Sekarang</a>

        <div class="steps">
            <h3>Langkah-langkah Instalasi:</h3>
            <ol>
                <li>Setelah file selesai diunduh, ekstrak file <b>.zip</b> terlebih dahulu.</li>
                <li>Buka folder hasil ekstrak tersebut.</li>
                <li> Klik dua kali pada file <b>setup.bat</b> untuk menjalankan proses awal.</li>
                <li>Setelah selesai, ketika aplikasi tidak berjalan, jalankan aplikasi dengan klik dua kali file <b>ClientUploader2.exe</b> pada desktop</li>
            </ol>
        </div>
    </div>
</body>

</html>