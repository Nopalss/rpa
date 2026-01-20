@echo off
title RPA Redis Worker_chart Controller

cd /d D:\laragon\www\rpa\api\chart

powershell -NoProfile -ExecutionPolicy Bypass -File  "D:\laragon\www\rpa\api\chart\worker_cpk_controller.ps1"

pause