<?php
session_start();
require_once 'check_maintenance.php';
if(!isset($_SESSION['user'])){
    header("Location: login.php"); exit;
}

$conn = mysqli_connect('localhost','root','','ev-data-analytics-marketplace');
$user_email = $_SESSION['user']['email'];
$dataset_id = (int)($_GET['id'] ?? 0);

if($dataset_id > 0){
    mysqli_query($conn, "DELETE FROM user_dashboard WHERE user_email='$user_email' AND dataset_id=$dataset_id");
}

header("Location: dashboard.php");
exit;
?>
