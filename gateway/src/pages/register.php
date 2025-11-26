<?php
session_start();
if (isset($_SESSION['user'])) {
    // Nếu đã đăng nhập thì điều hướng theo vai trò
    $role = $_SESSION['user']['role'] ?? 'user';
    switch ($role) {
        case 'admin':
            header('Location: admin.php');
            break;
        case 'provider':
            header('Location: provider.php');
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
    <title>Đăng ký tài khoản</title>
    <link rel="stylesheet" href="../public/assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="login-container">
    <h2>Đăng ký tài khoản</h2>

    <!-- Hiển thị thông báo -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form action="xulydangky.php" method="post">
        <div class="form-group">
            <label for="username">Tên tài khoản</label>
            <div class="input-box">
                <i class="fa fa-user"></i>
                <input type="text" name="username" id="username" placeholder="Nhập tên tài khoản" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <div class="input-box">
                <i class="fa fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Nhập email" required>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <div class="input-box">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Nhập mật khẩu" required>
            </div>
        </div>

        <div class="form-group">
            <label for="role">Vai trò</label>
            <div class="input-box">
                <i class="fa fa-user-tag"></i>
                <select name="role" id="role" required>
                    <option value="user">User</option>
                    <option value="provider">Provider</option>
                </select>
            </div>
        </div>

        <button type="submit" class="login-btn">Đăng ký</button>
    </form>

    <div class="register-link">
        Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
    </div>
</div>
</body>
</html>
