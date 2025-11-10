<?php
// classes/Revenue.php

class Revenue
{
    private PDO $db;
    private int $providerId;

    // Tên bảng – nếu bạn đổi tên bảng thì chỉnh lại ở đây
    private string $tableTx = 'transactions';
    private string $tableDs = 'datasets';

    /**
     * @param PDO $db
     * @param int $providerId ID của nhà cung cấp dữ liệu hiện tại
     */
    public function __construct(PDO $db, int $providerId)
    {
        $this->db         = $db;
        $this->providerId = $providerId;
    }

    /**
     * Helper: thêm điều kiện filter theo from/to (YYYY-MM-DD) cho alias bất kỳ.
     *
     * @param string|null $from   Ngày bắt đầu (YYYY-MM-DD) hoặc null
     * @param string|null $to     Ngày kết thúc (YYYY-MM-DD) hoặc null
     * @param array       $params Mảng param sẽ được bổ sung (truyền by reference)
     * @param string      $alias  Alias bảng (vd: "t", "d")
     *
     * @return string Chuỗi SQL kiểu: " AND t.timestamp >= :from AND t.timestamp <= :to"
     */
    private function buildDateFilter(?string $from, ?string $to, array &$params, string $alias = 't'): string
    {
        $sql = '';

        if (!empty($from)) {
            $sql .= " AND {$alias}.timestamp >= :from";
            $params[':from'] = $from . ' 00:00:00';
        }

        if (!empty($to)) {
            $sql .= " AND {$alias}.timestamp <= :to";
            $params[':to'] = $to . ' 23:59:59';
        }

        return $sql;
    }

    /**
     * Tổng quan doanh thu cho 1 provider
     *
     * - total_transactions: số lượt mua / tải (giao dịch completed)
     * - total_revenue: tổng amount
     * - total_datasets: số dataset đã có ít nhất 1 giao dịch
     * - total_buyers: số khách hàng khác nhau (buyer)
     */
    public function getSummary(?string $from, ?string $to): array
    {
        $params = [
            ':pid' => $this->providerId,
        ];

        $dateFilter = $this->buildDateFilter($from, $to, $params, 't');

        $sql = "
            SELECT
                COUNT(t.id)                      AS total_transactions,
                COALESCE(SUM(t.amount), 0)       AS total_revenue,
                COUNT(DISTINCT t.dataset_id)     AS total_datasets,
                COUNT(DISTINCT t.buyer)          AS total_buyers
            FROM {$this->tableTx} t
            INNER JOIN {$this->tableDs} d ON d.id = t.dataset_id
            WHERE d.provider_id = :pid
              AND t.status = 'completed'
              {$dateFilter}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'total_transactions' => 0,
                'total_revenue'      => 0,
                'total_datasets'     => 0,
                'total_buyers'       => 0,
            ];
        }

        // Ép kiểu cho chắc
        $row['total_transactions'] = (int) ($row['total_transactions'] ?? 0);
        $row['total_revenue']      = (int) ($row['total_revenue']      ?? 0);
        $row['total_datasets']     = (int) ($row['total_datasets']     ?? 0);
        $row['total_buyers']       = (int) ($row['total_buyers']       ?? 0);

        return $row;
    }

    /**
     * Doanh thu theo dataset (top bán chạy / chart)
     * Lấy tất cả dataset thuộc provider, kể cả dataset chưa có giao dịch.
     */
    public function getRevenueByDataset(?string $from, ?string $to): array
    {
        $params = [
            ':pid' => $this->providerId,
        ];

        // Filter ngày đặt trong điều kiện JOIN (vì chỉ áp dụng với transactions)
        $dateFilter = $this->buildDateFilter($from, $to, $params, 't');

        $sql = "
            SELECT
                d.id              AS dataset_id,
                d.name            AS name,
                d.type            AS type,
                d.price           AS price,
                COUNT(t.id)       AS transactions,
                COALESCE(SUM(t.amount), 0) AS revenue,
                MAX(t.timestamp)  AS last_purchase_at
            FROM {$this->tableDs} d
            LEFT JOIN {$this->tableTx} t
                ON t.dataset_id = d.id
               AND t.status = 'completed'
               {$dateFilter}
            WHERE d.provider_id = :pid
            GROUP BY d.id, d.name, d.type, d.price
            ORDER BY revenue DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['dataset_id']   = (int) ($r['dataset_id']   ?? 0);
            $r['price']        = (int) ($r['price']        ?? 0);
            $r['transactions'] = (int) ($r['transactions'] ?? 0);
            $r['revenue']      = (int) ($r['revenue']      ?? 0);
        }

        return $rows;
    }

    /**
     * Doanh thu 12 tháng gần nhất (line chart)
     *
     * - Mặc định: 12 tháng tính tới tháng hiện tại (không phụ thuộc from/to của dashboard).
     * - Kể cả những tháng không có doanh thu vẫn trả về revenue = 0.
     */
    public function getMonthlyRevenueLast12(): array
    {
        $params = [
            ':pid' => $this->providerId,
        ];

        // 12 tháng gần nhất tính từ tháng hiện tại (bao gồm tháng hiện tại)
        $sql = "
            SELECT
                YEAR(t.timestamp)  AS year,
                MONTH(t.timestamp) AS month,
                COALESCE(SUM(t.amount), 0) AS revenue
            FROM {$this->tableTx} t
            INNER JOIN {$this->tableDs} d ON d.id = t.dataset_id
            WHERE d.provider_id = :pid
              AND t.status = 'completed'
              AND t.timestamp >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 11 MONTH)
            GROUP BY YEAR(t.timestamp), MONTH(t.timestamp)
            ORDER BY YEAR(t.timestamp), MONTH(t.timestamp)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map YYYY-MM → revenue
        $map = [];
        foreach ($rows as $r) {
            $y   = (int) $r['year'];
            $m   = (int) $r['month'];
            $key = sprintf('%04d-%02d', $y, $m);
            $map[$key] = (int) ($r['revenue'] ?? 0);
        }

        // Build đủ 12 tháng liên tục (kể cả tháng chưa có doanh thu)
        $result  = [];
        $current = new DateTime('first day of this month');
        $current->modify('-11 months');

        for ($i = 0; $i < 12; $i++) {
            $year  = (int) $current->format('Y');
            $month = (int) $current->format('m');
            $key   = $current->format('Y-m');

            $result[] = [
                'year'    => $year,
                'month'   => $month,
                'label'   => 'T' . $month,   // Label cho chart (vd: T1, T2, ...)
                'revenue' => $map[$key] ?? 0,
            ];

            $current->modify('+1 month');
        }

        return $result;
    }

    /**
     * Lấy N giao dịch gần nhất cho provider (bảng "Khách hàng mua dữ liệu")
     * Có áp dụng filter from/to nếu truyền vào.
     *
     * @param int         $limit Số lượng giao dịch tối đa
     * @param string|null $from  Ngày từ (YYYY-MM-DD) hoặc null
     * @param string|null $to    Ngày đến (YYYY-MM-DD) hoặc null
     */
    public function getRecentTransactions(
        int $limit = 10,
        ?string $from = null,
        ?string $to = null
    ): array {
        $params = [
            ':pid' => $this->providerId,
        ];

        $dateFilter = $this->buildDateFilter($from, $to, $params, 't');

        $sql = "
            SELECT
                t.id,
                t.timestamp,
                t.buyer,
                t.amount,
                t.method,
                t.status,
                d.name  AS dataset_name,
                d.type  AS dataset_type
            FROM {$this->tableTx} t
            INNER JOIN {$this->tableDs} d
                ON t.dataset_id = d.id
            WHERE d.provider_id = :pid
              AND t.status = 'completed'
              {$dateFilter}
            ORDER BY t.timestamp DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);

        // Bind params từ mảng $params
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        // LIMIT bắt buộc bind kiểu INT
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['id']     = (int) ($r['id']     ?? 0);
            $r['amount'] = (int) ($r['amount'] ?? 0);
        }

        return $rows;
    }
}
