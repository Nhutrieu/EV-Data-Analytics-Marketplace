const db = require('../config/database');

class Pricing {
    static findByUserId(userId, callback) {
        const query = 'SELECT * FROM pricing_policies WHERE user_id = ?';
        db.query(query, [userId], (err, results) => {
            if (err) return callback(err);
            
            if (results.length === 0) {
                // Tạo policy mặc định
                const defaultPolicy = {
                    user_id: userId,
                    pricing_model: 'per_download',
                    base_price: 199.99,
                    currency: 'VND',
                    usage_rights: 'research_only',
                    license_terms: 'Giấy phép mặc định'
                };
                
                this.create(defaultPolicy, callback);
            } else {
                callback(null, results[0]);
            }
        });
    }

    static create(policyData, callback) {
        const query = `
            INSERT INTO pricing_policies (user_id, pricing_model, base_price, currency, usage_rights, license_terms)
            VALUES (?, ?, ?, ?, ?, ?)
        `;
        
        db.query(query, [
            policyData.user_id,
            policyData.pricing_model,
            policyData.base_price,
            policyData.currency,
            policyData.usage_rights,
            policyData.license_terms
        ], callback);
    }

    static update(userId, updateData, callback) {
        const query = `
            UPDATE pricing_policies 
            SET pricing_model = ?, base_price = ?, currency = ?, usage_rights = ?, license_terms = ?
            WHERE user_id = ?
        `;
        
        db.query(query, [
            updateData.pricing_model,
            updateData.base_price,
            updateData.currency,
            updateData.usage_rights,
            updateData.license_terms,
            userId
        ], callback);
    }
}

module.exports = Pricing;