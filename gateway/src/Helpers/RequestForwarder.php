<?php
namespace App\Helpers;

use GuzzleHttp\Client;

class RequestForwarder {

    public static function forward($baseUrl) {
        $client = new Client(['timeout' => 10]);
        $method = $_SERVER['REQUEST_METHOD'];

        $uri = $_SERVER['REQUEST_URI'];
        $parsed = parse_url($uri);
        $path = $parsed['path'] ?? '/';
        $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';
        $fullPath = $path . $query;

        $body = file_get_contents('php://input');

        // Chuẩn hóa headers
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $headers[$headerName] = $value;
            }
        }
        unset($headers['Host']); // tránh xung đột

        try {
            $response = $client->request($method, rtrim($baseUrl, '/') . $fullPath, [
                'headers' => $headers,
                'body' => $body,
                'http_errors' => false
            ]);

            http_response_code($response->getStatusCode());
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header("$name: $value");
                }
            }
            echo $response->getBody();

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Gateway Error: '.$e->getMessage()]);
        }
    }
}
