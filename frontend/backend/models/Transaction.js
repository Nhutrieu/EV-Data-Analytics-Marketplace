const db = require('../config/database');

class Transaction {
    // Tạo transaction mới
    static create(transactionData, callback) {
        const query = `
            INSERT INTO transactions (dataset_id, buyer_name, buyer_email, amount, currency, payment_method, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        `;
        
        db.query(query, [
            transactionData.dataset_id,
            transactionData.buyer_name,
            transactionData.buyer_email,
            transactionData.amount,
            transactionData.currency,
            transactionData.payment_method,
            transactionData.status || 'pending'
        ], callback);
    }

    // Lấy tất cả transactions của user
    static findByUserId(userId, callback) {
        const query = `
            SELECT t.*, d.name as dataset_name 
            FROM transactions t
            JOIN datasets d ON t.dataset_id = d.id
            WHERE d.user_id = ?
            ORDER BY t.created_at DESC
        `;
        db.query(query, [userId], callback);
    }

    // Thống kê doanh thu
    static getRevenueStats(userId, callback) {
        const query = `
            SELECT 
                COUNT(*) as total_transactions,
                SUM(amount) as total_revenue,
                COUNT(DISTINCT buyer_email) as total_buyers
            FROM transactions t
            JOIN datasets d ON t.dataset_id = d.id
            WHERE d.user_id = ? AND t.status = 'completed'
        `;
        db.query(query, [userId], callback);
    }
}

module.exports = Transaction;