<?php
session_start();

// Nếu đã đăng nhập, chuyển hướng về trang tương ứng
if (isset($_SESSION['user']) || isset($_SESSION['admin'])) {
    if (isset($_SESSION['admin'])) {
        header('Location: admin.php'); exit;
    }
    $role = $_SESSION['user']['role'] ?? 'user';
    if ($role === 'provider') header('Location: provider_dashboard.php');
    else header('Location: home_logged.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - EV Data Marketplace</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="login-card">
    <h2>Đăng nhập</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form action="xulydangnhap.php" method="post">
        <div class="input-group">
            <i class="fa fa-user"></i>
            <input type="text" name="email" placeholder="Email hoặc Username" required>
        </div>
        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" placeholder="Mật khẩu" required>
        </div>
        <button type="submit" class="btn-login">Đăng nhập</button>
    </form>

    <div class="register">
        Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
    </div>
</div>
</body>
</html>
