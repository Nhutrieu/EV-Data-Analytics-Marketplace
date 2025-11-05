<?php
// ===========================================
// EV DATA MARKETPLACE ROUTER (Data Consumer Service)
// ===========================================

$page = $_GET['page'] ?? 'consumer';
$baseDir = __DIR__;
$publicDir = realpath($baseDir . '/../../public');

switch ($page) {

    // 1. Consumer UI
    case 'consumer':
        include $publicDir . '/consumer.html';
        break;

    // 2. Dataset API
    case 'datasets':
        require_once $baseDir . '/api/controllers/DatasetController.php';
        $datasetController = new DatasetController();
        if(isset($_GET['id'])){
            $datasetController->viewDataset($_GET['id']);
        } else {
            $datasetController->listDatasets();
        }
        break;

    // 3. Analytics Packages API
    case 'analytics':
        require_once $baseDir . '/api/controllers/AnalyticsController.php';
        $analyticsController = new AnalyticsController();
        if(isset($_GET['id'])){
            $analyticsController->viewPackage($_GET['id']);
        } else {
            $analyticsController->listPackages();
        }
        break;

case 'analytics_data':
    require_once $baseDir . '/api/controllers/AnalyticsController.php';
    $analyticsController = new AnalyticsController();

    if (isset($_GET['id'])) {
        $analyticsController->getPackageData($_GET['id']);
    } else {
        $analyticsController->listAnalyticsData(); // ✅ lấy toàn bộ
    }
    break;

// 4. Purchase API
case 'purchase':
    require_once $baseDir . '/api/controllers/PurchaseController.php';
    $purchaseController = new PurchaseController();

    // Nếu có id => xem purchase
    if (isset($_GET['id'])) {
        $purchaseController->viewPurchase($_GET['id']);
    }
    // Nếu POST để tạo purchase
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $user_id = $input['user_id'] ?? null;
        $dataset_id = $input['dataset_id'] ?? null;
        $type = $input['type'] ?? null;
        $price = $input['price'] ?? null;

        if ($user_id && $dataset_id && $type && $price) {
            $purchaseController->createPurchase($user_id, $dataset_id, $type, $price);
        } else {
            echo json_encode(["success" => false, "message" => "Thiếu dữ liệu tạo purchase"]);
        }
    }
    // GET tất cả purchase của user
    elseif (isset($_GET['user_id'])) {
        $purchaseController->listUserPurchases($_GET['user_id']);
    }
    else {
        echo json_encode(["success" => false, "message" => "Route not found"]);
    }
    break;
    // 5. Payment API
case 'payment':
    require_once $baseDir . '/classes/Database.php';
    $db = Database::getConnection();
    require_once $baseDir . '/api/controllers/PaymentController.php';
    $paymentController = new PaymentController($db);

    $action = $_GET['action'] ?? '';

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $paymentController->create();
    } elseif ($action === 'check') {
        $paymentController->checkPayment();
    } else {
        echo json_encode(["success" => false, "message" => "Hành động thanh toán không hợp lệ"]);
    }
    break;



    default:
        http_response_code(404);
        echo "<h2>404 - Trang không tồn tại</h2>";
        break;
}
