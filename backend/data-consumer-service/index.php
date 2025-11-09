<?php
// ===========================================
// EV DATA MARKETPLACE ROUTER (Docker version)
// ===========================================

$baseDir   = __DIR__;               // gốc project
$publicDir = $baseDir . '/public';  // thư mục chứa consumer.html

$page = $_GET['page'] ?? 'consumer';

switch ($page) {

    // 1. Consumer UI (frontend HTML)
    case 'consumer':
        // http://localhost:8080/index.php?page=consumer
        // hoặc bạn có thể truy cập trực tiếp http://localhost:8080/public/consumer.html
        require $publicDir . '/consumer.html';
        break;

    // 2. Dataset API
    case 'datasets':
        header('Content-Type: application/json; charset=utf-8');

        require_once $baseDir . '/api/controllers/DatasetController.php';
        $datasetController = new DatasetController();

        if (isset($_GET['id'])) {
            $datasetController->viewDataset((int)$_GET['id']);
        } else {
            $datasetController->listDatasets();
        }
        break;

    // 3. Analytics Packages API (danh sách gói phân tích)
    case 'analytics':
        header('Content-Type: application/json; charset=utf-8');

        require_once $baseDir . '/api/controllers/AnalyticsController.php';
        $analyticsController = new AnalyticsController();

        if (isset($_GET['id'])) {
            $analyticsController->viewPackage((int)$_GET['id']);
        } else {
            $analyticsController->listPackages();
        }
        break;

    // 3b. Analytics data (dữ liệu cho biểu đồ, dashboard)
    case 'analytics_data':
        header('Content-Type: application/json; charset=utf-8');

        require_once $baseDir . '/api/controllers/AnalyticsController.php';
        $analyticsController = new AnalyticsController();

        if (isset($_GET['id'])) {
            $analyticsController->getPackageData((int)$_GET['id']);
        } else {
            $analyticsController->listAnalyticsData();
        }
        break;

    // 4. Purchase API (lịch sử mua hàng, tạo purchase)
    case 'purchase':
        session_start();
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Chưa login"
            ]);
            break;
        }

        $userId = (int) $_SESSION['user_id'];

        require_once $baseDir . '/api/controllers/PurchaseController.php';
        $purchaseController = new PurchaseController();

        if (isset($_GET['id'])) {
            // Xem chi tiết 1 purchase
            $purchaseController->viewPurchase((int) $_GET['id']);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // purchase.js gọi GET /index.php?page=purchase
            $purchaseController->listUserPurchases($userId);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Tạo purchase bằng API
            $input      = json_decode(file_get_contents('php://input'), true) ?? [];
            $dataset_id = $input['dataset_id'] ?? null;
            $type       = $input['type'] ?? null;
            $price      = $input['price'] ?? null;

            if ($dataset_id && $type && $price) {
                $purchaseController->createPurchase(
                    $userId,
                    (int) $dataset_id,
                    $type,
                    (float) $price
                );
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Thiếu dữ liệu tạo purchase"
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Route purchase không hỗ trợ method này"
            ]);
        }
        break;

    // 5. API Key management
    case 'api_key':
        header('Content-Type: application/json; charset=utf-8');

        require_once $baseDir . '/classes/Database.php';
        require_once $baseDir . '/classes/ApiKey.php';

        $db  = Database::getConnection();
        $api = new ApiKey($db);

        $action  = $_GET['action'] ?? '';
        $user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;

        // Tạo mới API key (xóa key cũ trước)
        if ($action === 'create' && $user_id) {

            // Xóa key cũ nếu có
            $stmt = $db->prepare("DELETE FROM api_keys WHERE user_id = :uid");
            $stmt->execute([':uid' => $user_id]);

            // Tạo key mới
            $key = $api->createKey($user_id);
            echo json_encode([
                "success" => true,
                "message" => "Tạo API key mới thành công. Key cũ đã bị xóa.",
                "api_key" => $key
            ]);
        }

        // Lấy key của user
        elseif ($action === 'list' && $user_id) {
            $stmt = $db->prepare("
                SELECT id, api_key, status, created_at
                FROM api_keys
                WHERE user_id = :uid
            ");
            $stmt->execute([':uid' => $user_id]);
            $key = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($key) {
                $masked = substr($key['api_key'], 0, 10) . str_repeat('*', 10);
                $key['api_key'] = $masked;

                echo json_encode([
                    "success" => true,
                    "data"    => $key
                ]);
            } else {
                echo json_encode([
                    "success" => true,
                    "data"    => null,
                    "message" => "Chưa có API key."
                ]);
            }
        }

        // Xóa key hiện tại của user
        elseif ($action === 'delete' && $user_id) {
            $stmt = $db->prepare("DELETE FROM api_keys WHERE user_id = :uid");
            $ok   = $stmt->execute([':uid' => $user_id]);

            echo json_encode([
                "success" => $ok,
                "message" => $ok
                    ? "Đã xóa API key của user."
                    : "Không xóa được API key."
            ]);
        }

        else {
            echo json_encode([
                "success" => false,
                "message" => "Hành động không hợp lệ hoặc thiếu user_id."
            ]);
        }
        break;

    // 6. Payment
    case 'payment':
        header('Content-Type: application/json; charset=utf-8');

        $action = $_GET['action'] ?? '';

        if ($action === 'create') {
            require_once $baseDir . '/payment/create_payment.php';
            // file này tự đọc php://input và echo json
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Action payment không hợp lệ"
            ]);
        }
        break;

    // 7. Default 404
    default:
        http_response_code(404);
        echo "<h2>404 - Trang không tồn tại</h2>";
        break;
}
