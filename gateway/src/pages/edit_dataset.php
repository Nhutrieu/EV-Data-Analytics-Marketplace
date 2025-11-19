<?php
session_start();
require_once 'check_maintenance.php';
require_once 'db_connect.php';

// Kiểm tra admin
if (!isset($_SESSION['admin']) || ($_SESSION['admin']['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: manage_datasets.php");
    exit;
}

// Lấy thông tin dataset
$id_safe = mysqli_real_escape_string($conn, $id);
$dataset = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM datasets WHERE id='$id_safe'"));
if (!$dataset) {
    die("Dataset không tồn tại.");
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $source      = mysqli_real_escape_string($conn, $_POST['source'] ?? '');
    $size        = mysqli_real_escape_string($conn, $_POST['size'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Upload ảnh nếu có
    $image_url = $dataset['image_url'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "../public/assets/images/datasets/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image_url = "public/assets/images/datasets/" . $filename;
        }
    }

    mysqli_query($conn, "UPDATE datasets SET 
        name='$name',
        source='$source',
        size='$size',
        description='$description',
        image_url='$image_url',
        is_featured='$is_featured'
        WHERE id='$id_safe'");

    header("Location: manage_datasets.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sửa dataset</title>
<link rel="stylesheet" href="../public/assets/css/admin.css">
<style>
body { background:#0f1724; color:#e6eef6; font-family:'Inter', sans-serif; padding:20px;}
.container { max-width:500px; margin:50px auto; background:rgba(255,255,255,0.03); padding:30px; border-radius:12px;}
h2 { color:#06b6d4; margin-bottom:15px; }
label { display:block; margin:12px 0 4px; }
input, textarea { width:100%; padding:8px 10px; border-radius:6px; border:none; margin-bottom:10px; }
textarea { height:100px; resize:none; }
button { padding:10px 16px; border:none; border-radius:6px; background:#06b6d4; color:#fff; cursor:pointer; }
button:hover { opacity:0.8; }
img.preview { margin-top:6px; max-width:120px; border-radius:6px; display:block; }
</style>
</head>
<body>

<div class="container">
<h2>✏️ Sửa bộ dữ liệu</h2>
<form method="POST" enctype="multipart/form-data">
    <label>Tên bộ dữ liệu</label>
    <input type="text" name="name" value="<?= htmlspecialchars($dataset['name']) ?>" required>

    <label>Nguồn</label>
    <input type="text" name="source" value="<?= htmlspecialchars($dataset['source']) ?>">

    <label>Kích thước</label>
    <input type="text" name="size" value="<?= htmlspecialchars($dataset['size']) ?>">

    <label>Mô tả</label>
    <textarea name="description"><?= htmlspecialchars($dataset['description']) ?></textarea>

    <label>Ảnh hiện tại</label>
    <?php if(!empty($dataset['image_url'])): ?>
        <img src="../<?= htmlspecialchars($dataset['image_url']) ?>" class="preview">
    <?php else: ?>
        <p>Chưa có ảnh</p>
    <?php endif; ?>

    <label>Thay ảnh mới</label>
    <input type="file" name="image">

    <label>
        <input type="checkbox" name="is_featured" <?= $dataset['is_featured']?'checked':'' ?>> Nổi bật
    </label>

    <button type="submit">Cập nhật</button>
</form>
</div>

</body>
</html>
