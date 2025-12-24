@echo off
setlocal ENABLEDELAYEDEXPANSION

:: ==================================================
:: KONFIGURASI
:: ==================================================
set APP_NAME=ClientUploader2.exe
set UPDATER_NAME=Updater.exe
set ENV_NAME=.env

:: lokasi source (hasil extract zip)
set SRC_DIR=%~dp0
set SRC_APP=%SRC_DIR%%APP_NAME%
set SRC_UPDATER=%SRC_DIR%%UPDATER_NAME%
set SRC_ENV=%SRC_DIR%%ENV_NAME%

:: lokasi target (FINAL)
set TARGET_DIR=C:\RPA\ClientUploader2
set TARGET_APP=%TARGET_DIR%\%APP_NAME%
set TARGET_UPDATER=%TARGET_DIR%\%UPDATER_NAME%
set TARGET_ENV=%TARGET_DIR%\%ENV_NAME%

:: shortcut startup
set STARTUP_DIR=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup
set STARTUP_LNK=%STARTUP_DIR%\ClientUploader2.lnk

:: lokasi lama
set OLD_DIR=D:\RPA\ClientUploader2

echo ==========================================
echo [SETUP] ClientUploader2 Installer (FINAL)
echo ==========================================

:: ==================================================
:: VALIDASI FILE
:: ==================================================
if not exist "%SRC_APP%" (
    echo [ERROR] %APP_NAME% tidak ditemukan
    pause
    exit /b
)

if not exist "%SRC_UPDATER%" (
    echo [ERROR] %UPDATER_NAME% tidak ditemukan
    pause
    exit /b
)

:: ==================================================
:: STOP APLIKASI JIKA MASIH JALAN
:: ==================================================
tasklist | find /I "%APP_NAME%" >nul
if %errorlevel%==0 (
    echo [INFO] Menghentikan ClientUploader2...
    taskkill /F /IM "%APP_NAME%" >nul 2>&1
    timeout /t 2 >nul
)

tasklist | find /I "%UPDATER_NAME%" >nul
if %errorlevel%==0 (
    echo [INFO] Menghentikan Updater...
    taskkill /F /IM "%UPDATER_NAME%" >nul 2>&1
    timeout /t 2 >nul
)

:: ==================================================
:: HAPUS INSTALL LAMA (D:)
:: ==================================================
if exist "%OLD_DIR%" (
    echo [INFO] Menghapus instalasi lama di D:
    rmdir /S /Q "%OLD_DIR%"
)

:: ==================================================
:: BUAT FOLDER TARGET
:: ==================================================
if not exist "%TARGET_DIR%" (
    echo [INFO] Membuat folder %TARGET_DIR%
    mkdir "%TARGET_DIR%"
)

:: ==================================================
:: COPY FILE
:: ==================================================
echo [INFO] Menyalin ClientUploader2.exe
copy /Y "%SRC_APP%" "%TARGET_APP%" >nul

echo [INFO] Menyalin Updater.exe
copy /Y "%SRC_UPDATER%" "%TARGET_UPDATER%" >nul

:: ==================================================
:: COPY .env (TIDAK OVERWRITE)
:: ==================================================
if exist "%SRC_ENV%" (
    if not exist "%TARGET_ENV%" (
        echo [INFO] Menyalin file .env
        copy "%SRC_ENV%" "%TARGET_ENV%" >nul
    ) else (
        echo [INFO] File .env sudah ada, tidak ditimpa
    )
)

:: ==================================================
:: HAPUS SHORTCUT LAMA
:: ==================================================
if exist "%STARTUP_LNK%" del "%STARTUP_LNK%" >nul 2>&1

:: ==================================================
:: BUAT SHORTCUT (DINAMIS & AMAN)
:: ==================================================
echo [INFO] Membuat shortcut Startup + Desktop

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"$ws = New-Object -ComObject WScript.Shell; ^
$desktop = [Environment]::GetFolderPath('Desktop'); ^
$s = $ws.CreateShortcut('%STARTUP_LNK%'); ^
$s.TargetPath = '%TARGET_UPDATER%'; ^
$s.WorkingDirectory = '%TARGET_DIR%'; ^
$s.Save(); ^
if ($desktop -and (Test-Path $desktop)) { ^
    $s2 = $ws.CreateShortcut(\"$desktop\ClientUploader2.lnk\"); ^
    $s2.TargetPath = '%TARGET_APP%'; ^
    $s2.WorkingDirectory = '%TARGET_DIR%'; ^
    $s2.Save(); ^
}"

:: ==================================================
:: JALANKAN UPDATER
:: ==================================================
echo [INFO] Menjalankan Updater...
start "" "%TARGET_UPDATER%"

echo.
echo ==========================================
echo [SUCCESS]
echo - Install path : %TARGET_DIR%
echo - Startup      : Updater.exe
echo - Desktop      : ClientUploader2.exe
echo - .env         : AMAN
echo - Install lama : D: DIBERSIHKAN
echo ==========================================
pause
endlocal
