<?php
session_start();
require_once 'db_connect.php';
// Kiểm tra quyền admin
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = mysqli_connect('127.0.0.1', 'root', '', 'ev-data-analytics-marketplace');
if (!$conn) {
    die("Không thể kết nối CSDL: " . mysqli_connect_error());
}

// Lấy dữ liệu cấu hình hiện tại
$sql = "SELECT * FROM system_settings WHERE id = 1";
$result = mysqli_query($conn, $sql);
$settings = mysqli_fetch_assoc($result);

// Nếu chưa có bản ghi nào, tạo mặc định
if (!$settings) {
    mysqli_query($conn, "INSERT INTO system_settings (system_name, system_description, support_email, maintenance_mode)
                         VALUES ('EV Data Analytics Marketplace', 'Nền tảng phân tích dữ liệu xe điện Việt Nam', 'support@evmarket.vn', 0)");
    $settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM system_settings WHERE id = 1"));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $system_name = mysqli_real_escape_string($conn, $_POST['system_name'] ?? '');
    $system_description = mysqli_real_escape_string($conn, $_POST['system_description'] ?? '');
    $support_email = mysqli_real_escape_string($conn, $_POST['support_email'] ?? '');
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;

    // Upload logo nếu có
    $logo_url = $settings['logo_url'] ?? '';
    if (!empty($_FILES['logo']['name'])) {
        $target_dir = "../public/assets/uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . "_" . basename($_FILES["logo"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $logo_url = str_replace('../public', '', $target_file);
        }
    }

    // Cập nhật cấu hình
    $update_sql = "UPDATE system_settings SET
                   system_name='$system_name',
                   system_description='$system_description',
                   support_email='$support_email',
                   logo_url='$logo_url',
                   maintenance_mode=$maintenance_mode,
                   updated_at=NOW()
                   WHERE id=1";
    mysqli_query($conn, $update_sql);

    // Ghi log admin
    $adminName = $_SESSION['admin']['tenAdmin'] ?? 'Admin';
    $log_msg = "[" . date('Y-m-d H:i:s') . "] $adminName đã cập nhật cấu hình hệ thống.\n";
    file_put_contents("../logs/admin_actions.log", $log_msg, FILE_APPEND);

    header("Location: system_settings.php?saved=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Cấu hình hệ thống - EV Data Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --bg:#0f1724;
  --card:#0b1220;
  --muted:#9aa4b2;
  --accent:#06b6d4;
  color-scheme:dark;
  color:#e6eef6;
  font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial;
}
body {
  margin:0;
  background:linear-gradient(180deg,#071022 0%, #081427 60%);
  min-height:100vh;
  padding:40px;
}
.container {
  background:var(--card);
  border-radius:14px;
  max-width:800px;
  margin:auto;
  padding:30px;
  box-shadow:0 10px 30px rgba(0,0,0,0.5);
}
h2 {
  color:var(--accent);
  margin-bottom:20px;
  display:flex;
  align-items:center;
  gap:10px;
}
form label {
  display:block;
  font-weight:600;
  margin-top:18px;
}
form input[type="text"],
form input[type="email"],
form textarea {
  width:100%;
  padding:10px 12px;
  border-radius:8px;
  border:1px solid rgba(255,255,255,0.1);
  background:#111827;
  color:white;
  margin-top:6px;
  font-family:inherit;
}
textarea {
  resize:vertical;
  min-height:80px;
}
form input[type="checkbox"] {
  transform:scale(1.2);
  margin-right:6px;
}
button {
  margin-top:22px;
  padding:10px 18px;
  border:none;
  border-radius:8px;
  background:var(--accent);
  color:#041017;
  font-weight:700;
  cursor:pointer;
  transition:transform 0.2s, box-shadow 0.2s;
}
button:hover {
  transform:translateY(-2px);
  box-shadow:0 0 12px rgba(6,182,212,0.4);
}
a.back {
  color:var(--muted);
  text-decoration:none;
  display:inline-block;
  margin-bottom:24px;
  transition:color 0.2s;
}
a.back:hover {color:var(--accent);}
.logo-preview {
  margin-top:10px;
  background:#111827;
  padding:10px;
  border-radius:10px;
  display:flex;
  align-items:center;
  gap:10px;
}
.logo-preview img {
  width:60px;
  height:60px;
  border-radius:8px;
  object-fit:contain;
  background:white;
}
.notice {
  color:#10b981;
  text-align:center;
  font-weight:600;
  margin-bottom:15px;
}
</style>
</head>
<body>

<a href="admin.php" class="back"><i class="fa-solid fa-arrow-left"></i> Quay lại trang Admin</a>

<div class="container">
  <h2><i class="fa-solid fa-gear"></i> Cấu hình hệ thống</h2>

  <?php if(isset($_GET['saved'])): ?>
    <div class="notice">✅ Đã lưu thay đổi thành công!</div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <label>Tên hệ thống</label>
    <input type="text" name="system_name" value="<?= htmlspecialchars($settings['system_name']); ?>">

    <label>Mô tả hệ thống</label>
    <textarea name="system_description"><?= htmlspecialchars($settings['system_description']); ?></textarea>

    <label>Email hỗ trợ</label>
    <input type="email" name="support_email" value="<?= htmlspecialchars($settings['support_email']); ?>">

    <label>Logo hệ thống</label>
    <input type="file" name="logo" accept="image/*">
    <?php if (!empty($settings['logo_url'])): ?>
      <div class="logo-preview">
        <img src="<?= htmlspecialchars($settings['logo_url']); ?>" alt="Logo">
        <span>Logo hiện tại</span>
      </div>
    <?php endif; ?>

    <label style="margin-top:20px;">
      <input type="checkbox" name="maintenance_mode" <?= $settings['maintenance_mode'] ? 'checked' : ''; ?>>
      Kích hoạt chế độ bảo trì
    </label>

    <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi</button>
  </form>
</div>

</body>
</html>
