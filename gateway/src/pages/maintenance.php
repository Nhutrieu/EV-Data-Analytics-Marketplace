<?php
session_start();

if (!isset($_SESSION['admin']) || ($_SESSION['admin']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// File maintenance trong cùng thư mục
$maintenanceFile = __DIR__ . '/maintenance_mode.txt';

// Tạo file nếu chưa tồn tại
if (!file_exists($maintenanceFile)) {
    file_put_contents($maintenanceFile, "0"); // 0 = tắt bảo trì
}

// Xử lý bật/tắt bảo trì
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'on') {
        file_put_contents($maintenanceFile, "1");
    } elseif ($action === 'off') {
        file_put_contents($maintenanceFile, "0");
    }
}

// Đọc trạng thái hiện tại
$maintenanceMode = trim(file_get_contents($maintenanceFile));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý bảo trì</title>
</head>
<body>
<h2>⚙️ Quản lý bảo trì</h2>
<p>Trạng thái hiện tại: <strong><?= $maintenanceMode === "1" ? "BẬT" : "TẮT" ?></strong></p>

<form method="POST">
    <button type="submit" name="action" value="on">Bật bảo trì</button>
    <button type="submit" name="action" value="off">Tắt bảo trì</button>
</form>
</body>
</html>
