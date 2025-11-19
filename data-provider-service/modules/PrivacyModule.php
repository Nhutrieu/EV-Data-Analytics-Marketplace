<?php
// modules/PrivacyModule.php

require_once __DIR__ . '/../classes/Privacy.php';

class PrivacyModule
{
    private Privacy $privacy;

    public function __construct(PDO $db, int $providerId)
    {
        $this->privacy = new Privacy($db, $providerId);
    }

    public function getSettings(): array
    {
        return $this->privacy->getSettings();
    }

    /**
     * Validate input + lưu cài đặt
     */
    public function saveSettings(array $data): void
    {
        // anonymize: cho phép true/false/"1"/"0"/1/0
        if (isset($data['anonymize'])) {
            $data['anonymize'] = filter_var($data['anonymize'], FILTER_VALIDATE_BOOL);
        }

        if (isset($data['retention_months'])) {
            $months = (int)$data['retention_months'];
            if ($months < 0 || $months > 120) {
                throw new InvalidArgumentException('Thời gian lưu trữ không hợp lệ (0–120 tháng).');
            }
            $data['retention_months'] = $months;
        }

        $ok = $this->privacy->saveSettings($data);
        if (!$ok) {
            throw new RuntimeException('Không lưu được cài đặt bảo mật.');
        }
    }
}
