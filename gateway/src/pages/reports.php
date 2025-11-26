<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}
$conn = mysqli_connect('localhost', 'root', '', 'ev-data-analytics-marketplace');
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

$users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];
$datasets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM datasets"))['total'];
$contacts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM contact_messages"))['total'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Báo cáo thống kê</title>
<link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body>
<h2>📈 Báo cáo tổng hợp</h2>
<ul>
<li>Tổng số người dùng: <?= $users ?></li>
<li>Tổng số bộ dữ liệu: <?= $datasets ?></li>
<li>Số phản hồi từ người dùng: <?= $contacts ?></li>
</ul>
</body>
</html>
