<?php
session_start();
require_once 'check_maintenance.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$conn = mysqli_connect('localhost', 'root', '', 'ev-data-analytics-marketplace');
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

$user_email = $_SESSION['user']['email'];
$dataset_id = $_GET['id'] ?? 0;

if ($dataset_id) {
    $check = mysqli_query($conn, "SELECT * FROM user_dashboard WHERE user_email='$user_email' AND dataset_id=$dataset_id");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO user_dashboard (user_email, dataset_id) VALUES ('$user_email', $dataset_id)");
    }
}

header("Location: dashboard.php");
exit;
?>
