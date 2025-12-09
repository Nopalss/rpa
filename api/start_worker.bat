@echo off
title Redis Worker - RPA
cd /d D:\laragon\www\rpa\api
echo [INFO] Starting Redis worker...
"D:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" worker.php
pause

