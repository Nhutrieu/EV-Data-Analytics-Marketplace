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


    default:
        http_response_code(404);
        echo "<h2>404 - Trang không tồn tại</h2>";
        break;
}
