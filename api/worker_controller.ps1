# =========================================
# RPA Hybrid Worker Controller (24/7 Stable)
# =========================================

$PhpPath    = "D:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe"
$WorkerFile = "D:\laragon\www\rpa\api\worker.php"
$StatusFile = "D:\laragon\www\rpa\api\worker.status"
$LogFile    = "D:\laragon\www\rpa\api\worker_controller.log"

# Idle limit (detik)
$IdleLimit = 60

# -------------------------------
function Log($msg) {
    $time = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Add-Content -Path $LogFile -Value "[$time] $msg"
}

# -------------------------------
Log "========================================="
Log "Hybrid worker controller started"
Log "========================================="

function Start-Worker {
    Log "Starting worker"
    Start-Process `
        -FilePath $PhpPath `
        -ArgumentList "`"$WorkerFile`"" `
        -WindowStyle Hidden
}

function Stop-Worker {
    Log "Stopping worker"
    Get-Process php -ErrorAction SilentlyContinue |
        Where-Object { $_.Path -eq $PhpPath } |
        Stop-Process -Force
}

# -------------------------------
# Pastikan tidak ada worker nyangkut
Stop-Worker
Start-Worker

# -------------------------------
# LOOP UTAMA (ANTI MATI)
while ($true) {
    try {
        Start-Sleep -Seconds 10

        if (!(Test-Path $StatusFile)) {
            Log "Status file not found, waiting..."
            continue
        }

        $last = (Get-Item $StatusFile).LastWriteTime
        $idle = (New-TimeSpan -Start $last -End (Get-Date)).TotalSeconds

        if ($idle -gt $IdleLimit) {
            Log "Worker idle $idle sec > $IdleLimit sec, restarting..."

            Stop-Worker
            Start-Sleep -Seconds 5
            Start-Worker
        }
    }
    catch {
        # Jangan sampai PowerShell mati karena error apa pun
        Log "Controller error: $($_.Exception.Message)"
        Start-Sleep -Seconds 5
    }
}
