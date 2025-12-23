@echo off
setlocal ENABLEDELAYEDEXPANSION

:: ==================================================
:: KONFIGURASI
:: ==================================================
set APP_NAME=ClientUploader2.exe
set UPDATER_NAME=Updater.exe

set TARGET_DIR=C:\RPA\ClientUploader2

set STARTUP_DIR=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup
set DESKTOP_DIR=%USERPROFILE%\Desktop

set STARTUP_LNK=%STARTUP_DIR%\ClientUploader2.lnk
set DESKTOP_LNK=%DESKTOP_DIR%\ClientUploader2.lnk

echo ==========================================
echo [UNINSTALL] ClientUploader2
echo ==========================================

:: ==================================================
:: STOP PROSES JIKA MASIH JALAN
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
:: HAPUS SHORTCUT
:: ==================================================
if exist "%STARTUP_LNK%" (
    echo [INFO] Menghapus shortcut Startup
    del "%STARTUP_LNK%" >nul 2>&1
)

if exist "%DESKTOP_LNK%" (
    echo [INFO] Menghapus shortcut Desktop
    del "%DESKTOP_LNK%" >nul 2>&1
)

:: ==================================================
:: HAPUS FOLDER APLIKASI (C:)
:: ==================================================
if exist "%TARGET_DIR%" (
    echo [INFO] Menghapus folder aplikasi di C:
    rmdir /S /Q "%TARGET_DIR%"
) else (
    echo [INFO] Folder aplikasi tidak ditemukan
)

echo.
echo ==========================================
echo [SUCCESS]
echo - Aplikasi       : DIHAPUS
echo - Shortcut       : DIHAPUS
echo - Lokasi C:      : DIBERSIHKAN
echo ==========================================
pause
endlocal
