<?php
use App\Controllers\AuthController;

$uri = $_SERVER["REQUEST_URI"];
$method = $_SERVER["REQUEST_METHOD"];
$auth = new AuthController();

if ($uri == "/register" && $method == "POST") {
    $auth->register();
} elseif ($uri == "/login" && $method == "POST") {
    $auth->login();
} else {
    echo "Auth service running...";
}
