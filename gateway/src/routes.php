<?php
use App\Controllers\GatewayController;

$controller = new GatewayController();

// Định tuyến request theo URI
switch ($uri) {
    case '/api/auth':
    case (preg_match('#^/api/auth/.*#', $uri) ? true : false):
        $controller->forwardToAuthService();
        break;

    case '/api/data':
    case (preg_match('#^/api/data/.*#', $uri) ? true : false):
        $controller->forwardToDataService();
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
        break;
}
