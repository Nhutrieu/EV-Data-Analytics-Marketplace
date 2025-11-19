<?php
session_start();
require_once 'db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin']) || ($_SESSION['admin']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy id dataset từ GET
$id = $_GET['id'] ?? null;
if ($id) {
    $id = intval($id); // tránh SQL injection
    $sql = "DELETE FROM datasets WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['msg'] = "Xóa dataset thành công.";
    } else {
        $_SESSION['msg'] = "Lỗi: " . mysqli_error($conn);
    }
} else {
    $_SESSION['msg'] = "ID không hợp lệ.";
}

// Quay về trang quản lý datasets
header('Location: manage_datasets.php');
exit;
?>
