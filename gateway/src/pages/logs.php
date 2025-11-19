<?php
session_start();
require_once 'check_maintenance.php';
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}
$conn = mysqli_connect('localhost', 'root', '', 'ev-data-analytics-marketplace');
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

$result = mysqli_query($conn, "SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Nhật ký hệ thống - EV Data</title>
<link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body>
<h2>🧾 Nhật ký hệ thống</h2>
<table border="1" cellpadding="10" cellspacing="0">
<tr><th>Email</th><th>Hành động</th><th>Mô tả</th><th>Thời gian</th></tr>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
  <td><?= htmlspecialchars($row['user_email']); ?></td>
  <td><?= htmlspecialchars($row['action']); ?></td>
  <td><?= htmlspecialchars($row['description']); ?></td>
  <td><?= $row['created_at']; ?></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
