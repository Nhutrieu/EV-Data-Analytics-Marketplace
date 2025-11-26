<?php
// modules/SettingsModule.php

require_once __DIR__ . '/../classes/Settings.php';

class SettingsModule
{
    private Settings $settings;

    public function __construct(PDO $db, int $providerId)
    {
        $this->settings = new Settings($db, $providerId);
    }

    /**
     * Lấy toàn bộ dữ liệu cần cho trang settings:
     * - Thông tin công ty
     * - Thông tin login (email)
     * - Cài đặt hệ thống
     */
    public function getAll(): array
    {
        return [
            'company' => $this->settings->getCompanyInfo(),
            'login'   => $this->settings->getLoginInfo(),
            'system'  => $this->settings->getSystemSettings(),
        ];
    }

    public function saveCompany(array $data): void
    {
        // Validate cơ bản
        $companyName   = trim($data['company_name']   ?? '');
        $contactEmail  = trim($data['contact_email']  ?? '');
        $contactPerson = trim($data['contact_person'] ?? '');

        if ($companyName === '') {
            throw new InvalidArgumentException('Tên công ty là bắt buộc.');
        }
        if ($contactEmail === '' || !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email liên hệ không hợp lệ.');
        }
        if ($contactPerson === '') {
            throw new InvalidArgumentException('Người liên hệ là bắt buộc.');
        }

        $ok = $this->settings->saveCompanyInfo([
            'company_name'   => $companyName,
            'contact_email'  => $contactEmail,
            'contact_phone'  => trim($data['contact_phone'] ?? ''),
            'contact_person' => $contactPerson,
            'address'        => trim($data['address'] ?? ''),
            'description'    => trim($data['description'] ?? ''),
        ]);

        if (!$ok) {
            throw new RuntimeException('Không lưu được thông tin công ty.');
        }
    }

    public function changePassword(array $data): void
    {
        $newPassword     = (string)($data['new_password'] ?? '');
        $confirmPassword = (string)($data['confirm_password'] ?? '');

        if ($newPassword === '' || $confirmPassword === '') {
            throw new InvalidArgumentException('Vui lòng nhập đầy đủ mật khẩu mới và xác nhận.');
        }
        if ($newPassword !== $confirmPassword) {
            throw new InvalidArgumentException('Mật khẩu xác nhận không khớp.');
        }
        if (strlen($newPassword) < 6) {
            throw new InvalidArgumentException('Mật khẩu phải có ít nhất 6 ký tự.');
        }

        $ok = $this->settings->changePassword($newPassword);
        if (!$ok) {
            throw new RuntimeException('Không đổi được mật khẩu.');
        }
    }

    public function saveSystem(array $data): void
    {
        $timezone = $data['timezone'] ?? '+7';
        $format   = $data['date_format'] ?? 'dd/mm/yyyy';
        $currency = $data['currency'] ?? 'VND';

        // Optional validate
        $validFormats = ['dd/mm/yyyy', 'mm/dd/yyyy', 'yyyy-mm-dd'];
        if (!in_array($format, $validFormats, true)) {
            throw new InvalidArgumentException('Định dạng ngày không hợp lệ.');
        }

        $validCurrencies = ['VND', 'USD', 'EUR'];
        if (!in_array($currency, $validCurrencies, true)) {
            throw new InvalidArgumentException('Đơn vị tiền tệ không hợp lệ.');
        }

        $ok = $this->settings->saveSystemSettings([
            'timezone'    => $timezone,
            'date_format' => $format,
            'currency'    => $currency,
        ]);

        if (!$ok) {
            throw new RuntimeException('Không lưu được cài đặt hệ thống.');
        }
    }
}
