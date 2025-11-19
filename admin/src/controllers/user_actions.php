<?php
require_once __DIR__.'/../db.php';
require_once __DIR__.'/UserController.php';

$userCtrl = new UserController($pdo);

// Lấy API key
if(isset($_GET['get_key'])){
    $id = intval($_GET['get_key']);
    $stmt = $pdo->prepare("SELECT api_key FROM users WHERE id=?");
    $stmt->execute([$id]);
    echo $stmt->fetchColumn() ?: '—';
    exit;
}

// CRUD qua AJAX
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])){
    $action = $_POST['action'];

    switch($action){
        case 'add_user':
            $role = $_POST['role'] ?? 'consumer';
            $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $role
            ]);
            echo json_encode(['ok'=>true,'id'=>$pdo->lastInsertId()]);
            break;

        case 'edit_user':
            $id = $_POST['id'];
            $params = [$_POST['name'], $_POST['email']];
            $passPart = '';
            if(!empty($_POST['password'])){
                $passPart = ", password=?";
                $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            $params[] = $id;
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=? $passPart WHERE id=?");
            $stmt->execute($params);
            echo json_encode(['ok'=>true]);
            break;

        case 'delete_user':
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$id]);
            echo json_encode(['ok'=>true]);
            break;

        case 'generate_api':
            $userCtrl->generateApiKey($_POST['id']);
            echo json_encode(['ok'=>true]);
            break;

        case 'revoke_api':
            $userCtrl->revokeApiKey($_POST['id']);
            echo json_encode(['ok'=>true]);
            break;
    }

    exit;
}

