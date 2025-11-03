<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';

header('Content-Type: application/json');

// Kiểm tra user login
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Vui lòng đăng nhập để truy cập dữ liệu."
    ]);
    exit;
}

// Lấy dataset ID từ query
$dataset_id = $_GET['dataset_id'] ?? null;
if (!$dataset_id) {
    echo json_encode([
        "success" => false,
        "message" => "Thiếu tham số dataset_id."
    ]);
    exit;
}

// Kết nối DB
$db = Database::getConnection();

// Kiểm tra quyền user: đã mua hoặc thuê còn hạn
$stmt = $db->prepare("SELECT * FROM purchases WHERE user_id=:uid AND dataset_id=:did AND 
    (type='Mua' OR (type='Thuê tháng' AND DATE_ADD(purchased_at, INTERVAL 1 MONTH) >= NOW()) OR 
     (type='Thuê năm' AND DATE_ADD(purchased_at, INTERVAL 1 YEAR) >= NOW()))");
$stmt->execute([
    ':uid' => $user_id,
    ':did' => $dataset_id
]);
$access = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$access) {
    echo json_encode([
        "success" => false,
        "message" => "Bạn chưa có quyền truy cập dataset này hoặc thời hạn thuê đã hết."
    ]);
    exit;
}

// ============================
// Gọi API bên thứ 3
// ============================
$apiUrl = "https://thirdparty.example.com/api/data?dataset_id=" . $dataset_id;
$apiKey = "YOUR_API_KEY_HERE";

// Gọi API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apiKey"
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode([
        "success" => false,
        "message" => "Không lấy được dữ liệu từ bên thứ 3."
    ]);
    exit;
}

// Trả dữ liệu về frontend
echo $response;
