<?php
// public/index.php
// API router for admin functions
// Usage examples:
//  GET  /public/index.php?path=users
//  POST /public/index.php?path=users   (body JSON)
//  GET  /public/index.php?path=stats/overview

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/DataController.php';
require_once __DIR__ . '/../controllers/PaymentController.php';
require_once __DIR__ . '/../controllers/AnalyticsController.php';

$pdo = $GLOBALS['pdo'] ?? null;
if (!$pdo) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Database connection not found']);
    exit;
}

$userCtrl = new UserController($pdo);
$dataCtrl = new DataController($pdo);
$payCtrl  = new PaymentController($pdo);
$anCtrl   = new AnalyticsController($pdo);

$path = $_GET['path'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json');

switch ($path) {
    case 'users':
        if ($method === 'GET') {
            $auth = require_bearer($pdo); admin_only($auth);
            echo json_encode($userCtrl->listUsers());
            exit;
        }
        if ($method === 'POST') {
            $auth = require_bearer($pdo); admin_only($auth);
            $body = json_decode(file_get_contents('php://input'), true);
            echo json_encode(['id' => $userCtrl->createUser($body)]);
            exit;
        }
        break;

    case 'users/apikey':
        $auth = require_bearer($pdo); admin_only($auth);
        $action = $_GET['action'] ?? null;
        $id = (int)($_GET['id'] ?? 0);
        if ($action === 'gen') echo json_encode($userCtrl->generateApiKey($id));
        if ($action === 'revoke') echo json_encode($userCtrl->revokeApiKey($id));
        exit;

    case 'datasets/pending':
        $auth = require_bearer($pdo); admin_only($auth);
        echo json_encode($dataCtrl->listPending());
        exit;

    case 'datasets/approve':
        $auth = require_bearer($pdo); admin_only($auth);
        $id = (int)($_GET['id'] ?? 0);
        echo json_encode($dataCtrl->approve($id, $auth['id']));
        exit;

    case 'datasets/reject':
        $auth = require_bearer($pdo); admin_only($auth);
        $id = (int)($_GET['id'] ?? 0);
        $reason = $_GET['reason'] ?? null;
        echo json_encode($dataCtrl->reject($id, $auth['id'], $reason));
        exit;

    case 'transactions':
        $auth = require_bearer($pdo); admin_only($auth);
        echo json_encode($payCtrl->listTransactions());
        exit;

    case 'transactions/complete':
        $auth = require_bearer($pdo); admin_only($auth);
        $txid = (int)($_GET['txid'] ?? 0);
        echo json_encode($payCtrl->completeTransaction($txid));
        exit;

    case 'stats/overview':
        $auth = require_bearer($pdo); admin_only($auth);
        echo json_encode($anCtrl->overview());
        exit;

   case 'stats/data-trends':
        $auth = require_bearer($pdo); 
        admin_only($auth);
        echo json_encode($anCtrl->data_trends());
        exit;

    default:
        header("Location: /admin_dashboard.php");
exit;

}
