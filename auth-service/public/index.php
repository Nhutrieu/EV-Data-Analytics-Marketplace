<?php
<<<<<<< HEAD
// Mặc định chuyển tới home.php
header("Location: home.php");
exit;
=======
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Helpers\ResponseHelper;

// Load .env
$dotenvPath = __DIR__ . '/../.env';
if (file_exists($dotenvPath)) {
    $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!strpos($line, '=')) continue;
        putenv($line);
    }
}

// Setup DB (optionally used by models)
Database::init();

// Simple routing
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Strip query string
$uri = explode('?', $uri, 2)[0];

// load routes
(require __DIR__ . '/../src/routes.php')($method, $uri);
>>>>>>> b2b62b30502933ce69778733bd55f2cea706a8e3
