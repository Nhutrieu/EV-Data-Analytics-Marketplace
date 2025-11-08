<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/ApiKey.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["success" => false, "message" => "Chưa đăng nhập"]);
    exit;
}

$db = Database::getConnection();
$api = new ApiKey($db);

// Kiểm tra đã có key chưa
$stmt = $db->prepare("SELECT api_key FROM api_keys WHERE user_id=:uid");
$stmt->execute([':uid' => $user_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo json_encode(["success" => true, "api_key" => $existing['api_key']]);
    exit;
}

// Nếu chưa có → tạo mới
$newKey = $api->generateKey($user_id);
echo json_encode(["success" => true, "api_key" => $newKey]);
