<?php
session_start();
require_once 'db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin']) || ($_SESSION['admin']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy id user cần chỉnh sửa
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: manage_users.php');
    exit;
}

// Lấy dữ liệu user hiện tại
$id = intval($id);
$result = mysqli_query($conn, "SELECT id, username, email, role FROM users WHERE id = $id");
$user = mysqli_fetch_assoc($result);

if (!$user) {
    $_SESSION['msg'] = "Người dùng không tồn tại.";
    header('Location: manage_users.php');
    exit;
}

// Xử lý form khi submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role    = mysqli_real_escape_string($conn, $_POST['role']);

    $sql = "UPDATE users SET username='$username', email='$email', role='$role' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['msg'] = "Cập nhật thành công.";
        header('Location: manage_users.php');
        exit;
    } else {
        $error = "Lỗi: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chỉnh sửa người dùng</title>
<link rel="stylesheet" href="../public/assets/css/home.css">
</head>
<body>
<div class="container">
<h2>Chỉnh sửa người dùng</h2>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    <label>Username:</label><br>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

    <label>Role:</label><br>
    <select name="role">
        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select><br><br>

    <button type="submit">Cập nhật</button>
    <a href="manage_users.php">Hủy</a>
</form>
</div>
</body>
</html>
