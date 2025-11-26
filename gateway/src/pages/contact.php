<?php
session_start();
require_once 'check_maintenance.php';
require_once 'db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin']) || ($_SESSION['admin']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy từ khóa tìm kiếm
$search = $_GET['q'] ?? '';
$sql = "SELECT id, name, email, subject, message, reply, status, created_at FROM contacts";
if ($search) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $sql .= " WHERE name LIKE '%$search_safe%' OR email LIKE '%$search_safe%' OR subject LIKE '%$search_safe%'";
}
$sql .= " ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý liên hệ</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../public/assets/css/home.css">
<style>
body { background:#0f172a; color:#e2e8f0; font-family:'Inter',sans-serif; margin:0; padding:0; }
header { display:flex; align-items:center; justify-content:space-between; padding:15px 30px; background:#1e1e2f; border-bottom:1px solid rgba(255,255,255,0.05);}
header .logo .mark { font-weight:700; color:#06b6d4; font-size:22px; }
header .logo .title { font-weight:600; }
header nav a { margin-right:18px; color:#9aa4b2; text-decoration:none; font-weight:500; }
header nav a.active { color:#06b6d4; }
.logout-btn { background:#ef4444; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; }
.container { max-width:1100px; margin:40px auto; padding:30px; background:rgba(255,255,255,0.03); border-radius:16px; box-shadow:0 8px 30px rgba(0,0,0,0.2);}
h2 { color:#06b6d4; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:12px; border-bottom:1px solid rgba(255,255,255,0.08); text-align:left; color:#e2e8f0; }
th { color:#06b6d4; text-transform:uppercase; letter-spacing:0.5px; }
tr:hover { background: rgba(255,255,255,0.04); }
.status.pending { color:#f59e0b; font-weight:bold; }
.status.replied { color:#10b981; font-weight:bold; }
.reply-btn { background:#06b6d4; color:#fff; padding:4px 8px; border-radius:6px; text-decoration:none; font-size:13px; }
.reply-btn:hover { opacity:0.8; }
.search-form { margin-bottom:15px; }
.search-form input { padding:6px 10px; border-radius:6px; border:none; width:250px; }
.search-form button { padding:6px 10px; border-radius:6px; border:none; background:#06b6d4; color:#fff; cursor:pointer; }
</style>
</head>
<body>

<header>
  <div class="logo">
    <div class="mark">EV</div>
    <div>
      <div class="title">EV Data Admin</div>
      <div class="subtitle">Quản lý liên hệ</div>
    </div>
  </div>
  <nav>
    <a href="admin.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="manage_users.php"><i class="fa-solid fa-users"></i> Người dùng</a>
    <a href="manage_datasets.php"><i class="fa-solid fa-database"></i> Dữ liệu</a>
    <a href="contacts.php" class="active"><i class="fa-solid fa-envelope"></i> Liên hệ</a>
    <form style="display:inline;" action="logout.php" method="POST">
      <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</button>
    </form>
  </nav>
</header>

<div class="container">
  <h2>📩 Danh sách liên hệ</h2>

  <form method="GET" class="search-form">
      <input type="text" name="q" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Tìm</button>
  </form>

  <table>
    <tr>
      <th>ID</th>
      <th>Tên</th>
      <th>Email</th>
      <th>Chủ đề</th>
      <th>Trạng thái</th>
      <th>Ngày gửi</th>
      <th>Hành động</th>
    </tr>
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['subject']) ?></td>
          <td class="status <?= $row['status'] ?>"><?= $row['status'] === 'pending' ? 'Chưa trả lời' : 'Đã trả lời' ?></td>
          <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
          <td>
            <a href="reply_contact.php?id=<?= $row['id'] ?>" class="reply-btn">Trả lời</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="7" style="text-align:center;color:#9aa4b2;">Chưa có liên hệ nào.</td></tr>
    <?php endif; ?>
  </table>
</div>

</body>
</html>
