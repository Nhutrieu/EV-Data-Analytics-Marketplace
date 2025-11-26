<?php
session_start();
require_once 'check_maintenance.php';
require_once 'db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin']) || ($_SESSION['admin']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy từ khóa tìm kiếm (nếu có)
$search = $_GET['q'] ?? '';
$sql = "SELECT id, username, email, role, created_at FROM users";
if ($search) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $sql .= " WHERE username LIKE '%$search_safe%' OR email LIKE '%$search_safe%'";
}
$sql .= " ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Quản lý người dùng</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../public/assets/css/manage-user.css">
</head>
<body>

<header>
  <div class="logo">
    <div class="mark">EV</div>
    <div>
      <div class="title">EV Data Admin</div>
      <div class="subtitle">Quản lý người dùng</div>
    </div>
  </div>
  <nav>
    <a href="admin.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="manage_users.php" class="active"><i class="fa-solid fa-users"></i> Người dùng</a>
    <a href="manage_datasets.php"><i class="fa-solid fa-database"></i> Dữ liệu</a>
    <a href=""><i class="fa-solid fa-envelope"></i> Liên hệ</a>
    <form style="display:inline;" action="logout.php" method="POST">
      <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</button>
    </form>
  </nav>
</header>

<div class="container">
  <h2>👥 Danh sách người dùng</h2>

  <form method="GET" class="search-form">
      <input type="text" name="q" placeholder="Tìm kiếm người dùng..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Tìm</button>
  </form>

  <table>
    <tr>
      <th>ID</th>
      <th>Tên đăng nhập</th>
      <th>Email</th>
      <th>Vai trò</th>
      <th>Ngày tạo</th>
      <th>Hành động</th>
    </tr>
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['username']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['role']) ?></td>
          <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
          <td>
            <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn-edit">Sửa</a>
            <a href="delete_user.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa người dùng này?')">Xóa</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="6" style="text-align:center;color:#9aa4b2;">Không tìm thấy người dùng nào.</td></tr>
    <?php endif; ?>
  </table>
</div>

</body>
</html>
