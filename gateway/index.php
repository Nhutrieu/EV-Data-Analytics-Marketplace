<?php
session_start();

// Nếu người dùng đã đăng nhập, chuyển thẳng sang home_logged.php
if (isset($_SESSION['user'])) {
    header("Location: src/pages/home_logged.php");
    exit;
}

// Nếu chưa đăng nhập, chuyển sang trang home.php (trang giới thiệu / đăng nhập)
header("Location: src/pages/home.php");
exit;
