<?php
// modules/PricingModule.php

require_once __DIR__ . '/../classes/Pricing.php';

class PricingModule
{
    private Pricing $pricing;

    public function __construct(PDO $db, int $providerId)
    {
        $this->pricing = new Pricing($db, $providerId);
    }

    public function getPolicy(): array
    {
        return $this->pricing->getPolicy();
    }

    public function updatePolicy(array $data): void
    {
        if (isset($data['price']) && (int)$data['price'] < 0) {
            throw new InvalidArgumentException('Giá không hợp lệ');
        }

        $ok = $this->pricing->savePolicy($data);
        if (!$ok) {
            throw new RuntimeException('Không lưu được chính sách giá');
        }
    }
}
