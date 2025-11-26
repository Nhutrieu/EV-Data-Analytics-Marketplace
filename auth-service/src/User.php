<?php
class User {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($username, $password, $role) {
        $table = '';
        if ($role === 'admin') $table = 'admins';
        elseif ($role === 'provider') $table = 'providers';
        elseif ($role === 'consumer') $table = 'consumers';
        else return false;

        $stmt = $this->pdo->prepare("SELECT * FROM $table WHERE username=:username LIMIT 1");
        $stmt->execute(['username'=>$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) return $user;
        return false;
    }

    public function register($username, $password, $role, $extra=null) {
        $table = '';
        if ($role === 'admin') $table = 'admins';
        elseif ($role === 'provider') $table = 'providers';
        elseif ($role === 'consumer') $table = 'consumers';
        else return false;

        $passHash = password_hash($password, PASSWORD_BCRYPT);

        if ($role === 'provider') {
            $stmt = $this->pdo->prepare("INSERT INTO $table (username,password,company_name) VALUES (:u,:p,:c)");
            return $stmt->execute(['u'=>$username,'p'=>$passHash,'c'=>$extra]);
        } elseif ($role === 'consumer') {
            $stmt = $this->pdo->prepare("INSERT INTO $table (username,password,email) VALUES (:u,:p,:e)");
            return $stmt->execute(['u'=>$username,'p'=>$passHash,'e'=>$extra]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO $table (username,password) VALUES (:u,:p)");
            return $stmt->execute(['u'=>$username,'p'=>$passHash]);
        }
    }
}
