<?php
session_start();
require_once 'db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Kiểm tra email tồn tại
    $result = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($result) > 0) {
        // Tạo token reset (mẫu)
        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $user = mysqli_fetch_assoc($result);
        $userId = $user['id'];

        // Lưu token vào db (giả sử có cột reset_token, reset_expires)
        mysqli_query($conn, "UPDATE users SET reset_token='$token', reset_expires='$expires' WHERE id=$userId");

        // Ở đây bạn sẽ gửi email với link reset, ví dụ: reset_password.php?token=$token
        $message = "Liên kết đặt lại mật khẩu đã được gửi tới email (giả lập). Token: $token";
    } else {
        $message = "Email không tồn tại trong hệ thống.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quên mật khẩu</title>
<link rel="stylesheet" href="../public/assets/css/home.css">
</head>
<body>
<div class="container">
<h2>Quên mật khẩu</h2>
<?php if ($message) echo "<p>$message</p>"; ?>
<form method="POST">
    <label>Nhập email của bạn:</label><br>
    <input type="email" name="email" required><br><br>
    <button type="submit">Gửi liên kết</button>
</form>
<a href="login.php">Quay lại đăng nhập</a>
</div>
</body>
</html>
