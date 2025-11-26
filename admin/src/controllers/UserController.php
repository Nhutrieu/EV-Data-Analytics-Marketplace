<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers.php';

class UserController {
    private $pdo;
    private $userModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    public function generateApiKey($id) {
    $apiKey = bin2hex(random_bytes(16)); // tạo API key 32 ký tự

    $stmt = $this->pdo->prepare("UPDATE users SET api_key=? WHERE id=?");
    $stmt->execute([$apiKey, $id]);

    return [
        "status" => "success",
        "api_key" => $apiKey
    ];
}

public function revokeApiKey($id) {
    $stmt = $this->pdo->prepare("UPDATE users SET api_key=NULL WHERE id=?");
    $stmt->execute([$id]);

    return [
        "status" => "success",
        "message" => "API key revoked"
    ];
}


    /* ===============================
       CRUD NGƯỜI DÙNG
    =============================== */
    public function listUsers() {
        return $this->userModel->findAll();
    }


    public function getProviders() {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = 'provider'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConsumers() {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = 'consumer'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Cập nhật lại: nhận 1 mảng dữ liệu duy nhất
    public function createUser($data) {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['role'] ?? 'consumer'
        ]);
    }

    public function updateUser($id, $name, $email) {
        $stmt = $this->pdo->prepare("UPDATE users SET name=?, email=? WHERE id=?");
        $stmt->execute([$name, $email, $id]);
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$id]);
    }
}

/* ========================================================
   HÀM HIỂN THỊ BẢNG + FORM TRONG ADMIN DASHBOARD
======================================================== */

function showProviders() {
    $pdo = require __DIR__ . '/../db.php';
    $ctrl = new UserController($pdo);

    // Xử lý form CRUD
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['role'] ?? '') === 'provider') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $ctrl->createUser($_POST);
                    break;
                case 'edit':
                    $ctrl->updateUser($_POST['id'], $_POST['name'], $_POST['email']);
                    break;
                case 'delete':
                    $ctrl->deleteUser($_POST['id']);
                    break;
            }
        }
    }

    $rows = $ctrl->getProviders();
    echo "<h2>🏪 Danh sách Provider</h2>";
    echo "<form method='POST' class='user-form'>
            <input type='hidden' name='role' value='provider'>
            <input type='text' name='name' placeholder='Tên' required>
            <input type='email' name='email' placeholder='Email' required>
            <input type='password' name='password' placeholder='Mật khẩu' required>
            <button type='submit' name='action' value='add'>➕ Thêm Provider</button>
          </form>";

    if (!$rows) {
        echo "<p>Không có Provider nào.</p>";
        return;
    }

    echo "<table class='user-table'>
            <tr><th>ID</th><th>Tên</th><th>Email</th><th>Hành động</th></tr>";
    foreach ($rows as $r) {
        echo "<tr>
                <td>{$r['id']}</td>
                <td>{$r['name']}</td>
                <td>{$r['email']}</td>
                <td>
                    <form method='POST' style='display:inline-block'>
                        <input type='hidden' name='role' value='provider'>
                        <input type='hidden' name='id' value='{$r['id']}'>
                        <input type='text' name='name' value='{$r['name']}' required>
                        <input type='email' name='email' value='{$r['email']}' required>
                        <button type='submit' name='action' value='edit'>✏️</button>
                    </form>
                    <form method='POST' style='display:inline-block' onsubmit='return confirm(\"Xóa người dùng này?\")'>
                        <input type='hidden' name='role' value='provider'>
                        <input type='hidden' name='id' value='{$r['id']}'>
                        <button type='submit' name='action' value='delete'>🗑️</button>
                    </form>
                </td>
              </tr>";
    }
    echo "</table>";
}

function showConsumers() {
    $pdo = require __DIR__ . '/../db.php';
    $ctrl = new UserController($pdo);

    // CRUD form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['role'] ?? '') === 'consumer') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $ctrl->createUser($_POST);
                    break;
                case 'edit':
                    $ctrl->updateUser($_POST['id'], $_POST['name'], $_POST['email']);
                    break;
                case 'delete':
                    $ctrl->deleteUser($_POST['id']);
                    break;
            }
        }
    }

    $rows = $ctrl->getConsumers();
    echo "<h2>👤 Danh sách Consumer</h2>";
    echo "<form method='POST' class='user-form'>
            <input type='hidden' name='role' value='consumer'>
            <input type='text' name='name' placeholder='Tên' required>
            <input type='email' name='email' placeholder='Email' required>
            <input type='password' name='password' placeholder='Mật khẩu' required>
            <button type='submit' name='action' value='add'>➕ Thêm Consumer</button>
          </form>";

    if (!$rows) {
        echo "<p>Không có Consumer nào.</p>";
        return;
    }

    echo "<table class='user-table'>
            <tr><th>ID</th><th>Tên</th><th>Email</th><th>Hành động</th></tr>";
    foreach ($rows as $r) {
        echo "<tr>
                <td>{$r['id']}</td>
                <td>{$r['name']}</td>
                <td>{$r['email']}</td>
                <td>
                    <form method='POST' style='display:inline-block'>
                        <input type='hidden' name='role' value='consumer'>
                        <input type='hidden' name='id' value='{$r['id']}'>
                        <input type='text' name='name' value='{$r['name']}' required>
                        <input type='email' name='email' value='{$r['email']}' required>
                        <button type='submit' name='action' value='edit'>✏️</button>
                    </form>
                    <form method='POST' style='display:inline-block' onsubmit='return confirm(\"Xóa người dùng này?\")'>
                        <input type='hidden' name='role' value='consumer'>
                        <input type='hidden' name='id' value='{$r['id']}'>
                        <button type='submit' name='action' value='delete'>🗑️</button>
                    </form>
                </td>
              </tr>";
    }
    echo "</table>";
}
