<?php
session_start();
require_once 'db_connect.php';

$token = $_GET['token'] ?? '';
$message = '';
$showForm = true;

if (!$token) {
    $message = "Liên kết không hợp lệ.";
    $showForm = false;
}

// Kiểm tra token tồn tại và chưa hết hạn
if ($showForm) {
    $token = mysqli_real_escape_string($conn, $token);
    $result = mysqli_query($conn, "SELECT id, reset_expires FROM users WHERE reset_token='$token'");
    if (mysqli_num_rows($result) === 0) {
        $message = "Liên kết không hợp lệ hoặc đã hết hạn.";
        $showForm = false;
    } else {
        $user = mysqli_fetch_assoc($result);
        if (strtotime($user['reset_expires']) < time()) {
            $message = "Liên kết đã hết hạn.";
            $showForm = false;
        }
    }
}

// Xử lý form khi submit
if ($showForm && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($password !== $confirm) {
        $message = "Mật khẩu xác nhận không trùng khớp.";
    } elseif (strlen($password) < 6) {
        $message = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userId = $user['id'];
        mysqli_query($conn, "UPDATE users SET password='$hash', reset_token=NULL, reset_expires=NULL WHERE id=$userId");
        $message = "Đặt lại mật khẩu thành công. Bạn có thể đăng nhập <a href='login.php'>tại đây</a>.";
        $showForm = false;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đặt lại mật khẩu</title>
<link rel="stylesheet" href="../public/assets/css/home.css">
</head>
<body>
<div class="container">
<h2>Đặt lại mật khẩu</h2>
<?php if ($message) echo "<p>$message</p>"; ?>

<?php if ($showForm): ?>
<form method="POST">
    <label>Mật khẩu mới:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Xác nhận mật khẩu:</label><br>
    <input type="password" name="confirm" required><br><br>

    <button type="submit">Đặt lại mật khẩu</button>
</form>
<?php endif; ?>
</div>
</body>
</html>
