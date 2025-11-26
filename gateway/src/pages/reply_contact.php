<?php
session_start();
require_once 'check_maintenance.php';
require_once 'db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin']) || ($_SESSION['admin']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$id = (int)$id;

// Lấy thông tin liên hệ
$result = mysqli_query($conn, "SELECT * FROM contacts WHERE id=$id");
$contact = mysqli_fetch_assoc($result);

if (!$contact) {
    die("Liên hệ không tồn tại!");
}

$success_msg = "";

// Xử lý submit phản hồi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply = mysqli_real_escape_string($conn, $_POST['reply']);
    mysqli_query($conn, "UPDATE contacts SET reply='$reply', status='replied' WHERE id=$id");
    $success_msg = "✅ Phản hồi đã được gửi thành công!";
    // Cập nhật lại dữ liệu contact
    $result = mysqli_query($conn, "SELECT * FROM contacts WHERE id=$id");
    $contact = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Trả lời liên hệ - EV Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../public/assets/css/home.css">
<style>
:root {
  --bg: #0f1724; 
  --card: #0b1220;
  --muted: #9aa4b2;
  --accent: #06b6d4;
  --green: #10b981;
}

body { background: var(--bg); color: #e6eef6; font-family:'Inter',sans-serif; margin:0; padding:0; }
header { display:flex; align-items:center; justify-content:space-between; padding:16px 28px; background:var(--card); border-bottom:1px solid rgba(255,255,255,0.05);}
header .logo .mark { font-weight:700; color:var(--accent); font-size:22px; }
header .logo .title { font-weight:600; color:#e6eef6; }
header nav a { margin-right:18px; color:#9aa4b2; text-decoration:none; font-weight:500; }
header nav a.active { color:var(--accent); }
.logout-btn { background:#ef4444; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; }

.container { max-width:700px; margin:40px auto; padding:30px; background:var(--card); border-radius:16px; box-shadow:0 8px 30px rgba(0,0,0,0.2);}
h2 { color:var(--accent); margin-bottom:20px; }
label { display:block; margin-top:15px; margin-bottom:5px; color:#e6eef6; }
textarea { width:100%; height:120px; padding:10px; border-radius:6px; border:none; background: rgba(255,255,255,0.05); color:#e6eef6; }
textarea:focus { outline:2px solid var(--accent); }
button { margin-top:10px; padding:8px 15px; border:none; border-radius:6px; background:var(--accent); color:#04141a; cursor:pointer; font-weight:600; }
button:hover { opacity:0.9; }

.info { background: rgba(255,255,255,0.03); padding:12px; border-radius:8px; margin-bottom:15px; line-height:1.5; }
.success-msg { background: var(--green); color:#04141a; padding:10px 12px; border-radius:6px; margin-bottom:15px; font-weight:600; }
</style>
</head>
<body>

<header>
  <div class="logo">
    <div class="mark">EV</div>
    <div class="title">EV Data Admin - Trả lời liên hệ</div>
  </div>
  <nav>
    <a href="admin.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="contacts.php" class="active"><i class="fa-solid fa-envelope"></i> Liên hệ</a>
    <form style="display:inline;" action="logout.php" method="POST">
      <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</button>
    </form>
  </nav>
</header>

<div class="container">
  <h2>Trả lời liên hệ</h2>

  <?php if($success_msg): ?>
    <div class="success-msg"><?= $success_msg ?></div>
  <?php endif; ?>

  <div class="info">
      <strong>Người gửi:</strong> <?= htmlspecialchars($contact['name']) ?><br>
      <strong>Email:</strong> <?= htmlspecialchars($contact['email']) ?><br>
      <strong>Chủ đề:</strong> <?= htmlspecialchars($contact['subject']) ?><br>
      <strong>Nội dung:</strong><br> <?= nl2br(htmlspecialchars($contact['message'])) ?>
  </div>

  <form method="POST">
      <label for="reply">Phản hồi:</label>
      <textarea name="reply" id="reply" required><?= htmlspecialchars($contact['reply']) ?></textarea>
      <button type="submit"><i class="fa-solid fa-paper-plane"></i> Gửi phản hồi</button>
  </form>
</div>

</body>
</html>
