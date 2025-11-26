<?php
// modules/RevenueModule.php

require_once __DIR__ . '/../classes/Revenue.php';

class RevenueModule
{
    private Revenue $revenue;

    /**
     * @param PDO $db
     * @param int $providerId  ID của nhà cung cấp dữ liệu hiện tại
     */
    public function __construct(PDO $db, int $providerId)
    {
        // Truyền providerId xuống model Revenue
        $this->revenue = new Revenue($db, $providerId);
    }

    /**
     * Lấy toàn bộ dữ liệu cho dashboard của 1 provider
     *
     * @param string|null $from Ngày bắt đầu (YYYY-MM-DD) hoặc null
     * @param string|null $to   Ngày kết thúc (YYYY-MM-DD) hoặc null
     *
     * @return array{
     *   summary: array,
     *   by_dataset: array,
     *   monthly: array,
     *   transactions: array
     * }
     */
    public function getDashboard(?string $from = null, ?string $to = null): array
    {
        // Các block chịu ảnh hưởng của filter from/to
        $summary      = $this->revenue->getSummary($from, $to);
        $byDataset    = $this->revenue->getRevenueByDataset($from, $to);
        $transactions = $this->revenue->getRecentTransactions(10, $from, $to); // 10 giao dịch gần nhất

        // Biểu đồ 12 tháng gần nhất (không phụ thuộc from/to)
        $monthly      = $this->revenue->getMonthlyRevenueLast12();

        return [
            'summary'      => $summary,
            'by_dataset'   => $byDataset,
            'monthly'      => $monthly,
            'transactions' => $transactions,
        ];
    }

    /**
     * Lấy giao dịch mới nhất (không filter from/to)
     */
    public function getRecentOnly(int $limit = 10): array
    {
        return $this->revenue->getRecentTransactions($limit, null, null);
    }
}
