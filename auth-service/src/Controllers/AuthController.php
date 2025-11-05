<?php
namespace App\Controllers;

use App\Models\User;
use App\Helpers\ResponseHelper;
use Firebase\JWT\JWT;

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function register() {
        $email = $_POST["email"] ?? "";
        $password = $_POST["password"] ?? "";

        if (!$email || !$password) {
            ResponseHelper::json(["error" => "Empty fields"], 400);
        }

        if ($this->user->findByEmail($email)) {
            ResponseHelper::json(["error" => "Email exists"], 409);
        }

        $this->user->register($email, $password);
        ResponseHelper::json(["message" => "User registered"]);
    }

    public function login() {
        $email = $_POST["email"] ?? "";
        $password = $_POST["password"] ?? "";

        $user = $this->user->findByEmail($email);
        if (!$user || !password_verify($password, $user["password"])) {
            ResponseHelper::json(["error" => "Invalid credentials"], 401);
        }

        $payload = [
            "id" => $user["id"],
            "email" => $email,
            "iat" => time(),
            "exp" => time() + 86400
        ];

        $token = JWT::encode($payload, "SECRET_KEY", 'HS256');

        ResponseHelper::json(["token" => $token]);
    }
}
