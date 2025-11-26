<?php
session_start();

require_once __DIR__ . '/classes/Database.php';
$db = Database::getConnection();

// Nếu đã login thì cho vào luôn UI chính
if (!empty($_SESSION['user_id'])) {
    header('Location: /public/consumer.html');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    } else {
        // Lấy user theo email
        $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ở DB hiện tại, password lưu PLAIN TEXT
        if ($user && $user['password'] === $password) {
            $_SESSION['user_id'] = (int) $user['id'];

            header('Location: /public/consumer.html');
            exit;
        } else {
            $error = 'Email hoặc mật khẩu không đúng.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập | EV Data Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS chung (nếu bạn đã có) -->
    <link rel="stylesheet" href="css/consumer.css">

    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top, #0b1120 0, #020617 45%, #000 100%);
            color: #e5e7eb;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: #020617;
            border-radius: 18px;
            border: 1px solid #1f2937;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.9);
            padding: 1.8rem 1.9rem 1.9rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .login-logo {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            font-size: 1.5rem;
        }

        .login-header h1 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
        }

        .login-header p {
            margin: 0.4rem 0 0;
            font-size: 0.9rem;
            color: #9ca3af;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .login-form .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .login-form label {
            font-size: 0.85rem;
            color: #cbd5f5;
        }

        .login-form input[type="email"],
        .login-form input[type="password"] {
            background: #020617;
            border-radius: 10px;
            border: 1px solid #1f2937;
            padding: 0.55rem 0.75rem;
            font-size: 0.9rem;
            color: #e5e7eb;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .login-form input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.6);
            background: #02091f;
        }

        .login-error {
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: #fecaca;
            background: rgba(127, 29, 29, 0.2);
            border-radius: 10px;
            border: 1px solid rgba(239, 68, 68, 0.4);
            padding: 0.55rem 0.7rem;
        }

        .login-submit {
            width: 100%;
            margin-top: 0.5rem;
            padding: 0.55rem 1rem;
            border-radius: 999px;
            border: 1px solid #2563eb;
            background: #2563eb;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, transform 0.05s ease;
        }

        .login-submit:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.6);
            transform: translateY(-1px);
        }

        .login-meta {
            margin-top: 0.75rem;
            text-align: center;
            font-size: 0.8rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">⚡</div>
            <h1>EV Data Marketplace</h1>
            <p>Đăng nhập để truy cập dashboard & dataset.</p>
        </div>

        <?php if ($error): ?>
            <div class="login-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form class="login-form" method="post" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    autocomplete="email"
                    value="<?= isset($email) ? htmlspecialchars($email) : '' ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="login-submit">
                Đăng nhập
            </button>

            <div class="login-meta">
                EV Data Platform &copy; <?= date('Y') ?>
            </div>
        </form>
    </div>
</div>
</body>
</html>
