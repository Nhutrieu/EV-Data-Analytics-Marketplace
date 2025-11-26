<?php
session_start();
require_once 'check_maintenance.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];

// TỰ NHẬN XAMPP / DOCKER
$server = getenv("DOCKER_ENV") ? "host.docker.internal" : "localhost";

$conn = mysqli_connect($server, "root", "", "ev-data-analytics-marketplace");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

$stmt = mysqli_prepare($conn, "
    SELECT id, name, source, size, tags, preview_url, image_url, description
    FROM datasets
    WHERE user_id = ?
    ORDER BY id DESC
");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$datasets = [];
while ($row = mysqli_fetch_assoc($result)) {
    $datasets[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Bộ dữ liệu của bạn — EV Data Marketplace</title>
<link rel="stylesheet" href="../public/assets/css/home.css">
<style>
.container {max-width:1200px;margin:0 auto;padding:20px;}
.add-new {margin-bottom:20px;}
.dataset-image {width:100%;height:200px;object-fit:cover;border-radius:10px;}
.grid {display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;}
.card {border:1px solid #ddd;padding:15px;border-radius:10px;background:#fff;display:flex;flex-direction:column;}
.card h4 {margin:0 0 10px 0;color:#1a1a1a;font-weight:600;}
.card .meta p {margin:3px 0;font-size:14px;color:#555;}
.actions {margin-top:auto;margin-top:10px;}
.actions .btn {display:inline-block;padding:6px 12px;margin-right:5px;border-radius:5px;text-decoration:none;color:#fff;}
.actions .btn.primary {background:#007bff;}
.actions .btn.secondary {background:#6c757d;}
.actions .btn.danger {background:#dc3545;}
</style>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container">
    <h1>Bộ dữ liệu của bạn</h1>
    <div class="add-new">
        <a class="btn primary" href="add_dataset.php">+ Thêm dataset mới</a>
    </div>

    <div class="grid">
        <?php if (empty($datasets)): ?>
            <p>Bạn chưa thêm dataset nào.</p>
        <?php else: ?>
            <?php foreach ($datasets as $data): ?>
                <div class="card">
                    <h4><?= htmlspecialchars($data['name']); ?></h4>
                    <img src="<?= htmlspecialchars($data['image_url'] ?: '../public/assets/images/demo.jpg'); ?>" class="dataset-image">
                    <div class="meta">
                        <p><strong>Nguồn:</strong> <?= htmlspecialchars($data['source'] ?: 'Chưa rõ'); ?></p>
                        <p><strong>Kích thước:</strong> <?= htmlspecialchars($data['size'] ?: 'N/A'); ?></p>
                        <p><strong>Tags:</strong> <?= htmlspecialchars($data['tags'] ?: 'Không có'); ?></p>
                    </div>
                    <p><?= htmlspecialchars($data['description'] ?: ''); ?></p>
                    <div class="actions">
                        <a class="btn primary" href="<?= htmlspecialchars($data['preview_url'] ?: '#'); ?>" target="_blank">Xem chi tiết</a>
                        <a class="btn secondary" href="edit_dataset.php?id=<?= $data['id']; ?>">Chỉnh sửa</a>
                        <a class="btn danger" href="delete_dataset.php?id=<?= $data['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa dataset này?');">Xóa</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
