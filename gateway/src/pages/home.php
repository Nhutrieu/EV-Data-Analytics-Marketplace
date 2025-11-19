<?php
session_start();
require_once 'db_connect.php';

// Kiểm tra xem user đã đăng nhập hay chưa
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
<link rel="stylesheet" href="../public/assets/css/home.css">
<style>
/* BODY & HEADER */
body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    color: #e6eef6;
    background: #071022;
}
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 28px;
    background: #0b1220;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.logo { font-size: 20px; font-weight: 700; color: #06b6d4; }
nav a, nav form button {
    color: #9aa4b2;
    text-decoration: none;
    margin-left: 20px;
    font-weight: 600;
    background: none;
    border: none;
    cursor: pointer;
}
nav a.active, nav a:hover, nav form button:hover { color: #06b6d4; }

/* CONTAINER */
.container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
}

/* HERO SECTION */
.hero {
    position: relative;
    height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    text-align: center;
}

.hero-slide {
    position: absolute;
    top:0; left:0; width:100%; height:100%;
    background-size: cover;
    background-position: center;
    filter: brightness(0.5);
    opacity: 0;
    transition: opacity 1.5s ease-in-out;
}

.hero-slide.active { opacity: 1; }

.hero-content {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.hero h1 {
    font-size: 48px;
    margin-bottom: 20px;
    color: #06b6d4;
    text-shadow: 0 2px 8px rgba(0,0,0,0.6);
}

.hero p {
    font-size: 20px;
    color: #e2e8f0;
    line-height: 1.6;
    text-shadow: 0 1px 5px rgba(0,0,0,0.5);
}

/* RESPONSIVE */
@media(max-width: 768px) {
    .hero h1 { font-size: 36px; }
    .hero p { font-size: 16px; }
    .hero { height: 400px; }
}

@media(max-width: 480px) {
    .hero h1 { font-size: 28px; }
    .hero p { font-size: 14px; }
    .hero { height: 300px; }
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

<div class="container">
    <section class="hero">
        <!-- Các slide hình nền -->
        <div class="hero-slide" style="background-image: url('../public/assets/img/home.png');"></div>
        <div class="hero-slide" style="background-image: url('../public/assets/img/home1.png');"></div>
        <div class="hero-slide" style="background-image: url('../public/assets/img/home2.png');"></div>

        <!-- Nội dung nổi trên hình -->
        <div class="hero-content">
            <h1>EV Data Analytics</h1>
            <p>Khám phá, quản lý và chia sẻ dữ liệu xe điện một cách thông minh.  
            Nền tảng EV Data Marketplace giúp bạn truy cập các bộ dữ liệu phong phú, phân tích trực quan và đưa ra quyết định dựa trên dữ liệu.</p>
        </div>
    </section>
</div>

<script>
// JavaScript: tự động chuyển slide
let slides = document.querySelectorAll('.hero-slide');
let current = 0;

function showSlide(index) {
    slides.forEach((slide, i) => slide.classList.toggle('active', i === index));
}

showSlide(current);

setInterval(() => {
    current = (current + 1) % slides.length;
    showSlide(current);
}, 5000); // 5 giây chuyển ảnh
</script>

</body>
</html>
