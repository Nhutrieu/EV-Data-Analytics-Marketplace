<?php
// src/models/User.php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($name, $email, $password, $role = 'consumer') {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("
                INSERT INTO users (name, email, password, role)
                VALUES (:n, :e, :p, :r)
            ");
            $stmt->execute([
                ':n' => $name,
                ':e' => $email,
                ':p' => $hash,
                ':r' => $role
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("❌ Lỗi khi tạo user: " . $e->getMessage());
            return false;
        }
    }

    public function findAll() {
        $stmt = $this->pdo->query("
            SELECT id, name, email, role, created_at, api_key 
            FROM users
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("
            SELECT id, name, email, role, created_at, api_key 
            FROM users 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function setRole($id, $role) {
        $stmt = $this->pdo->prepare("
            UPDATE users SET role = :role WHERE id = :id
        ");
        $stmt->execute([':role' => $role, ':id' => $id]);
    }

    public function genApiKey($id) {
        $key = bin2hex(random_bytes(24));
        $stmt = $this->pdo->prepare("
            UPDATE users SET api_key = :k WHERE id = :id
        ");
        $stmt->execute([':k' => $key, ':id' => $id]);
        return $key;
    }

    public function revokeApiKey($id) {
        $stmt = $this->pdo->prepare("
            UPDATE users SET api_key = NULL WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }

    public function findByRole($role) {
        $stmt = $this->pdo->prepare("
            SELECT id, name, email, role, created_at, api_key 
            FROM users 
            WHERE role = ?
        ");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
