<?php
require_once '../Database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Chưa login']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'add':
        $data = json_decode(file_get_contents('php://input'), true);
        $package_id = $data['package_id'];
        $selected_type = $data['selected_type'];
        $price = $data['price'];

        $stmt = $pdo->prepare("
            INSERT INTO user_cart (user_id, package_id, selected_type, price)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE price = ?, quantity = quantity + 1
        ");
        $stmt->execute([$user_id, $package_id, $selected_type, $price, $price]);

        echo json_encode(['success' => true]);
        break;

    case 'get':
        $stmt = $pdo->prepare("SELECT * FROM user_cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'cart' => $cart]);
        break;

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
