<?php
session_start();
if (isset($_SESSION['user']) || isset($_SESSION['admin'])) {
    // Nếu đã đăng nhập thì chuyển về trang tương ứng
    if (isset($_SESSION['admin'])) {
        header('Location: admin.php');
        exit;
    }
    $role = $_SESSION['user']['role'] ?? 'user';
    switch ($role) {
        case 'provider':
            header('Location: provider_dashboard.php');
            break;
        default:
            header('Location: home_logged.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống</title>
    <link rel="stylesheet" href="../public/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="login-container">
    <h2>Đăng nhập tài khoản</h2>

    <!-- Thông báo lỗi / thành công -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form action="xulydangnhap.php" method="post">
        <div class="form-group">
            <label for="email">Email hoặc Username</label>
            <div class="input-box">
                <i class="fa fa-user"></i>
                <input type="text" name="email" id="email" placeholder="Nhập email hoặc username" required>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <div class="input-box">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Nhập mật khẩu" required>
            </div>
        </div>

        <button type="submit" class="login-btn">Đăng nhập</button>
    </form>

    <div class="register-link">
        Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
    </div>
</div>
</body>
</html>
