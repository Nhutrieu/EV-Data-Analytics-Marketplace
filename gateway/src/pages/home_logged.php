<?php
session_start();
require_once 'check_maintenance.php';
if(!isset($_SESSION['user'])){
    header('Location: login.php');
    exit;
}

require_once 'db_connect.php';
$user = $_SESSION['user'];

// Lấy 6 dataset nổi bật
$sql = "SELECT id, name, image_url, source, size, description 
        FROM datasets 
        WHERE is_featured = 1 
        ORDER BY id DESC 
        LIMIT 6";
$result = mysqli_query($conn, $sql);
$featured_datasets = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $featured_datasets[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>EV Data Marketplace - Dashboard</title>
<link rel="stylesheet" href="../public/assets/css/home.css"> <!-- CSS tương tự React -->
</head>
<body>
<div class="container">

    <!-- Header -->
    <header>
        <div class="logo">
            <div class="mark">EV</div>
            <div>
                <div class="title">EV Data Marketplace</div>
                <div class="subtitle">Chợ dữ liệu & công cụ phân tích xe điện</div>
            </div>
        </div>
        <nav>
            <a href="home_logged.php">Trang chủ</a>
            <a href="http://localhost:8009/public/consumer.html">Consumer Portal</a>

            <a href="datasets.php">Bộ sưu tập dữ liệu</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="contact.php">Liên hệ</a>
            <a href="logout.php" class="cta logout">Đăng xuất (<?= htmlspecialchars($user['username']) ?>)</a>
        </nav>
    </header>

    <!-- Hero -->
    <main>
        <section class="hero">
            <div class="hero-card">
                <h1>Chợ dữ liệu phân tích xe điện</h1>
                <p>Truy cập dataset về doanh số, trạm sạc, pin, phát thải và báo cáo phân tích — dành cho nhà phân tích, startup, doanh nghiệp.</p>
                <div class="stats">
                    <div class="stat">
                        <h3>12.4M</h3>
                        <p>Xe điện (lũy kế)</p>
                    </div>
                    <div class="stat">
                        <h3>8,250</h3>
                        <p>Trạm sạc (quốc gia)</p>
                    </div>
                    <div class="stat">
                        <h3><?= count($featured_datasets) ?></h3>
                        <p>Datasets nổi bật</p>
                    </div>
                </div>
                <div class="actions">
                    <a class="btn primary" href="datasets.php">Duyệt dữ liệu</a>
                    <a class="btn" href="dashboard.php">Mở dashboard</a>
                </div>
            </div>

            <aside class="preview">
                <div class="preview-header">
                    <div class="name">Preview: Doanh số EV theo quốc gia</div>
                    <div class="year">2020 - 2025</div>
                </div>
                <div class="chart-placeholder">
                    <img src="../public/assets/img/chart-demo.svg" alt="Biểu đồ demo">
                </div>
                <div class="quick-actions">
                    <a class="btn" href="datasets.php">Xem chi tiết</a>
                    <a class="btn" href="dashboard.php">Thêm vào dashboard</a>
                    <a class="btn" href="reports.php">So sánh</a>
                </div>
            </aside>
        </section>

        <!-- Featured datasets -->
        <section id="market" class="section">
            <div class="title">
                <h2>Bộ sưu tập dữ liệu nổi bật</h2>
                <div class="more">
                    <a href="datasets.php">Xem tất cả</a>
                </div>
            </div>
            <?php if(empty($featured_datasets)): ?>
                <p>Hiện chưa có dataset nổi bật.</p>
            <?php else: ?>
            <div class="grid">
                <?php foreach($featured_datasets as $data): ?>
                    <div class="card">
                        <h4><?= htmlspecialchars($data['name']) ?></h4>
                        <div class="meta">
                            Nguồn: <?= htmlspecialchars($data['source']) ?> • <?= htmlspecialchars($data['size']) ?>
                        </div>
                        <div class="tags">
                            <div class="tag">dataset</div>
                            <div class="tag">EV</div>
                        </div>
                        <div class="actions">
                            <a class="btn primary" href="datasets.php?id=<?= $data['id'] ?>">Tải xuống</a>
                            <a class="btn" href="datasets.php?id=<?= $data['id'] ?>">Xem</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>

        <!-- Features -->
        <section id="dashboard" class="section">
            <div class="features-header">
                <div>
                    <h3>Tính năng nổi bật</h3>
                    <p>Dashboard tương tác, export, API truy vấn, và meta rõ ràng.</p>
                </div>
                <div class="btns">
                    <a class="btn" href="api-docs.php">API Docs</a>
                    <a class="btn primary" href="dashboard.php">Bắt đầu</a>
                </div>
            </div>
            <div class="grid features">
                <div class="card">
                    <strong>API Truy vấn</strong>
                    <div class="meta">Trả về CSV/JSON</div>
                </div>
                <div class="card">
                    <strong>Preview dữ liệu</strong>
                    <div class="meta">Xem nhanh trước khi tải</div>
                </div>
                <div class="card">
                    <strong>Hợp đồng dữ liệu</strong>
                    <div class="meta">Quyền sử dụng rõ ràng</div>
                </div>
                <div class="card">
                    <strong>Báo cáo tự động</strong>
                    <div class="meta">Export PDF/Excel</div>
                </div>
            </div>
        </section>
    </main>
<?php include 'footer.php'; ?>
</div>
</body>
</html>
