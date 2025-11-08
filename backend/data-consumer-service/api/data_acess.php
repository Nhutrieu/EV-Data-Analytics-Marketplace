<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/ApiKey.php';

header('Content-Type: application/json');

$db = Database::getConnection();
$api = new ApiKey($db);

// --- Lấy API key từ header Authorization ---
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    echo json_encode(["success" => false, "message" => "Thiếu header Authorization"]);
    exit;
}

$authHeader = $headers['Authorization'];
if (strpos($authHeader, 'Bearer ') !== 0) {
    echo json_encode(["success" => false, "message" => "Sai định dạng Authorization"]);
    exit;
}

$apiKey = trim(str_replace('Bearer ', '', $authHeader));

// --- Kiểm tra API key hợp lệ ---
$keyData = $api->validateKey($apiKey);
if (!$keyData) {
    echo json_encode(["success" => false, "message" => "API key không hợp lệ hoặc đã bị vô hiệu hóa."]);
    exit;
}

$user_id = $keyData['user_id'];
$dataset_id = $_GET['dataset_id'] ?? null;
if (!$dataset_id) {
    echo json_encode(["success" => false, "message" => "Thiếu tham số dataset_id"]);
    exit;
}

// --- Kiểm tra quyền truy cập dataset ---
$stmt = $db->prepare("
    SELECT * FROM purchases 
    WHERE user_id=:uid AND dataset_id=:did 
    AND (
        type='Mua' 
        OR (type='Thuê tháng' AND DATE_ADD(purchased_at, INTERVAL 1 MONTH) >= NOW()) 
        OR (type='Thuê năm' AND DATE_ADD(purchased_at, INTERVAL 1 YEAR) >= NOW())
    )
");
$stmt->execute([':uid' => $user_id, ':did' => $dataset_id]);
$access = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$access) {
    echo json_encode(["success" => false, "message" => "Bạn không có quyền truy cập dataset này."]);
    exit;
}

// --- ✅ Lấy thông tin dataset thật ---
$stmt = $db->prepare("SELECT id, name, type, region, price, active, rent_monthly, rent_yearly 
                      FROM datasets WHERE id = :id");
$stmt->execute([':id' => $dataset_id]);
$dataset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dataset) {
    echo json_encode(["success" => false, "message" => "Dataset không tồn tại."]);
    exit;
}

echo json_encode(["success" => true, "data" => $dataset]);
?>
