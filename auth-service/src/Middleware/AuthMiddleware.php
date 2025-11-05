<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Helpers\ResponseHelper;

class AuthMiddleware {
    public static function verify() {
        $headers = getallheaders();
        if (!isset($headers["Authorization"])) {
            ResponseHelper::json(["error" => "Missing token"], 401);
        }

        $token = str_replace("Bearer ", "", $headers["Authorization"]);

        try {
            $decoded = JWT::decode($token, new Key("SECRET_KEY", 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            ResponseHelper::json(["error" => "Invalid token"], 401);
        }
    }
}
