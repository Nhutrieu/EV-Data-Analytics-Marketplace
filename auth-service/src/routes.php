<?php
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

return function(string $method, string $uri) {
    // normalize
    $uri = rtrim($uri, '/');

    // public routes
    if ($uri === '/api/auth/register' && $method === 'POST') {
        (new AuthController())->register();
        return;
    }
    if ($uri === '/api/auth/login' && $method === 'POST') {
        (new AuthController())->login();
        return;
    }

    // protected routes
    if ($uri === '/api/auth/profile' && $method === 'GET') {
        $mw = new AuthMiddleware();
        $user = $mw->handle();
        if (!$user) return; // middleware will handle response
        (new AuthController())->profile($user);
        return;
    }

    // default 404
    http_response_code(404);
    echo json_encode(['status' => false, 'message' => 'Not Found']);
};
