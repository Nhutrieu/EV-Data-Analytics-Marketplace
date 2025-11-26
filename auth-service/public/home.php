<?php
session_start();
require_once 'db_connect.php';
require_once '../src/User.php';

$isLoggedIn = isset($_SESSION['user']);
$username = $isLoggedIn ? $_SESSION['user']['username'] : '';
$role = $isLoggedIn ? $_SESSION['user']['role'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EV Data Marketplace</title>
<link rel="stylesheet" href="assets/css/home.css">
<style>
/* ===== NỀN & HERO ===== */
body {
  margin: 0;
  min-height: 100vh;
  background: linear-gradient(180deg, #0f1724, #1a202c);
  transition: background 2s ease;
  font-family: 'Inter', sans-serif;
}

.hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  margin-top: 36px;
  padding: 0 28px;
}
.hero-text {
  flex: 1 1 400px;
}
.hero-text h1 {
  font-size: 32px;
  margin-bottom: 12px;
}
.hero-text p {
  color: #9aa4b2;
  margin-bottom: 20px;
}
.hero-image img {
  max-width: 500px;
  border-radius: 20px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.5);
  transition: transform 0.3s ease, opacity 1s ease;
  opacity: 1;
}

/* ===== CARDS ===== */
.grid {
  display: flex;
  gap: 14px;
  margin-top: 16px;
}
.card {
  background: #0b1220;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  text-align: center;
  flex: 1;
  transition: transform 0.3s ease;
}
.card:hover {
  transform: translateY(-5px);
}
.card img {
  max-width: 100%;
  border-radius: 12px;
  transition: transform 0.3s ease, filter 0.3s ease;
}
.card img:hover {
  transform: scale(1.05);
  filter: brightness(1.1);
}

/* Buttons */
.btn.primary {
  background: #06b6d4;
  color: #04141a;
  padding: 10px 16px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: bold;
}
</style>
</head>
<body>
<header>
    <div class="logo">EV Data Marketplace</div>
    <nav>
        <a href="home.php" class="active">Trang chủ</a>
        <?php if($isLoggedIn): ?>
            <?php if($role === 'provider'): ?>
                <a href="provider_dashboard.php">Dashboard Provider</a>
            <?php elseif($role === 'admin'): ?>
                <a href="admin.php">Dashboard Admin</a>
            <?php endif; ?>
            <form action="logout.php" method="POST" style="display:inline;">
                <button type="submit">Đăng xuất (<?= htmlspecialchars($username) ?>)</button>
            </form>
        <?php else: ?>
            <a href="login.php">Đăng nhập</a>
            <a href="register.php">Đăng ký</a>
        <?php endif; ?>
    </nav>
</header>

<div class="hero">
    <div class="hero-text">
        <h1>Chào mừng đến với EV Data Marketplace</h1>
        <p>Nền tảng quản lý và chia sẻ dữ liệu xe điện thông minh.</p>
        <?php if(!$isLoggedIn): ?>
        <a href="register.php" class="btn primary">Đăng ký ngay</a>
        <?php endif; ?>
    </div>
    <div class="hero-image">
        <img id="hero-img" src="assets/img/ev_car.png" alt="EV Data" />
    </div>
</div>

<section class="marketplace section">
    <div class="title">
        <h2>Bộ sưu tập dữ liệu nổi bật</h2>
        <a href="datasets.php" class="btn">Xem tất cả</a>
    </div>
    <div class="grid">
        <div class="card">
            <img class="dataset-img" src="assets/img/home1.png" alt="Dataset 1">
            <h4>EV Charging Stations</h4>
            <p class="meta">Cập nhật: 2025</p>
        </div>
        <div class="card">
            <img class="dataset-img" src="assets/img/home.png" alt="Dataset 2">
            <h4>Battery Performance</h4>
            <p class="meta">Cập nhật: 2025</p>
        </div>
        <div class="card">
            <img class="dataset-img" src="assets/img/home2.png" alt="Dataset 3">
            <h4>EV Market Trends</h4>
            <p class="meta">Cập nhật: 2025</p>
        </div>
    </div>
</section>

<script>
// ===== Hero Image & Background động =====
const heroImages = [
    'assets/img/ev_car.png',
    'assets/img/home1.png',
    'assets/img/home2.png'
];
const gradients = [
    ['#0f1724','#1a202c'],
    ['#081427','#0f1c2c'],
    ['#0a1b2d','#0c1f33']
];
let heroIndex = 0;
const heroImgEl = document.getElementById('hero-img');

setInterval(() => {
    heroIndex = (heroIndex + 1) % heroImages.length;
    
    // Fade hero
    heroImgEl.style.opacity = 0;
    setTimeout(() => {
        heroImgEl.src = heroImages[heroIndex];
        heroImgEl.style.opacity = 1;
    }, 500);

    // Background gradient
    document.body.style.background = `linear-gradient(180deg, ${gradients[heroIndex][0]}, ${gradients[heroIndex][1]})`;
}, 5000);

// ===== Cards Slide động =====
const cardImgs = document.querySelectorAll('.dataset-img');
const cardImagesArray = [
    ['assets/img/home1.png','assets/img/home.png','assets/img/home2.png'],
    ['assets/img/home.png','assets/img/home2.png','assets/img/home1.png'],
    ['assets/img/home2.png','assets/img/home1.png','assets/img/home.png']
];
let cardIndex = 0;

setInterval(() => {
    cardIndex = (cardIndex + 1) % cardImagesArray[0].length;
    cardImgs.forEach((img, i) => {
        img.style.opacity = 0;
        setTimeout(() => {
            img.src = cardImagesArray[i][cardIndex];
            img.style.opacity = 1;
        }, 300);
    });
}, 6000);
</script>
</body>
</html>
