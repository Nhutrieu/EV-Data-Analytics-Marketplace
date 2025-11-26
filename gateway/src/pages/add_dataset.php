<?php
require_once 'check_maintenance.php';
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];  // ID user

$conn = mysqli_connect('localhost', 'root', '', 'ev-data-analytics-marketplace');
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

$success = $error = "";

// Khi gửi form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $source = mysqli_real_escape_string($conn, $_POST['source']);
    $size = mysqli_real_escape_string($conn, $_POST['size']);
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);
    $preview_url = mysqli_real_escape_string($conn, $_POST['preview_url']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "INSERT INTO datasets (user_id, name, source, size, tags, preview_url, image_url, description)
            VALUES ($userId, '$name', '$source', '$size', '$tags', '$preview_url', '$image_url', '$description')";

    if (mysqli_query($conn, $sql)) {
        $success = "✅ Dataset đã được thêm thành công!";
    } else {
        $error = "❌ Lỗi: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thêm Dataset Mới</title>
<link rel="stylesheet" href="../public/assets/css/home.css">
<style>
form {max-width:700px;margin:30px auto;padding:20px;background:#fff;border-radius:12px;box-shadow:0 3px 10px rgba(0,0,0,0.1);}
form input, form textarea {width:100%;padding:10px;margin:8px 0;border:1px solid #ddd;border-radius:6px;font-size:15px;}
form button {background:#2c7be5;color:#fff;border:none;padding:10px 18px;border-radius:6px;cursor:pointer;font-weight:bold;}
form button:hover {background:#1b6cd8;}
.message {text-align:center;margin-bottom:10px;}
</style>
</head>
<body>
<?php include 'header.php'; ?>
<main class="container">
    <h1>➕ Thêm Dataset Mới</h1>

    <?php if ($success) echo "<p class='message' style='color:green;'>$success</p>"; ?>
    <?php if ($error) echo "<p class='message' style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>Tên dataset:</label>
        <input type="text" name="name" required>

        <label>Nguồn dữ liệu:</label>
        <input type="text" name="source">

        <label>Kích thước (VD: 3.2 MB):</label>
        <input type="text" name="size">

        <label>Tags (phân cách bởi dấu phẩy):</label>
        <input type="text" name="tags">

        <label>URL xem trước:</label>
        <input type="text" name="preview_url">

        <label>URL hình ảnh:</label>
        <input type="text" name="image_url">

        <label>Mô tả dataset:</label>
        <textarea name="description" rows="5"></textarea>

        <button type="submit">💾 Thêm Dataset</button>
    </form>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
