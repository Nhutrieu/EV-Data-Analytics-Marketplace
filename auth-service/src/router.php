<?php
use Src\Controllers\AuthController;
use Src\Middleware\AuthMiddleware;

// Autoload namespace
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/Helpers/ResponseHelper.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$auth = new AuthController();

if ($uri === '/auth/login' && $method === 'POST') {
    $auth->login();
} elseif ($uri === '/auth/register' && $method === 'POST') {
    $auth->register();
} elseif ($uri === '/auth/me' && $method === 'GET') {
    AuthMiddleware::checkToken();
    $auth->me();
} elseif (strpos($uri, '/auth/social-login') === 0 && $method === 'GET') {
    $auth->socialLogin();
} else {
    http_response_code(404);
    echo json_encode(["error" => "Không tìm thấy endpoint"]);
}
