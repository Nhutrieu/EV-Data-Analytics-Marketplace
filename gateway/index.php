<?php
header("Content-Type: application/json");

$authServiceUrl = "http://auth-service:80/public/index.php?action=";

$path = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

if (in_array($path, ['login', 'register'])) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $authServiceUrl.$path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    echo $response;
} else {
    echo json_encode(['message'=>'Route not found']);
}
?>
