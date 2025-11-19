<?php
// classes/Privacy.php

class Privacy
{
    private PDO $db;
    private string $table = 'privacy_settings';
    private int $providerId;

    public function __construct(PDO $db, int $providerId)
    {
        $this->db = $db;
        $this->providerId = $providerId;
    }

    /**
     * Lấy cài đặt bảo mật của provider hiện tại.
     * Nếu chưa có thì trả về default.
     */
    public function getSettings(): array
    {
        $stmt = $this->db->prepare("
            SELECT provider_id, anonymize, standard, retention_months, access_control, updated_at
            FROM {$this->table}
            WHERE provider_id = :pid
            LIMIT 1
        ");
        $stmt->execute([':pid' => $this->providerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // Default nếu provider chưa cấu hình gì
            return [
                'provider_id'      => $this->providerId,
                'anonymize'        => true,
                'standard'         => 'GDPR',
                'retention_months' => 12,
                'access_control'   => 'verified-buyers',
                'updated_at'       => null,
            ];
        }

        // Ép kiểu
        $row['provider_id']      = (int)$row['provider_id'];
        $row['anonymize']        = (bool)$row['anonymize'];
        $row['retention_months'] = (int)$row['retention_months'];

        return $row;
    }

    /**
     * Lưu / update cài đặt bảo mật cho provider.
     */
    public function saveSettings(array $data): bool
    {
        $anonymize = !empty($data['anonymize']) ? 1 : 0;
        $standard  = $data['standard'] ?? 'GDPR';
        $retention = isset($data['retention_months'])
            ? (int)$data['retention_months']
            : 12;
        $access    = $data['access_control'] ?? 'verified-buyers';

        $sql = "
            INSERT INTO {$this->table}
                (provider_id, anonymize, standard, retention_months, access_control)
            VALUES
                (:pid, :anonymize, :standard, :retention, :access_control)
            ON DUPLICATE KEY UPDATE
                anonymize        = VALUES(anonymize),
                standard         = VALUES(standard),
                retention_months = VALUES(retention_months),
                access_control   = VALUES(access_control),
                updated_at       = CURRENT_TIMESTAMP
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':pid'           => $this->providerId,
            ':anonymize'     => $anonymize,
            ':standard'      => $standard,
            ':retention'     => $retention,
            ':access_control'=> $access,
        ]);
    }
}
