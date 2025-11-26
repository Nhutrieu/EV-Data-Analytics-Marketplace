<?php
namespace App\Controllers;

use App\Models\User;
use App\Helpers\ResponseHelper;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController
{
    private User $userModel;
    private string $jwtSecret;
    private int $jwtExp;
    private string $iss;
    private string $aud;

    public function __construct()
    {
        $this->userModel = new User();
        $this->jwtSecret = getenv('JWT_SECRET') ?: 'change_me';
        $this->jwtExp = (int)(getenv('JWT_EXP') ?: 3600);
        $this->iss = getenv('JWT_ISSUER') ?: 'http://auth-service';
        $this->aud = getenv('JWT_AUDIENCE') ?: 'http://ev-data-marketplace';
    }

    public function register()
    {
        $data = ResponseHelper::input();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $name = $data['name'] ?? null;

        if (empty($email) || empty($password)) {
            ResponseHelper::json(['status' => false, 'message' => 'Email và password bắt buộc'], 400);
            return;
        }

        if ($this->userModel->findByEmail($email)) {
            ResponseHelper::json(['status' => false, 'message' => 'Email đã tồn tại'], 400);
            return;
        }

        $user = $this->userModel->create($email, $password, $name);
        ResponseHelper::json(['status' => true, 'message' => 'Đăng ký thành công', 'data' => $user], 201);
    }

    public function login()
    {
        $data = ResponseHelper::input();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            ResponseHelper::json(['status' => false, 'message' => 'Email và password bắt buộc'], 400);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            ResponseHelper::json(['status' => false, 'message' => 'Email hoặc password không đúng'], 401);
            return;
        }

        $now = time();
        $payload = [
            'iss' => $this->iss,
            'aud' => $this->aud,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->jwtExp,
            'data' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'] ?? null
            ]
        ];

        $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

        ResponseHelper::json([
            'status' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'token' => $jwt,
                'expires_in' => $this->jwtExp,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'] ?? null
                ]
            ]
        ]);
    }

    public function profile(array $user)
    {
        ResponseHelper::json(['status' => true, 'data' => $user]);
    }
}
