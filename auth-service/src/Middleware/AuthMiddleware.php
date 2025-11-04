<?php
namespace Src\Middleware;

use Src\Helpers\ResponseHelper;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    private static $secretKey = 'SECRET_KEY';

    public static function checkToken() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            ResponseHelper::json(["error" => "Thiếu token"], 401);
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            return JWT::decode($token, new Key(self::$secretKey, 'HS256'));
        } catch (\Exception $e) {
            ResponseHelper::json(["error" => "Token không hợp lệ: ".$e->getMessage()], 401);
        }
    }

    public static function getUserFromToken() {
        return self::checkToken();
    }
}
