<?php
namespace App\Middleware;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public function handle(): ?array
    {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        if (!$auth) {
            ResponseHelper::json(['status' => false, 'message' => 'Không có token Authorization'], 401);
            return null;
        }

        if (preg_match('/Bearer\s(\S+)/', $auth, $matches) !== 1) {
            ResponseHelper::json(['status' => false, 'message' => 'Header Authorization không hợp lệ'], 401);
            return null;
        }

        $token = $matches[1];
        $secret = getenv('JWT_SECRET') ?: 'change_me';

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            $uid = $decoded->data->id ?? null;
            if (!$uid) {
                ResponseHelper::json(['status' => false, 'message' => 'Token không hợp lệ'], 401);
                return null;
            }

            $userModel = new User();
            $user = $userModel->findById((int)$uid);
            if (!$user) {
                ResponseHelper::json(['status' => false, 'message' => 'Người dùng không tồn tại'], 401);
                return null;
            }

            return $user;
        } catch (\Exception $e) {
            ResponseHelper::json(['status' => false, 'message' => 'Lỗi xác thực token: ' . $e->getMessage()], 401);
            return null;
        }
    }
}
