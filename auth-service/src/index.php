<?php
// ✅ Hiển thị trạng thái service
echo "✅ Auth Service is running on port 8001<br>";

// ✅ Thiết lập header chung cho toàn API
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// ✅ Nếu trình duyệt gửi preflight request (OPTIONS) → cho qua
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Autoload class bằng Composer
require_once __DIR__ . '/../vendor/autoload.php';

// ✅ Gọi router chính
require_once __DIR__ . '/router.php';
