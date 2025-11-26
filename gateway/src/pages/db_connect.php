<?php
// Tự động phân biệt XAMPP vs Docker
$server = getenv("DOCKER_ENV") ? "host.docker.internal" : "localhost";

$username = "root";
$password = ""; 
$dbname = "ev-data-analytics-marketplace";

$conn = mysqli_connect($server, $username, $password, $dbname);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
?>
