<?php
namespace App\Models;

use App\Config\Database;

class User
{
    private \PDO $db;
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(string $email, string $password, string $name = null): array
    {
        $sql = "INSERT INTO users (email, password_hash, name, created_at) VALUES (:email, :pwd, :name, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':pwd' => password_hash($password, PASSWORD_DEFAULT),
            ':name' => $name
        ]);
        $id = (int)$this->db->lastInsertId();
        return $this->findById($id);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT id, email, password_hash, name, created_at FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, email, name, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
