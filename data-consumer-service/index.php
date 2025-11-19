<?php

// Thư mục gốc backend (trong container: /var/www/html)
$baseDir = __DIR__;

// Kết nối DB
require_once $baseDir . '/classes/Database.php';

// Thư mục public (trong container: /var/www/html/public)
$publicDir = $baseDir . '/public';

// Trang mặc định
$page = $_GET['page'] ?? 'consumer';



switch ($page) {
    case 'consumer':
        require $baseDir . '/public/consumer.html';
        break;

    case 'datasets':
        header('Content-Type: application/json; charset=utf-8');
        require_once $baseDir . '/api/controllers/DatasetController.php';
        $controller = new DatasetController();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->index();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    // 3. Analytics Packages API (danh sách gói phân tích)
    case 'analytics':
        header('Content-Type: application/json; charset=utf-8');

        require_once $baseDir . '/api/controllers/AnalyticsController.php';
        $analyticsController = new AnalyticsController();

        if (isset($_GET['id'])) {
            $analyticsController->viewPackage((int) $_GET['id']);
        } else {
            $analyticsController->listPackages();
        }
        break;

    // 3b. Analytics data (dữ liệu cho biểu đồ, dashboard)
    case 'analytics_data':
        header('Content-Type: application/json; charset=utf-8');

        require_once $baseDir . '/api/controllers/AnalyticsController.php';
        $analyticsController = new AnalyticsController();

        if (isset($_GET['id'])) {
            $analyticsController->getPackageData((int) $_GET['id']);
        } else {
            $analyticsController->listAnalyticsData();
        }
        break;

    // 4. Purchase API (lịch sử mua hàng, tạo purchase)
    case 'purchase':
        session_start();
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Chưa login"
            ]);
            break;
        }

        $userId = (int) $_SESSION['user_id'];

        require_once $baseDir . '/api/controllers/PurchaseController.php';
        $purchaseController = new PurchaseController();

        if (isset($_GET['id'])) {
            // Xem chi tiết 1 purchase
            $purchaseController->viewPurchase((int) $_GET['id']);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // GET /index.php?page=purchase → trả về danh sách purchase của user
            $purchaseController->listUserPurchases($userId);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Tạo purchase bằng API (cho giỏ hàng thanh toán)
            $input      = json_decode(file_get_contents('php://input'), true) ?? [];
            $dataset_id = $input['dataset_id'] ?? null;
            $type       = $input['type'] ?? null;
            $price      = $input['price'] ?? null;

            if ($dataset_id && $type && $price) {
                $purchaseController->createPurchase(
                    $userId,
                    (int) $dataset_id,
                    $type,
                    (float) $price
                );
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Thiếu dữ liệu tạo purchase"
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Route purchase không hỗ trợ method này"
            ]);
        }
        break;

    // 5. API Key management
    case 'api_key':
        session_start();
        header('Content-Type: application/json; charset=utf-8');

        // Phải login mới dùng được API key
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Chưa login"
            ]);
            break;
        }

        $user_id = (int) $_SESSION['user_id'];

        require_once $baseDir . '/classes/Database.php';
        require_once $baseDir . '/classes/ApiKey.php';

        $db  = Database::getConnection();
        $api = new ApiKey($db);

        $action  = $_GET['action'] ?? '';

        // 🔹 Tạo API key mới cho user đang login
        if ($action === 'create') {

            // Xoá hết key cũ của user (nếu bạn muốn revoke luôn)
            $stmt = $db->prepare("DELETE FROM api_keys WHERE user_id = :uid");
            $stmt->execute([':uid' => $user_id]);

            // Tạo key mới
            $key = $api->createKey($user_id);

            echo json_encode([
                "success" => true,
                "message" => "Tạo API key mới thành công.",
                "api_key" => $key
            ]);
        }

        // 🔹 Lấy key hiện tại của user (1 key mới nhất)
        elseif ($action === 'list') {
            $stmt = $db->prepare("
                SELECT id, api_key, status, created_at
                FROM api_keys
                WHERE user_id = :uid
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmt->execute([':uid' => $user_id]);
            $key = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($key) {
                echo json_encode([
                    "success" => true,
                    "data"    => $key   // trả FULL api_key
                ]);
            } else {
                echo json_encode([
                    "success" => true,
                    "data"    => null,
                    "message" => "Chưa có API key."
                ]);
            }
        }

        // 🔹 Xoá tất cả key của user đang login
        elseif ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM api_keys WHERE user_id = :uid");
            $ok   = $stmt->execute([':uid' => $user_id]);

            echo json_encode([
                "success" => $ok,
                "message" => $ok
                    ? "Đã xóa API key của user."
                    : "Không xóa được API key."
            ]);
        }

        else {
            echo json_encode([
                "success" => false,
                "message" => "Hành động không hợp lệ."
            ]);
        }

        break;

    // 6. Payment
   case 'payment':
    session_start();
    header('Content-Type: application/json; charset=utf-8');

    // Bắt buộc phải login
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Chưa login, không thể tạo thanh toán."
        ]);
        break;
    }

    $userId = (int) $_SESSION['user_id'];

    $action = $_GET['action'] ?? '';

    if ($action === 'create') {
        // Truyền $userId cho file create_payment nếu cần
        require_once $baseDir . '/payment/create_payment.php';
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Action payment không hợp lệ"
        ]);
    }
    break;


    // 7. Account backend (update profile, change password, logout, delete)
      // 1b. Account backend (update profile, change password, logout, delete)
    case 'account':
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8');

        // Bắt buộc login
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Bạn chưa đăng nhập.'
            ]);
            break;
        }

        // ✅ Dùng PDO
        try {
            $db = Database::getConnection(); // PDO
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Không kết nối được DB: ' . $e->getMessage()
            ]);
            break;
        }

        $user_id = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Chỉ hỗ trợ POST.'
            ]);
            break;
        }

        $action = $_POST['action'] ?? '';

        switch ($action) {
            // ===== CẬP NHẬT HỒ SƠ =====
            case 'update_profile':
                $name  = trim($_POST['name']  ?? '');
                $email = trim($_POST['email'] ?? '');

                if ($name === '' || $email === '') {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Tên và email không được để trống.'
                    ]);
                    break;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Email không hợp lệ.'
                    ]);
                    break;
                }

                // check email trùng (ngoại trừ chính mình)
                $stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND id <> :id");
                $stmt->execute([
                    ':email' => $email,
                    ':id'    => $user_id
                ]);
                if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Email này đã được sử dụng.'
                    ]);
                    break;
                }

                $stmt = $db->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
                $stmt->execute([
                    ':name'  => $name,
                    ':email' => $email,
                    ':id'    => $user_id
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật hồ sơ thành công.',
                    'data'    => ['name' => $name, 'email' => $email]
                ]);
                break;

            // ===== ĐỔI MẬT KHẨU (đang dùng plain text giống login.php) =====
            case 'change_password':
                $current = $_POST['current_password'] ?? '';
                $new     = $_POST['new_password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';

                if ($current === '' || $new === '' || $confirm === '') {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Vui lòng nhập đầy đủ mật khẩu.'
                    ]);
                    break;
                }

                // Lấy password hiện tại
                $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
                $stmt->execute([':id' => $user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user || $current !== $user['password']) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Mật khẩu hiện tại không đúng.'
                    ]);
                    break;
                }

                if ($new !== $confirm) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Mật khẩu mới và xác nhận không khớp.'
                    ]);
                    break;
                }

                if (strlen($new) < 8) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Mật khẩu mới phải từ 8 ký tự trở lên.'
                    ]);
                    break;
                }

                // ⚠️ Tạm thời lưu plain text (để khớp với login.php hiện tại)
                $stmt = $db->prepare("UPDATE users SET password = :pwd WHERE id = :id");
                $stmt->execute([
                    ':pwd' => $new,
                    ':id'  => $user_id
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Đổi mật khẩu thành công.'
                ]);
                break;

            // ===== ĐĂNG XUẤT =====
            case 'logout':
                $_SESSION = [];

                if (ini_get('session.use_cookies')) {
                    $params = session_get_cookie_params();
                    setcookie(
                        session_name(),
                        '',
                        time() - 42000,
                        $params['path'],
                        $params['domain'],
                        $params['secure'],
                        $params['httponly']
                    );
                }

                session_destroy();

                echo json_encode([
                    'success' => true,
                    'message' => 'Đã đăng xuất.'
                ]);
                break;

            // ===== XOÁ TÀI KHOẢN =====
           // ===== XOÁ TÀI KHOẢN =====
case 'delete_account':
    $password_input = $_POST['password'] ?? '';

    // lấy password hiện tại của user đang login (theo session)
    $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // bạn đang lưu password PLAIN TEXT (giống login.php: $user['password'] === $password)
    if (
        !$user ||
        $password_input === '' ||
        $user['password'] !== $password_input
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Mật khẩu xác nhận không đúng.'
        ]);
        break;
    }

    try {
        // bắt đầu transaction cho chắc chắn
        $db->beginTransaction();

        // ⚙️ Xoá dữ liệu liên quan tới user (có FK user_id -> users.id)
        $tables = [
            'api_keys',
            'user_cart',
            'purchases'
        ];

        foreach ($tables as $tbl) {
            $sql = "DELETE FROM {$tbl} WHERE user_id = :id";
            $st  = $db->prepare($sql);
            $st->execute([':id' => $user_id]);
        }

        // cuối cùng mới xoá user
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);

        $db->commit();

        // clear session
        $_SESSION = [];
        if (session_id() !== '') {
            session_destroy();
        }

        echo json_encode([
            'success' => true,
            'message' => 'Tài khoản đã được xoá.'
        ]);
    } catch (PDOException $e) {
        $db->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Không xoá được tài khoản: ' . $e->getMessage()
        ]);
    }
    break;


            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Hành động không hợp lệ.'
                ]);
                break;
        }

        break;

    case 'current_user':
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Chưa đăng nhập'
            ]);
            break;
        }

        // ✅ Dùng đúng file classes/Database.php
        require_once $baseDir . '/classes/Database.php';

        // ✅ Lấy PDO từ class Database
        $db = Database::getConnection();   // đặt tên $db hay $pdo tuỳ bạn, miễn nhất quán
        $user_id = (int) $_SESSION['user_id'];

        $stmt = $db->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy user'
            ]);
            break;
        }

        echo json_encode([
            'success' => true,
            'data'    => [
                'name'  => $user['name'],
                'email' => $user['email']
            ]
        ]);
        break;

case 'data_access':
    header('Content-Type: application/json; charset=utf-8');
    require_once $baseDir . '/api/data_access.php';
    break;

    // 8. Default 404
    default:
        http_response_code(404);
        echo "<h2>404 - Trang không tồn tại</h2>";
        break;
}
