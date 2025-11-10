<?php
// classes/Pricing.php

class Pricing
{
    private PDO $db;
    private string $table = 'pricing_policy';
    private int $providerId;

    public function __construct(PDO $db, int $providerId)
    {
        $this->db = $db;
        $this->providerId = $providerId;
    }

    public function getPolicy(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE provider_id = :pid
            LIMIT 1
        ");
        $stmt->execute([':pid' => $this->providerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'provider_id'  => $this->providerId,
                'model'        => 'per-download',
                'price'        => 500000,
                'currency'     => 'VND',
                'usage_rights' => 'commercial',
                'license_terms'=> 'Default license terms.',
                'updated_at'   => null,
            ];
        }

        $row['provider_id'] = (int)$row['provider_id'];
        $row['price']       = (int)$row['price'];

        return $row;
    }

    public function savePolicy(array $data): bool
    {
        $model        = $data['model']        ?? 'per-download';
        $price        = isset($data['price']) ? (int)$data['price'] : 500000;
        $currency     = $data['currency']     ?? 'VND';
        $usageRights  = $data['usage_rights'] ?? 'commercial';
        $licenseTerms = $data['license_terms']?? 'Default license terms.';

        $sql = "
            INSERT INTO {$this->table}
                (provider_id, model, price, currency, usage_rights, license_terms)
            VALUES
                (:pid, :model, :price, :currency, :usage_rights, :license_terms)
            ON DUPLICATE KEY UPDATE
                model         = VALUES(model),
                price         = VALUES(price),
                currency      = VALUES(currency),
                usage_rights  = VALUES(usage_rights),
                license_terms = VALUES(license_terms),
                updated_at    = CURRENT_TIMESTAMP
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':pid'           => $this->providerId,
            ':model'         => $model,
            ':price'         => $price,
            ':currency'      => $currency,
            ':usage_rights'  => $usageRights,
            ':license_terms' => $licenseTerms,
        ]);
    }
}
