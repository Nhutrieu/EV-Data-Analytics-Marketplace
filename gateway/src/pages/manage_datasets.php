<?php
session_start();
require_once 'check_maintenance.php';
require_once 'db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin']) || ($_SESSION['admin']['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

// Lấy từ khóa tìm kiếm (nếu có)
$search = $_GET['q'] ?? '';
$sql = "SELECT id, name, image_url, source, size, description, is_featured, created_at FROM datasets";
if ($search) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $sql .= " WHERE name LIKE '%$search_safe%' OR source LIKE '%$search_safe%'";
}
$sql .= " ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

// Thống kê nhanh
$total_datasets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM datasets"))['total'] ?? 0;
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'] ?? 0;
$total_contacts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM contacts"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Quản lý bộ dữ liệu</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../public/assets/css/manage-dataset.css">
</head>
<body>

<header>
  <div class="logo">
    <div class="mark">EV</div>
    <div>
      <div class="title">EV Data Admin</div>
      <div class="subtitle">Quản lý dữ liệu</div>
    </div>
  </div>
  <nav>
    <a href="admin.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="manage_users.php"><i class="fa-solid fa-users"></i> Người dùng</a>
    <a href="manage_datasets.php" class="active"><i class="fa-solid fa-database"></i> Dữ liệu</a>
    <a href="contacts.php"><i class="fa-solid fa-envelope"></i> Liên hệ</a>
    <a href="maintenance.php"><i class="fa-solid fa-tools"></i> Bảo trì</a>
    <form style="display:inline;" action="logout.php" method="POST">
      <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</button>
    </form>
  </nav>
</header>

<div class="container">
    <h2>💾 Quản lý bộ dữ liệu</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $total_datasets ?></h3>
            <p>Bộ dữ liệu</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_users ?></h3>
            <p>Người dùng</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_contacts ?></h3>
            <p>Phản hồi</p>
        </div>
    </div>

    <form method="GET" class="search-form">
        <input type="text" name="q" placeholder="Tìm kiếm bộ dữ liệu..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Tìm</button>
    </form>

    <table>
    <tr>
        <th>ID</th>
        <th>Ảnh</th>
        <th>Tên bộ dữ liệu</th>
        <th>Nguồn</th>
        <th>Kích thước</th>
        <th>Mô tả</th>
        <th>Nổi bật</th>
        <th>Ngày tạo</th>
        <th>Hành động</th>
    </tr>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while ($ds = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $ds['id'] ?></td>
                <td>
                    <?php if(!empty($ds['image_url'])): ?>
                        <img src="<?= htmlspecialchars($ds['image_url']) ?>" alt="<?= htmlspecialchars($ds['name']) ?>">
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($ds['name']) ?></td>
                <td><?= htmlspecialchars($ds['source']) ?></td>
                <td><?= htmlspecialchars($ds['size']) ?></td>
                <td class="description"><?= htmlspecialchars($ds['description']) ?></td>
                <td><?= $ds['is_featured'] ? '<span class="featured">✔</span>' : '—' ?></td>
                <td><?= date('d/m/Y H:i', strtotime($ds['created_at'])) ?></td>
                <td>
                    <a href="edit_dataset.php?id=<?= $ds['id'] ?>" class="btn-edit">Sửa</a>
                    <a href="delete_dataset.php?id=<?= $ds['id'] ?>" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa dataset này?')">Xóa</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="9" style="text-align:center;color:#9aa4b2;">Không tìm thấy dataset nào.</td></tr>
    <?php endif; ?>
    </table>
</div>

</body>
</html>
