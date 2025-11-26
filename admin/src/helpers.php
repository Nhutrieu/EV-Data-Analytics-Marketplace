<?php
function jsonResponse($data, $code = 200) {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}

function encrypt_text($plaintext) {
  $cfg = require __DIR__ . '/config.php';
  $key = substr(hash('sha256', $cfg['encrypt_key']), 0, 32);
  $iv = random_bytes(16);
  $cipher = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
  return base64_encode($iv . $cipher);
}

function decrypt_text($b64) {
  $cfg = require __DIR__ . '/config.php';
  $key = substr(hash('sha256', $cfg['encrypt_key']), 0, 32);
  $raw = base64_decode($b64);
  $iv = substr($raw, 0, 16);
  $cipher = substr($raw, 16);
  return openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
}

function require_bearer($pdo) {
  $headers = getallheaders();
  $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
  if (!$token) jsonResponse(['error'=>'Unauthorized'], 401);
  if (stripos($token, 'Bearer ') === 0) $token = substr($token, 7);
  $stmt = $pdo->prepare("SELECT * FROM users WHERE api_key = :api_key LIMIT 1");
  $stmt->execute([':api_key' => $token]);
  $user = $stmt->fetch();
  if (!$user) jsonResponse(['error'=>'Invalid token'], 401);
  return $user;
}

function admin_only($user) {
  if ($user['role'] !== 'admin') jsonResponse(['error'=>'Forbidden'], 403);
}
