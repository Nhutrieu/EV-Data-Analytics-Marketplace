<?php
require_once '../Database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Chưa login']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

switch ($action) {

    // Thêm / tăng số lượng trong giỏ
    case 'add':
        $data = json_decode(file_get_contents('php://input'), true);
        $package_id    = $data['package_id'] ?? null;
        $selected_type = $data['selected_type'] ?? null;
        $price         = $data['price'] ?? null;
        $quantity      = $data['quantity'] ?? 1;

        if (!$package_id || !$selected_type || $price === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Thiếu dữ liệu add cart']);
            exit;
        }

        // Nếu đã có (user_id + package_id + selected_type) thì cộng dồn quantity
        $stmt = $pdo->prepare("
            INSERT INTO user_cart (user_id, package_id, selected_type, price, quantity)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                price    = VALUES(price),
                quantity = quantity + VALUES(quantity)
        ");
        $stmt->execute([$user_id, $package_id, $selected_type, $price, $quantity]);

        echo json_encode(['success' => true]);
        break;

    // Lấy giỏ hàng
    case 'get':
        $stmt = $pdo->prepare("SELECT * FROM user_cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'cart' => $cart]);
        break;

    // Xoá 1 item khỏi giỏ (theo dataset + loại gói)
    case 'remove':
        $data = json_decode(file_get_contents('php://input'), true);
        $package_id    = $data['package_id'] ?? null;
        $selected_type = $data['selected_type'] ?? null;

        if (!$package_id || !$selected_type) {
            http_response_code(400);
            echo json_encode(['error' => 'Thiếu dữ liệu remove cart']);
            exit;
        }

        $stmt = $pdo->prepare("
            DELETE FROM user_cart 
            WHERE user_id = ? 
              AND package_id = ? 
              AND selected_type = ?
        ");
        $stmt->execute([$user_id, $package_id, $selected_type]);

        echo json_encode(['success' => true]);
        break;

    // Xoá toàn bộ giỏ của user
    case 'clear':
        $stmt = $pdo->prepare("DELETE FROM user_cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action không hợp lệ']);
        break;
}
