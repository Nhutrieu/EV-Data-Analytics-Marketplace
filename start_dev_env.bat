@echo off
title 🚀 Khởi động XAMPP và Docker Compose
echo ===============================================
echo   BẮT ĐẦU KHỞI ĐỘNG XAMPP VÀ DOCKER COMPOSE...
echo ===============================================

:: Mở XAMPP Control Panel
echo 🔸 Đang mở XAMPP Control Panel...
start "" "C:\xampp\xampp-control.exe"

:: (Tuỳ chọn) Mở Docker Desktop nếu chưa bật
echo 🔹 Đang mở Docker Desktop...
start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"

:: Chờ 10 giây để Docker khởi động
timeout /t 10 /nobreak >nul

:: Chuyển đến thư mục project
cd /d D:\database\htdocs\ev-data-analytics-marketplace

:: Chạy Docker Compose
echo 🔹 Đang khởi động Docker containers...
docker-compose up -d

echo ✅ Hoàn tất! XAMPP và Docker Compose đã chạy.
echo -----------------------------------------------
pause
