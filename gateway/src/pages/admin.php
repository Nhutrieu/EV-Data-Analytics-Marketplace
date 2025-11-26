<?php
session_start();
require_once 'db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin']) || ($_SESSION['admin']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// --- Maintenance ---
$maintenanceFile = __DIR__ . '/maintenance_mode.txt';
if (!file_exists($maintenanceFile)) file_put_contents($maintenanceFile, "0");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_maintenance'])) {
    $currentStatus = trim(file_get_contents($maintenanceFile)) === "1";
    file_put_contents($maintenanceFile, $currentStatus ? "0" : "1");
    header("Location: admin.php");
    exit;
}

$MAINTENANCE_MODE = trim(file_get_contents($maintenanceFile)) === "1";

// Admin info
$adminName = $_SESSION['admin']['tenAdmin'] ?? 'Admin';
$adminEmail = $_SESSION['admin']['email'] ?? '';

// Stats
$users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users"))['total_users'] ?? 0;
$datasets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_datasets FROM datasets"))['total_datasets'] ?? 0;
$contacts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_contacts FROM contacts"))['total_contacts'] ?? 0;

// Recent feedback
$result = mysqli_query($conn, "SELECT id, name, email, subject, message, reply, status, created_at 
                               FROM contacts 
                               ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - EV Data Marketplace</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --bg: #0f1724;
  --card: #0b1220;
  --muted: #9aa4b2;
  --accent: #06b6d4;
  --green: #10b981;
  --glass: rgba(255,255,255,0.03);
  --maxw: 1200px;
  color-scheme: dark;
  color: #e6eef6;
  font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
}
* { box-sizing:border-box; transition: all 0.2s ease; }
body {
  margin:0; background: linear-gradient(180deg,#071022 0%,#081427 60%);
  min-height:100vh; color: var(--muted);
}
/* Header */
header { display:flex; justify-content:space-between; align-items:center; padding:16px 28px; background: var(--card); border-bottom:1px solid rgba(255,255,255,0.05);}
header .logo { font-size:20px; font-weight:700; color: var(--accent); display:flex; align-items:center; gap:8px; }
header nav { display:flex; align-items:center; gap:20px; }
header nav a { color: var(--muted); text-decoration:none; font-weight:600; position:relative; }
header nav a.active,
header nav a:hover { color: var(--accent); }
header nav a.active::after,
header nav a:hover::after { content:""; position:absolute; height:3px; width:100%; bottom:-6px; left:0; background: var(--accent); border-radius:4px; }
/* Maintenance banner */
.maintenance-banner { position:absolute; top:0; left:0; width:100%; background:#f59e0b; color:#111827; text-align:center; padding:4px 0; font-size:13px; font-weight:500; }
/* Container */
.container { max-width: var(--maxw); margin:20px auto; padding:0 28px; }
/* Titles */
.container h2 { color:#e6eef6; font-size:26px; margin-bottom:16px; }
.container h3 { color:#e6eef6; margin:24px 0 12px; }
/* Stats grid */
.stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:18px; margin-top:16px; }
.stat-card { background: var(--card); padding:20px; border-radius:14px; text-align:center; transition: transform 0.2s, box-shadow 0.2s;}
.stat-card:hover { transform:translateY(-3px); box-shadow:0 6px 18px rgba(6,182,212,0.25);}
.stat-card h3 { font-size:28px; color: var(--accent); margin:0; }
.stat-card p { font-size:14px; margin-top:6px; color: var(--muted); }
/* Feedback cards */
.feedback-list { margin-top:16px; }
.feedback-card { background: var(--card); padding:16px; border-radius:14px; margin-bottom:12px; box-shadow:0 2px 8px rgba(0,0,0,0.25); transition:0.2s; }
.feedback-card:hover { background: rgba(6,182,212,0.05); }
.feedback-card strong { color: var(--accent); }
.feedback-card small { color: var(--muted); font-size:12px; }
.feedback-card p { margin-top:6px; font-size:13px; line-height:1.4; color:#e6eef6; }
.feedback-card .status { font-size:12px; padding:2px 6px; border-radius:4px; color:#fff; margin-left:10px; }
.status.pending { background:#f59e0b; }
.status.replied { background:var(--green); }
.feedback-card a.reply-btn { float:right; color: var(--accent); font-size:14px; text-decoration:none; }
.feedback-card a.reply-btn:hover { text-decoration:underline; }
/* Admin widgets */
.admin-widgets { display:flex; flex-wrap:wrap; gap:20px; margin-top:20px; }
.widget { flex:1 1 180px; background: var(--card); color:#fff; text-align:center; border-radius:14px; padding:20px; text-decoration:none; transition:0.3s; }
.widget:hover { background: rgba(6,182,212,0.05); transform:translateY(-3px); }
.widget i { font-size:28px; color: var(--accent); margin-bottom:8px; display:block; }
.widget span { display:block; font-weight:500; }
/* Footer */
footer { margin-top:40px; text-align:center; color: var(--muted); font-size:13px; }
/* Responsive */
@media(max-width:768px){ .stats-grid, .admin-widgets { grid-template-columns: 1fr 1fr; } header { flex-direction:column; gap:10px; } }
@media(max-width:480px){ .stats-grid, .admin-widgets { grid-template-columns:1fr; } }
.logout-btn { background:none; border:none; color: var(--muted); cursor:pointer; font-weight:600; font-size:14px; }
.logout-btn:hover { color: var(--accent); }
</style>
</head>
<body>

<header>
  <div class="logo">
    <i class="fa-solid fa-database"></i> EV Admin
  </div>
  <nav>
    <!-- 🔥 🔥 LINK GATEWAY MỚI THÊM -->
    <a href="http://localhost:8004/admin_dashboard.php" style="color:#06b6d4; font-weight:700;">
    <i class="fa-solid fa-door-open"></i> Về trang Admin
</a>
    <!-- 🔥 🔥 -->

    <a href="admin.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="manage_users.php"><i class="fa-solid fa-users"></i> Người dùng</a>
    <a href="manage_datasets.php"><i class="fa-solid fa-database"></i> Dữ liệu</a>
    <a href="system_settings.php"><i class="fa-solid fa-gear"></i> Cấu hình</a>
    <a href="contacts.php"><i class="fa-solid fa-envelope"></i> Liên hệ</a>
    <form style="display:inline;" action="logout.php" method="POST">
      <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</button>
    </form>
  </nav>
  <?php if($MAINTENANCE_MODE): ?>
    <div class="maintenance-banner">⚠️ Hệ thống đang bảo trì</div>
  <?php endif; ?>
</header>

<div class="container">
  <h2>👋 Xin chào, <?= htmlspecialchars($adminName) ?></h2>
  <p>Email: <?= htmlspecialchars($adminEmail) ?></p>

  <h3>📊 Thống kê nhanh</h3>
  <div class="stats-grid">
    <div class="stat-card"><h3><?= $users ?></h3><p>Người dùng</p></div>
    <div class="stat-card"><h3><?= $datasets ?></h3><p>Bộ dữ liệu</p></div>
    <div class="stat-card"><h3><?= $contacts ?></h3><p>Phản hồi</p></div>
  </div>

  <h3>📩 Phản hồi gần đây</h3>
  <div class="feedback-list">
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="feedback-card">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
              <strong><?= htmlspecialchars($row['name']) ?></strong>
              <small>(<?= htmlspecialchars($row['email']) ?>)</small>
              <?php if ($row['status'] === 'pending'): ?>
                <span class="status pending">Chưa trả lời</span>
              <?php else: ?>
                <span class="status replied">Đã trả lời</span>
              <?php endif; ?>
            </div>
            <small><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></small>
          </div>
          <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
          <a href="reply_contact.php?id=<?= $row['id'] ?>" class="reply-btn">Trả lời</a>
          <?php if(!empty($row['reply'])): ?>
            <p style="color: var(--green);">Phản hồi: <?= nl2br(htmlspecialchars($row['reply'])) ?></p>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center;color:var(--muted);">Chưa có phản hồi nào.</p>
    <?php endif; ?>
  </div>

  <h3>🛠️ Chức năng nhanh</h3>
  <div class="admin-widgets">
    <a href="manage_users.php" class="widget"><i class="fa-solid fa-users"></i><span>Người dùng</span></a>
    <a href="manage_datasets.php" class="widget"><i class="fa-solid fa-database"></i><span>Dữ liệu</span></a>
    <a href="reply_contact.php" class="widget"><i class="fa-solid fa-envelope"></i><span>Liên hệ</span></a>
    <a href="maintenance.php" class="widget"><i class="fa-solid fa-tools"></i><span>Bảo trì</span></a>
    <a href="admin.php" class="widget"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
  </div>
</div>

<footer>© 2025 EV Data Marketplace</footer>

</body>
</html>
