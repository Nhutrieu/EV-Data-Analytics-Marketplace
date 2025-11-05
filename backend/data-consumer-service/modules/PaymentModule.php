<?php
class PaymentModule {
    private $clientId;
    private $apiKey;

    public function __construct($clientId, $apiKey) {
        $this->clientId = $clientId;
        $this->apiKey = $apiKey;
    }

    public function createPayOSSQR($orderId, $amount, $accountNumber, $accountName, $bankCode) {
        $payload = [
            "accountNumber" => $accountNumber,
            "accountName" => $accountName,
            "bankCode" => $bankCode,
            "amount" => $amount,
            "orderCode" => $orderId,
            "description" => "Thanh toan don #{$orderId}"
        ];

        $ch = curl_init("https://api.payoss.vn/v1/qrcode/create");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Client-ID: {$this->clientId}",
            "API-Key: {$this->apiKey}"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if($err) {
            return ["error" => $err];
        }

        return json_decode($res, true); // QR thật
    }
}
