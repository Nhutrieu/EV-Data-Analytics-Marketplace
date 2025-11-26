<?php
// backend/data-consumer-service/api/cart.php

// ✅ Dùng Database class (PDO) trong classes/Database.php
require_once __DIR__ . '/../classes/Database.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

// Tạo biến $pdo dùng chung trong file này
try {
    $pdo = Database::getConnection(); // trả về PDO
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Không kết nối được DB: ' . $e->getMessage()
    ]);
    exit;
}

// Bắt buộc login
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa login']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$action  = $_GET['action'] ?? '';

// ===============================
// 1) SYNC ITEM (POST, không có ?action=...)
//    body: { package_id, selected_type, quantity, price? }
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === '') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    $package_id    = isset($data['package_id']) ? (int)$data['package_id'] : 0;
    $selected_type = trim($data['selected_type'] ?? '');
    $quantity      = isset($data['quantity']) ? (int)$data['quantity'] : 1;
    $price         = isset($data['price']) ? (float)$data['price'] : null;

    if ($package_id <= 0 || $selected_type === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu package_id hoặc selected_type'
        ]);
        exit;
    }

    if ($quantity <= 0) {
        // Nếu quantity <= 0 thì xoá item luôn
        $stmt = $pdo->prepare("
            DELETE FROM user_cart 
            WHERE user_id = ? AND package_id = ? AND selected_type = ?
        ");
        $stmt->execute([$user_id, $package_id, $selected_type]);

        echo json_encode(['success' => true, 'message' => 'Đã xoá item khỏi giỏ']);
        exit;
    }

    // Kiểm tra xem row đã tồn tại chưa
    $stmt = $pdo->prepare("
        SELECT id 
        FROM user_cart
        WHERE user_id = ? AND package_id = ? AND selected_type = ?
        LIMIT 1
    ");
    $stmt->execute([$user_id, $package_id, $selected_type]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // UPDATE số lượng (và price nếu có gửi lên)
        if ($price !== null) {
            $up = $pdo->prepare("
                UPDATE user_cart 
                SET quantity = ?, price = ?
                WHERE id = ?
            ");
            $up->execute([$quantity, $price, $row['id']]);
        } else {
            $up = $pdo->prepare("
                UPDATE user_cart 
                SET quantity = ?
                WHERE id = ?
            ");
            $up->execute([$quantity, $row['id']]);
        }
    } else {
        // INSERT mới
        $insert = $pdo->prepare("
            INSERT INTO user_cart (user_id, package_id, selected_type, price, quantity)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert->execute([
            $user_id,
            $package_id,
            $selected_type,
            $price ?? 0,
            $quantity
        ]);
    }

    echo json_encode(['success' => true]);
    exit;
}

// ===============================
// 2) CÁC ACTION KHÁC (GET/POST có ?action=...)
// ===============================
switch ($action) {
    // LẤY GIỎ HÀNG
    case 'get':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method không hỗ trợ']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM user_cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'cart'    => $cart
        ]);
        break;

    // XÓA 1 ITEM
    case 'remove':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method không hỗ trợ']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $package_id    = isset($data['package_id']) ? (int)$data['package_id'] : 0;
        $selected_type = trim($data['selected_type'] ?? '');

        if ($package_id <= 0 || $selected_type === '') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu dữ liệu remove cart'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("
            DELETE FROM user_cart
            WHERE user_id = ? AND package_id = ? AND selected_type = ?
        ");
        $stmt->execute([$user_id, $package_id, $selected_type]);

        echo json_encode(['success' => true]);
        break;

    // XÓA TOÀN BỘ GIỎ
    case 'clear':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method không hỗ trợ']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM user_cart WHERE user_id = ?");
        $stmt->execute([$user_id]);

        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
        break;
}
