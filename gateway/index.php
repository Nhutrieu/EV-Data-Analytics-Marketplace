<?php
<<<<<<< HEAD
header("Content-Type: application/json");

$authServiceUrl = "http://auth-service:80/public/index.php?action=";

$path = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

if (in_array($path, ['login', 'register'])) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $authServiceUrl.$path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    echo $response;
} else {
    echo json_encode(['message'=>'Route not found']);
}
?>
=======
session_start();

// Nếu người dùng đã đăng nhập, chuyển thẳng sang home_logged.php
if (isset($_SESSION['user'])) {
    header("Location: src/pages/home_logged.php");
    exit;
}

// Nếu chưa đăng nhập, chuyển sang trang home.php (trang giới thiệu / đăng nhập)
header("Location: src/pages/home.php");
exit;
>>>>>>> b2b62b30502933ce69778733bd55f2cea706a8e3
