<?php
// Yêu cầu controller
require_once __DIR__ . '/api/controllers/DatasetController.php';

$path = $_GET['path'] ?? '';  // Lấy tham số path từ query string

// Dựa trên path để gọi các phương thức khác nhau trong DatasetController
if ($path === 'datasets') {
    $controller = new DatasetController();
    $controller->index(); // Hiển thị tất cả datasets
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $controller = new DatasetController();
    $controller->view($id); // Xem chi tiết dataset theo ID
} else {
    echo json_encode([
        "success" => false,
        "message" => "Route not found"
    ]);
}
?>
