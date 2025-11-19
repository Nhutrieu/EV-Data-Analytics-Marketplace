<?php
// src/helpers/SecurityHelper.php

class SecurityHelper {
    private static $secretKey = 'CHO_DU_LIEU_SECRET_2025';

    public static function encrypt($data) {
        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt($data, 'AES-256-CBC', self::$secretKey, 0, $iv);
        return base64_encode($iv . $ciphertext);
    }

    public static function decrypt($encrypted) {
        $decoded = base64_decode($encrypted);
        $iv = substr($decoded, 0, 16);
        $ciphertext = substr($decoded, 16);
        return openssl_decrypt($ciphertext, 'AES-256-CBC', self::$secretKey, 0, $iv);
    }

    public static function generateApiToken($userId, $role) {
        $payload = json_encode(['id'=>$userId,'role'=>$role,'exp'=>time()+3600]);
        $sig = hash_hmac('sha256', $payload, self::$secretKey);
        return base64_encode($payload.'.'.$sig);
    }

    public static function verifyApiToken($token) {
        $decoded = base64_decode($token);
        if (!$decoded || !str_contains($decoded, '.')) return false;
        [$payload,$sig] = explode('.', $decoded, 2);
        $expected = hash_hmac('sha256', $payload, self::$secretKey);
        if (!hash_equals($expected, $sig)) return false;
        $data = json_decode($payload, true);
        return $data && $data['exp'] > time() ? $data : false;
    }
}
