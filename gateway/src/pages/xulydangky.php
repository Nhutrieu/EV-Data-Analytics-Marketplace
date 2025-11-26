<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    if (!in_array($role, ['user', 'provider'])) {
        $_SESSION['error'] = "Vai trò không hợp lệ.";
        header('Location: register.php');
        exit;
    }

    if ($role === 'user') {
        // Kiểm tra trùng email
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['error'] = "Email đã được sử dụng.";
            header('Location: register.php');
            exit;
        }

        $sql = "INSERT INTO users (username, email, password, role)
                VALUES ('$username', '$email', '$password', '$role')";
    } else {
        // Kiểm tra trùng email trong providers
        $check = mysqli_query($conn, "SELECT * FROM providers WHERE email='$email' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['error'] = "Email đã được sử dụng.";
            header('Location: register.php');
            exit;
        }

        $sql = "INSERT INTO providers (name, email, password)
                VALUES ('$username', '$email', '$password')";
    }

    if (mysqli_query($conn, $sql)) {
        echo "<script>
            alert('Đăng ký thành công! Bạn có thể đăng nhập ngay.');
            window.location.href = 'login.php';
        </script>";
        exit;
    } else {
        $_SESSION['error'] = 'Đăng ký thất bại: ' . mysqli_error($conn);
        header('Location: register.php');
        exit;
    }
}
?>
