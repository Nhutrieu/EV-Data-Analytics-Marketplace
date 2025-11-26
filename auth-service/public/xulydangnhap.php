<?php
session_start();
require_once 'db_connect.php'; // Kết nối PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username'] ?? $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($usernameOrEmail) || empty($password)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin.";
        header('Location: login.php');
        exit;
    }

    try {
        // ===== ADMIN =====
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username=? OR email=? LIMIT 1");
$stmt->execute([$usernameOrEmail, $usernameOrEmail]);
if ($stmt->rowCount() > 0) {
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Nếu mật khẩu trong DB đã hash
    if (password_verify($password, $admin['password'])) {
        $_SESSION['user'] = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'role' => 'admin'
        ];
        header('Location: http://localhost:8004/admin_dashboard.php');
        exit;
    }

    // Nếu mật khẩu trong DB chưa hash (ví dụ "123456"), test tạm thời
    if ($password === $admin['password']) {
        // Hash mật khẩu ngay lần login đầu tiên
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE admins SET password=? WHERE id=?");
        $update->execute([$hashed, $admin['id']]);

        $_SESSION['user'] = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'role' => 'admin'
        ];
        header('Location: http://localhost:8004/admin_dashboard.php');
        exit;
    }
}

        // ===== PROVIDER =====
        $stmt = $pdo->prepare("SELECT * FROM providers WHERE username=? OR email=? LIMIT 1");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        if ($stmt->rowCount() > 0) {
            $prov = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $prov['password'])) {
                $_SESSION['user'] = [
                    'id' => $prov['id'],
                    'username' => $prov['username'],
                    'role' => 'provider'
                ];
                header('Location: http://localhost:8009/frontend/data.html');
                exit;
            }
        }

        // ===== CONSUMER =====
        $stmt = $pdo->prepare("SELECT * FROM consumers WHERE username=? OR email=? LIMIT 1");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        if ($stmt->rowCount() > 0) {
            $consumer = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $consumer['password'])) {
                $_SESSION['user'] = [
                    'id' => $consumer['id'],
                    'username' => $consumer['username'],
                    'role' => 'user'
                ];
                header('Location: http://localhost:8008/public/consumer.html');
                exit;
            }
        }

        // ===== Thông tin không đúng =====
        $_SESSION['error'] = "Username/Email hoặc mật khẩu không đúng.";
        header('Location: login.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Lỗi hệ thống: " . $e->getMessage();
        header('Location: login.php');
        exit;
    }
}
