<?php
$conn = mysqli_connect('localhost', 'root', '', 'ev-data-analytics-marketplace');
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    mysqli_query($conn, "INSERT INTO contact_messages (name, email, message, created_at)
                         VALUES ('$name', '$email', '$message', NOW())");

    header("Location: contact.php?success=1");
    exit;
}
?>
