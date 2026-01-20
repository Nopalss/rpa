@echo off
title SPC MINMAX WORKER
setlocal enabledelayedexpansion

REM ============================
REM CONFIG
REM ============================
set PHP_PATH=D:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe
set WORKER_PATH=D:\laragon\www\rpa\api\minmax\worker_minmax.php
set LOG_DIR=D:\laragon\www\rpa\storage\logs
set LOG_FILE=%LOG_DIR%\minmax_worker.log

REM ============================
REM VALIDATION
REM ============================
if not exist "%PHP_PATH%" (
    echo [ERROR] PHP not found: %PHP_PATH%
    pause
    exit /b 1
)

if not exist "%WORKER_PATH%" (
    echo [ERROR] Worker not found: %WORKER_PATH%
    pause
    exit /b 1
)

if not exist "%LOG_DIR%" (
    mkdir "%LOG_DIR%"
)

echo ============================================
echo SPC MINMAX WORKER
echo PHP    : %PHP_PATH%
echo WORKER : %WORKER_PATH%
echo LOG    : %LOG_FILE%
echo ============================================

REM ============================
REM MAIN LOOP (ANTI MATI)
REM ============================
:LOOP
echo [%DATE% %TIME%] Worker starting... >> "%LOG_FILE%"

"%PHP_PATH%" "%WORKER_PATH%" >> "%LOG_FILE%" 2>&1

echo [%DATE% %TIME%] Worker stopped. Restarting in 3 seconds... >> "%LOG_FILE%"
timeout /t 3 >nul
goto LOOP
