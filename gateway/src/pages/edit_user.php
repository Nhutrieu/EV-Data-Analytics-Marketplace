<?php
session_start();
require_once 'check_maintenance.php';
require_once 'db_connect.php';

// Kiểm tra admin
if (!isset($_SESSION['admin']) || ($_SESSION['admin']['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: manage_users.php");
    exit;
}

// Lấy thông tin user
$id_safe = mysqli_real_escape_string($conn, $id);
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, username, email, role FROM users WHERE id='$id_safe'"));
if (!$user) {
    die("Người dùng không tồn tại.");
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $email    = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $role     = mysqli_real_escape_string($conn, $_POST['role'] ?? '');

    mysqli_query($conn, "UPDATE users SET username='$username', email='$email', role='$role' WHERE id='$id_safe'");
    header("Location: manage_users.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sửa người dùng</title>
<link rel="stylesheet" href="../public/assets/css/admin.css">
<style>
body { background:#0f1724; color:#e6eef6; font-family:'Inter', sans-serif; padding:20px;}
.container { max-width:500px; margin:50px auto; background:rgba(255,255,255,0.03); padding:30px; border-radius:12px;}
h2 { color:#06b6d4; margin-bottom:15px; }
label { display:block; margin:12px 0 4px; }
input, select { width:100%; padding:8px 10px; border-radius:6px; border:none; margin-bottom:10px; }
button { padding:10px 16px; border:none; border-radius:6px; background:#06b6d4; color:#fff; cursor:pointer; }
button:hover { opacity:0.8; }
</style>
</head>
<body>

<div class="container">
<h2>✏️ Sửa thông tin người dùng</h2>
<form method="POST">
    <label>Tên đăng nhập</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

    <label>Vai trò</label>
    <select name="role">
        <option value="user" <?= $user['role']=='user'?'selected':'' ?>>Người dùng</option>
        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
    </select>

    <button type="submit">Cập nhật</button>
</form>
</div>

</body>
</html>
