<?php
// backend/data-consumer-service/api/data_access.php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/ApiKey.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Kết nối DB (PDO)
    $db  = Database::getConnection();
    $api = new ApiKey($db);

    // --- Lấy API key từ header Authorization ---
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    if (!isset($headers['Authorization']) && isset($headers['authorization'])) {
        // Một số server trả về 'authorization' chữ thường
        $headers['Authorization'] = $headers['authorization'];
    }

    if (!isset($headers['Authorization'])) {
        echo json_encode([
            "success" => false,
            "message" => "Thiếu header Authorization"
        ]);
        exit;
    }

    $authHeader = $headers['Authorization'];
    if (strpos($authHeader, 'Bearer ') !== 0) {
        echo json_encode([
            "success" => false,
            "message" => "Sai định dạng Authorization"
        ]);
        exit;
    }

    $apiKey = trim(substr($authHeader, 7)); // cắt bỏ 'Bearer '

    // --- Kiểm tra API key hợp lệ ---
    $keyData = $api->validateKey($apiKey);
    if (!$keyData) {
        echo json_encode([
            "success" => false,
            "message" => "API key không hợp lệ hoặc đã bị vô hiệu hóa."
        ]);
        exit;
    }

    $user_id    = (int)$keyData['user_id'];
    $dataset_id = isset($_GET['dataset_id']) ? (int)$_GET['dataset_id'] : 0;

    if ($dataset_id <= 0) {
        echo json_encode([
            "success" => false,
            "message" => "Thiếu hoặc sai tham số dataset_id"
        ]);
        exit;
    }

    // --- Kiểm tra quyền truy cập dataset ---
    $stmt = $db->prepare("
        SELECT * FROM purchases 
        WHERE user_id = :uid 
          AND dataset_id = :did 
          AND status = 'paid'
          AND (
                type = 'Mua'
             OR (type = 'Thuê tháng' AND (expiry_date IS NULL OR expiry_date >= NOW()))
             OR (type = 'Thuê năm'   AND (expiry_date IS NULL OR expiry_date >= NOW()))
          )
        LIMIT 1
    ");
    $stmt->execute([
        ':uid' => $user_id,
        ':did' => $dataset_id
    ]);
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$access) {
        echo json_encode([
            "success" => false,
            "message" => "Bạn không có quyền truy cập dataset này."
        ]);
        exit;
    }

    // --- Lấy thông tin dataset ---
    $stmt = $db->prepare("
        SELECT id, name, type, region, price, active, rent_monthly, rent_yearly 
        FROM datasets 
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $dataset_id]);
    $dataset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dataset) {
        echo json_encode([
            "success" => false,
            "message" => "Dataset không tồn tại."
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data"    => $dataset
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Lỗi server: " . $e->getMessage()
    ]);
}
