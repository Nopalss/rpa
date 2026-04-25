@echo off
title RPA Redis Worker Controller

cd /d D:\laragon\www\rpa\api

powershell -NoProfile -ExecutionPolicy Bypass -File "D:\laragon\www\rpa\api\worker_controller.ps1"

pause
