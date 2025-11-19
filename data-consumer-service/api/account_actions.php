<?php
// account_actions.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Kết nối DB (trong database.php có $conn = new mysqli(...))
require_once __DIR__ . '/../classes/Database.php';

// Bắt buộc đăng nhập
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Bạn chưa đăng nhập.'
    ]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Chỉ hỗ trợ POST.'
    ]);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_profile':
        update_profile($conn, $user_id);
        break;

    case 'change_password':
        change_password($conn, $user_id);
        break;

    case 'logout':
        logout_user();
        break;

    case 'delete_account':
        delete_account($conn, $user_id);
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Hành động không hợp lệ.'
        ]);
        break;
}

/* =========================
   HÀM HỖ TRỢ / XỬ LÝ
   ========================== */

function get_user(mysqli $conn, int $user_id)
{
    $stmt = $conn->prepare(
        "SELECT id, name, email, password, role, created_at FROM users WHERE id = ?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc();
}

function update_profile(mysqli $conn, int $user_id)
{
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $email === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Tên và email không được để trống.'
        ]);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email không hợp lệ.'
        ]);
        return;
    }

    // Check email trùng
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->fetch_assoc()) {
        echo json_encode([
            'success' => false,
            'message' => 'Email này đã được sử dụng.'
        ]);
        return;
    }

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $user_id);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật hồ sơ thành công.',
        'data'    => ['name' => $name, 'email' => $email]
    ]);
}

function change_password(mysqli $conn, int $user_id)
{
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($current === '' || $new === '' || $confirm === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng nhập đầy đủ mật khẩu.'
        ]);
        return;
    }

    $user = get_user($conn, $user_id);
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy người dùng.'
        ]);
        return;
    }

    // password đang là hash
    if (!password_verify($current, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Mật khẩu hiện tại không đúng.'
        ]);
        return;
    }

    if ($new !== $confirm) {
        echo json_encode([
            'success' => false,
            'message' => 'Mật khẩu mới và xác nhận không khớp.'
        ]);
        return;
    }

    if (strlen($new) < 8) {
        echo json_encode([
            'success' => false,
            'message' => 'Mật khẩu mới phải từ 8 ký tự trở lên.'
        ]);
        return;
    }

    $hash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hash, $user_id);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Đổi mật khẩu thành công.'
    ]);
}

function logout_user()
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    session_destroy();

    echo json_encode([
        'success' => true,
        'message' => 'Đã đăng xuất.'
    ]);
}

function delete_account(mysqli $conn, int $user_id)
{
    $password_input = $_POST['password'] ?? '';

    $user = get_user($conn, $user_id);
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy người dùng.'
        ]);
        return;
    }

    if ($password_input === '' || !password_verify($password_input, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Mật khẩu xác nhận không đúng.'
        ]);
        return;
    }

    // TODO: xoá thêm dữ liệu liên quan nếu có (purchases, api_keys, ...)
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $_SESSION = [];
    session_destroy();

    echo json_encode([
        'success' => true,
        'message' => 'Tài khoản đã được xoá.'
    ]);
}
