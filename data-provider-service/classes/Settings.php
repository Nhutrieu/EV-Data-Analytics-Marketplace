<?php
// classes/Settings.php

class Settings
{
    private PDO $db;
    private int $providerId;

    private string $tableProviders = 'providers';
    private string $tableSettings  = 'provider_settings';

    public function __construct(PDO $db, int $providerId)
    {
        $this->db         = $db;
        $this->providerId = $providerId;
    }

    /**
     * Thông tin công ty (company form)
     */
    public function getCompanyInfo(): array
    {
        $sql = "
            SELECT
                id,
                company_name,
                contact_email,
                contact_phone,
                contact_person,
                address,
                description,
                login_email
            FROM {$this->tableProviders}
            WHERE id = :pid
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pid' => $this->providerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // Nếu provider chưa có record (hiếm khi), trả default trống
            return [
                'id'             => $this->providerId,
                'company_name'   => '',
                'contact_email'  => '',
                'contact_phone'  => '',
                'contact_person' => '',
                'address'        => '',
                'description'    => '',
                'login_email'    => '',
            ];
        }

        $row['id'] = (int) $row['id'];

        return $row;
    }

    /**
     * Lưu thông tin công ty
     */
    public function saveCompanyInfo(array $data): bool
    {
        $sql = "
            UPDATE {$this->tableProviders}
            SET
                company_name   = :company_name,
                contact_email  = :contact_email,
                contact_phone  = :contact_phone,
                contact_person = :contact_person,
                address        = :address,
                description    = :description,
                updated_at     = CURRENT_TIMESTAMP
            WHERE id = :pid
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':company_name'   => $data['company_name']   ?? '',
            ':contact_email'  => $data['contact_email']  ?? '',
            ':contact_phone'  => $data['contact_phone']  ?? '',
            ':contact_person' => $data['contact_person'] ?? '',
            ':address'        => $data['address']        ?? '',
            ':description'    => $data['description']    ?? '',
            ':pid'            => $this->providerId,
        ]);
    }

    /**
     * Lấy thông tin login (email)
     * (Mật khẩu không bao giờ trả ra)
     */
    public function getLoginInfo(): array
    {
        $sql = "
            SELECT
                id,
                login_email
            FROM {$this->tableProviders}
            WHERE id = :pid
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pid' => $this->providerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'id'          => $this->providerId,
                'login_email' => '',
            ];
        }

        $row['id'] = (int) $row['id'];

        return $row;
    }

    /**
     * Đổi mật khẩu đăng nhập
     */
    public function changePassword(string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        $sql = "
            UPDATE {$this->tableProviders}
            SET password_hash = :hash, updated_at = CURRENT_TIMESTAMP
            WHERE id = :pid
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':hash' => $hash,
            ':pid'  => $this->providerId,
        ]);
    }

    /**
     * Lấy cài đặt hệ thống ( timezone, date_format, currency)
     */
    public function getSystemSettings(): array
    {
        $sql = "
            SELECT
                provider_id,
                timezone,
                date_format,
                currency,
                updated_at
            FROM {$this->tableSettings}
            WHERE provider_id = :pid
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pid' => $this->providerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'provider_id' => $this->providerId,
                'timezone'    => '+7',
                'date_format' => 'dd/mm/yyyy',
                'currency'    => 'VND',
                'updated_at'  => null,
            ];
        }

        $row['provider_id'] = (int) $row['provider_id'];

        return $row;
    }

    /**
     * Lưu cài đặt hệ thống
     */
    public function saveSystemSettings(array $data): bool
    {
      $timezone = $data['timezone']    ?? '+7';
$format   = $data['date_format'] ?? 'dd/mm/yyyy';
$currency = $data['currency']    ?? 'VND';

$sql = "
    INSERT INTO {$this->tableSettings}
        (provider_id, timezone, date_format, currency)
    VALUES
        (:pid, :tz, :fmt, :cur)
    ON DUPLICATE KEY UPDATE
        timezone   = VALUES(timezone),
        date_format= VALUES(date_format),
        currency   = VALUES(currency),
        updated_at = CURRENT_TIMESTAMP
";

$stmt = $this->db->prepare($sql);

return $stmt->execute([
    ':pid'  => $this->providerId,
    ':tz'   => $timezone,
    ':fmt'  => $format,
    ':cur'  => $currency,
]);

    }
}
