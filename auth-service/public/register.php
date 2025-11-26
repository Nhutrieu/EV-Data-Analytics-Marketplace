<?php
session_start();

// Nếu đã đăng nhập thì điều hướng theo vai trò
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? 'user';
    switch ($role) {
        case 'admin': header('Location: admin.php'); break;
        case 'provider': header('Location: provider.php'); break;
        default: header('Location: home_logged.php'); break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - EV Data Marketplace</title>
    <link rel="stylesheet" href="assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="login-card">
    <h2>Đăng ký tài khoản</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form action="xulydangky.php" method="post">
        <div class="input-group">
            <i class="fa fa-user"></i>
            <input type="text" name="username" placeholder="Tên tài khoản" required>
        </div>
        <div class="input-group">
            <i class="fa fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" placeholder="Mật khẩu" required>
        </div>
        <div class="input-group">
            <i class="fa fa-user-tag"></i>
            <select name="role" required>
                <option value="user">Consumer</option>
                <option value="provider">Provider</option>
                <!-- <option value="admin" disabled>Admin (Chỉ dành cho quản trị hệ thống)</option> -->
            </select>
        </div>
        <button type="submit" class="btn-login">Đăng ký</button>
    </form>

    <div class="register-link">
        Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
    </div>
</div>
</body>
</html>
