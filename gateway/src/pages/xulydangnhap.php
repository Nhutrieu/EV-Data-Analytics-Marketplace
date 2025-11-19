<?php
session_start();
require_once 'db_connect.php'; // Kết nối database

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $emailOrUsername = trim($_POST['email']);
    $password = $_POST['password'];

    // ======= ADMIN =======
    $sqlAdmin = "SELECT * FROM admin WHERE email=? OR username=? LIMIT 1";
    $stmtAdmin = mysqli_prepare($conn, $sqlAdmin);
    mysqli_stmt_bind_param($stmtAdmin, "ss", $emailOrUsername, $emailOrUsername);
    mysqli_stmt_execute($stmtAdmin);
    $resAdmin = mysqli_stmt_get_result($stmtAdmin);

    if($resAdmin && mysqli_num_rows($resAdmin) > 0){
        $admin = mysqli_fetch_assoc($resAdmin);
        // Nếu DB dùng bcrypt
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = [
                'idAdmin' => $admin['idAdmin'],
                'tenAdmin' => $admin['tenAdmin'],
                'username' => $admin['username'] ?? $admin['tenAdmin'],
                'email' => $admin['email'],
                'role' => 'admin',
                'Quyen' => $admin['Quyen']
            ];
            header('Location: admin.php');
            exit;
        }
        // Nếu DB dùng plaintext (tạm thời)
        elseif ($password === $admin['password']) {
            $_SESSION['admin'] = [
                'idAdmin' => $admin['idAdmin'],
                'tenAdmin' => $admin['tenAdmin'],
                'username' => $admin['username'] ?? $admin['tenAdmin'],
                'email' => $admin['email'],
                'role' => 'admin',
                'Quyen' => $admin['Quyen']
            ];
            header('Location: admin.php');
            exit;
        }
    }

    // ========== USER ==========
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? OR username=? LIMIT 1");
    $stmt->bind_param("ss", $emailOrUsername, $emailOrUsername);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            header('Location: home_logged.php');
            exit;
        }
    }

    // ========== PROVIDER ==========
    $stmt = $conn->prepare("SELECT * FROM providers WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $emailOrUsername);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $prov = $res->fetch_assoc();
        if (password_verify($password, $prov['password'])) {
            $_SESSION['user'] = [
                'id' => $prov['id'],
                'username' => $prov['name'],
                'role' => 'provider'
            ];
            header('Location: provider_dashboard.php');
            exit;
        }
    }

    // ========== SAI THÔNG TIN ==========
    $_SESSION['error'] = "Email/Username hoặc mật khẩu không đúng.";
    header('Location: login.php');
    exit;
}
?>