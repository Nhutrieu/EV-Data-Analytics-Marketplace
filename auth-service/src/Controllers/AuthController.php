<?php
namespace Src\Controllers;

use Src\Models\User;
use Src\Models\Admin;
use Src\Helpers\ResponseHelper;
use Src\Middleware\AuthMiddleware;
use Firebase\JWT\JWT;

class AuthController {
    private $userModel;
    private $adminModel;

    public function __construct() {
        $this->userModel = new User();
        $this->adminModel = new Admin();
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $isAdmin = $data['isAdmin'] ?? false;

        if (!$email || !$password) {
            ResponseHelper::json(["error" => "Thiếu thông tin"], 400);
        }

        $user = $isAdmin ? $this->adminModel->findByEmail($email) : $this->userModel->findByEmail($email);
        if (!$user) {
            ResponseHelper::json(["error" => "Tài khoản không tồn tại"], 404);
        }

        $passwordField = $isAdmin ? 'password' : 'MatKhau';
        if (!password_verify($password, $user[$passwordField])) {
            ResponseHelper::json(["error" => "Sai mật khẩu"], 401);
        }

        $payload = [
            "email" => $user['Email'] ?? $user['email'],
            "role" => $isAdmin ? "admin" : "user",
            "iat" => time(),
            "exp" => time() + 86400
        ];

        $token = JWT::encode($payload, 'SECRET_KEY', 'HS256');
        ResponseHelper::json(["message" => "Đăng nhập thành công", "token" => $token, "user" => $user]);
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $name = $data['name'] ?? '';

        if (!$email || !$password || !$name) {
            ResponseHelper::json(["error" => "Thiếu thông tin"], 400);
        }

        if ($this->userModel->findByEmail($email)) {
            ResponseHelper::json(["error" => "Email đã tồn tại"], 409);
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $this->userModel->create($name, $email, $hashed);
        ResponseHelper::json(["message" => "Đăng ký thành công"], 201);
    }

    public function me() {
        $user = AuthMiddleware::getUserFromToken();
        ResponseHelper::json(["user" => $user]);
    }

    public function socialLogin() {
        $provider = $_GET['provider'] ?? null;
        if (!$provider) {
            ResponseHelper::json(["error" => "Thiếu thông tin provider"], 400);
        }

        $mockUsers = [
            "google" => ["name" => "Người dùng Google", "email" => "google_user@example.com"],
            "facebook" => ["name" => "Người dùng Facebook", "email" => "fb_user@example.com"],
            "instagram" => ["name" => "Người dùng Instagram", "email" => "insta_user@example.com"]
        ];

        if (!isset($mockUsers[$provider])) {
            ResponseHelper::json(["error" => "Provider không hợp lệ"], 400);
        }

        $user = $mockUsers[$provider];
        $existingUser = $this->userModel->findByEmail($user['email']);

        if (!$existingUser) {
            $randomPassword = password_hash('social_login_' . uniqid(), PASSWORD_DEFAULT);
            $this->userModel->create($user['name'], $user['email'], $randomPassword);
        }

        $payload = [
            "email" => $user['email'],
            "role" => "user",
            "iat" => time(),
            "exp" => time() + 86400
        ];

        $token = JWT::encode($payload, 'SECRET_KEY', 'HS256');

        ResponseHelper::json([
            "message" => "Đăng nhập $provider thành công!",
            "token" => $token,
            "user" => $user
        ]);
    }
}
