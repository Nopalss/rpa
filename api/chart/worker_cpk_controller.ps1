# =========================================
# CPK WORKER CONTROLLER (FINAL SAFE)
# =========================================

$PhpPath     = "D:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe"
$WorkerFile  = "D:\laragon\www\rpa\api\chart\worker_cpk.php"
$StatusFile  = "D:\laragon\www\rpa\api\chart\worker_cpk.status"
$LogFile     = "D:\laragon\www\rpa\api\chart\worker_cpk_controller.log"
$PidFile     = "D:\laragon\www\rpa\api\chart\worker_cpk.pid"

# 🔥 NAIKKAN IDLE LIMIT (5 MENIT)
$IdleLimit = 300

function Log($msg) {
    $time = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Add-Content -Path $LogFile -Value "[$time] $msg"
}

function Start-Worker {
    Log "START WORKER"

    $proc = Start-Process `
        -FilePath $PhpPath `
        -ArgumentList "`"$WorkerFile`"" `
        -WindowStyle Hidden `
        -PassThru

    $proc.Id | Out-File $PidFile -Force
    Log "WORKER STARTED PID=$($proc.Id)"
}

function Stop-Worker {
    if (Test-Path $PidFile) {
        $workerPid = Get-Content $PidFile
        if ($workerPid -and (Get-Process -Id $workerPid -ErrorAction SilentlyContinue)) {
            Log "STOP WORKER PID=$workerPid"
            Stop-Process -Id $workerPid -Force
        }
        Remove-Item $PidFile -Force
    }
}

Log "========================================="
Log "CPK WORKER CONTROLLER STARTED"
Log "========================================="

Stop-Worker
Start-Sleep 2
Start-Worker

while ($true) {
    try {
        Start-Sleep 10

        if (!(Test-Path $StatusFile)) {
            Log "WAITING STATUS FILE..."
            continue
        }

        $last = (Get-Item $StatusFile).LastWriteTime
        $idle = (New-TimeSpan -Start $last -End (Get-Date)).TotalSeconds

        if ($idle -gt $IdleLimit) {
            Log "WORKER IDLE $idle sec > $IdleLimit sec → RESTART"
            Stop-Worker
            Start-Sleep 3
            Start-Worker
        }
    }
    catch {
        Log "CONTROLLER ERROR: $($_.Exception.Message)"
        Start-Sleep 5
    }
}
