<?php
// ===========================================
// EV DATA PROVIDER SERVICE ROUTER
// ===========================================

// CORS cho API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Đường dẫn gốc
$baseDir     = __DIR__;
$frontendDir = $baseDir . '/frontend';

// page quyết định route
$page = $_GET['page'] ?? 'data';

switch ($page) {

    // ========= FRONTEND =========

    case 'data':
        // Quản lý dữ liệu (data.html)
        require $frontendDir . '/data.html';
        break;

    case 'pricing':
        require $frontendDir . '/pricing.html';
        break;

    case 'revenue':
        require $frontendDir . '/revenue.html';
        break;

    case 'privacy':
        require $frontendDir . '/privacy.html';
        break;

    // ========= DATASETS API (JSON) =========
    //
    // GET    ?page=datasets
    // GET    ?page=datasets&id=13
    // POST   ?page=datasets
    // PUT    ?page=datasets&id=13
    // DELETE ?page=datasets&id=13
    // POST   ?page=datasets&id=13&action=upload (multipart/form-data)
    //
    case 'datasets':
        header('Content-Type: application/json; charset=utf-8');

        require_once $baseDir . '/api/DatasetController.php';
        $controller = new DatasetController();

        $method = $_SERVER['REQUEST_METHOD'];
        $id     = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $action = $_GET['action'] ?? null;

        if ($method === 'GET') {
            if ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
            break;
        }

        if ($method === 'POST' && !$id) {
            $controller->store();
            break;
        }

        if ($method === 'POST' && $id && $action === 'upload') {
            $controller->upload($id);
            break;
        }

        if ($method === 'PUT' && $id) {
            $controller->update($id);
            break;
        }

        if ($method === 'DELETE' && $id) {
            $controller->destroy($id);
            break;
        }

        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed for datasets']);
        break;

    // ========= PRICING API (JSON) =========

  case 'pricing_api':
    header('Content-Type: application/json; charset=utf-8');
    require_once $baseDir . '/api/PricingController.php';
    $controller = new PricingController();

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // Lấy chính sách giá hiện tại
        $controller->show();
        break;
    }

    if ($method === 'POST' || $method === 'PUT') {
        // Tạo mới / cập nhật chính sách giá
        $controller->update();
        break;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    break;


    // ========= REVENUE API (JSON) =========
  
  case 'revenue_api':
    header('Content-Type: application/json; charset=utf-8');
    require_once $baseDir . '/api/RevenueController.php';
    $controller = new RevenueController();

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'dashboard';

    if ($method === 'GET') {
        if ($action === 'recent') {
            $controller->recentTransactions();
            break;
        }
        $controller->dashboard();
        break;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    break;
    // ========= PRIVACY API (JSON) =========
case 'privacy_api':
    header('Content-Type: application/json; charset=utf-8');
    require_once $baseDir . '/api/PrivacyController.php';
    $controller = new PrivacyController();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->show();
        break;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->update();
        break;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    break;
    // ========= SETTINGS API (JSON) =========

case 'settings_api':
    require_once __DIR__ . '/api/SettingsController.php';
    $controller = new SettingsController();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->show();
    } else {
        $controller->update();
    }
    break;


    // ========= DEFAULT =========
    default:
        http_response_code(404);
        echo "<h2>404 - Route không tồn tại (page={$page})</h2>";
        break;
}
