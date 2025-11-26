<?php
session_start();
require_once 'check_maintenance.php';
require_once 'db_connect.php';

// Kiểm tra role provider
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'provider'){
    header('Location: login.php');
    exit;
}

// Lấy thông tin provider
$providerName = $_SESSION['user']['username'];
$providerId = $_SESSION['user']['id'];

// Thống kê cơ bản
$totalDatasets = 0;
$totalDownloads = 0;

$sqlStats = "SELECT COUNT(*) as total, SUM(download_count) as downloads 
             FROM datasets 
             WHERE provider_id = ?";
$stmt = $conn->prepare($sqlStats);
$stmt->bind_param("i", $providerId);
$stmt->execute();
$resultStats = $stmt->get_result();
if($row = $resultStats->fetch_assoc()){
    $totalDatasets = $row['total'];
    $totalDownloads = $row['downloads'] ?? 0;
}
$stmt->close();

// Lấy 6 dataset demo
$sql = "SELECT id, name, image_url, source, size, description 
        FROM datasets 
        WHERE provider_id = ?
        ORDER BY id DESC 
        LIMIT 6";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $providerId);
$stmt->execute();
$result = $stmt->get_result();
$featured_datasets = [];
while($row = $result->fetch_assoc()){
    $featured_datasets[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Provider Dashboard</title>
<link rel="stylesheet" href="../public/assets/css/home.css">
<style>
/* Cơ bản cho dashboard */
body { font-family: Arial, sans-serif; margin:0; padding:0; background:#f4f4f4; }
header { background:#111; color:#fff; padding:15px; display:flex; justify-content:space-between; align-items:center; }
header .logo { font-weight:bold; font-size:1.2em; }
header nav a { color:#fff; margin-left:15px; text-decoration:none; }
.hero { background:#fffae6; padding:30px; text-align:center; }
.stats { display:flex; justify-content:center; margin:20px 0; gap:20px; }
.stats .card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 5px rgba(0,0,0,0.1); text-align:center; flex:1; }
.datasets { padding:20px; }
.datasets .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px; }
.datasets .card { background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
.datasets img { width:100%; height:150px; object-fit:cover; }
.card-content { padding:15px; }
.card-content h3 { margin:0 0 10px; }
.card-content p { margin:5px 0; }
.card-content a { display:inline-block; margin-top:10px; color:#007BFF; text-decoration:none; }
</style>
</head>
<body>
<header>
    <div class="logo">EV Data Marketplace</div>
    <nav>
        <a href="provider_dashboard.php">Trang chủ</a>
        <a href="upload_dataset.php">Tải dataset</a>
        <a href="my_datasets.php">Dataset của tôi</a>
        <a href="logout.php">Đăng xuất (<?= htmlspecialchars($providerName) ?>)</a>
    </nav>
</header>

<section class="hero">
  <h1>Chào, <?= htmlspecialchars($providerName) ?> ⚡</h1>
  <p>Quản lý, tải lên và chia sẻ datasets của bạn một cách nhanh chóng.</p>
</section>

<section class="stats">
    <div class="card">
        <h2><?= $totalDatasets ?></h2>
        <p>Tổng số dataset</p>
    </div>
    <div class="card">
        <h2><?= $totalDownloads ?></h2>
        <p>Tổng lượt tải</p>
    </div>
</section>

<section class="datasets">
  <h2>Danh sách dataset nổi bật</h2>
  <div class="grid">
  <?php if(empty($featured_datasets)): ?>
    <p>Hiện chưa có dữ liệu.</p>
  <?php else: ?>
    <?php foreach($featured_datasets as $data): ?>
      <div class="card">
        <img src="<?= htmlspecialchars($data['image_url'] ?: '../public/assets/images/demo.jpg') ?>" onerror="this.src='../public/assets/images/demo.jpg'" alt="Dataset">
        <div class="card-content">
          <h3><?= htmlspecialchars($data['name']) ?></h3>
          <p><?= htmlspecialchars(substr($data['description'],0,100)) ?>...</p>
          <p><strong>Nguồn:</strong> <?= htmlspecialchars($data['source']) ?></p>
          <p><strong>Kích thước:</strong> <?= htmlspecialchars($data['size']) ?></p>
          <a href="dataset_detail.php?id=<?= $data['id'] ?>">Xem chi tiết →</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>
</section>
</body>
</html>
