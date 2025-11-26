<?php
session_start();
require_once 'db_connect.php'; // PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Kiểm tra dữ liệu rỗng
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin.";
        header('Location: register.php');
        exit;
    }

    // Kiểm tra role hợp lệ
    if (!in_array($role, ['user', 'provider'])) {
        $_SESSION['error'] = "Vai trò không hợp lệ.";
        header('Location: register.php');
        exit;
    }

    // Hash mật khẩu
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        if ($role === 'user') {
            // Kiểm tra trùng username/email trong consumers
            $stmt = $pdo->prepare("SELECT * FROM consumers WHERE username=? OR email=? LIMIT 1");
            $stmt->execute([$username, $email]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Username hoặc Email đã được sử dụng.";
                header('Location: register.php');
                exit;
            }

            // Insert vào bảng consumers
            $stmt = $pdo->prepare("INSERT INTO consumers (username, password, email) VALUES (?, ?, ?)");
            $stmt->execute([$username, $passwordHash, $email]);

        } else { // provider
            // Kiểm tra trùng username trong providers
            $stmt = $pdo->prepare("SELECT * FROM providers WHERE username=? LIMIT 1");
            $stmt->execute([$username]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Username đã được sử dụng.";
                header('Location: register.php');
                exit;
            }

            // Insert vào bảng providers
            $stmt = $pdo->prepare("INSERT INTO providers (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $passwordHash]);
        }

        $_SESSION['success'] = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
        header('Location: login.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Lỗi hệ thống: " . $e->getMessage();
        header('Location: register.php');
        exit;
    }
}
?>
