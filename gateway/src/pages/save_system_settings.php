<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = mysqli_connect('127.0.0.1', 'root', '', 'ev-data-analytics-marketplace');
if (!$conn) {
    die("Không thể kết nối CSDL: " . mysqli_connect_error());
}

$system_name = $_POST['system_name'] ?? '';
$system_description = $_POST['system_description'] ?? '';
$support_email = $_POST['support_email'] ?? '';
$maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;

// Upload logo (nếu có)
$logo_url = '';
if (!empty($_FILES['logo']['name'])) {
    $target_dir = "../public/assets/uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $filename = basename($_FILES["logo"]["name"]);
    $target_file = $target_dir . time() . "_" . $filename;
    if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
        $logo_url = str_replace('../public', '', $target_file);
    }
}

// Lưu vào CSDL
$sql = "UPDATE system_settings SET 
            system_name='$system_name',
            system_description='$system_description',
            support_email='$support_email',
            maintenance_mode=$maintenance_mode" .
            (!empty($logo_url) ? ", logo_url='$logo_url'" : "") . "
        WHERE id=1";

mysqli_query($conn, $sql);

// Ghi log hoạt động
$adminName = $_SESSION['admin']['tenAdmin'] ?? 'Admin';
$log = "[".date('Y-m-d H:i:s')."] $adminName cập nhật cấu hình hệ thống.\n";
file_put_contents("../logs/admin_actions.log", $log, FILE_APPEND);

// Quay lại
header("Location: system_settings.php?saved=1");
exit;
?>
