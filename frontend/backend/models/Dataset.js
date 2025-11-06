const db = require('../config/database');

class Dataset {
    static findByUserId(userId, callback) {
        const query = 'SELECT * FROM datasets WHERE user_id = ? ORDER BY created_at DESC';
        db.query(query, [userId], callback);
    }

    static create(datasetData, callback) {
        const query = `
            INSERT INTO datasets (user_id, name, description, type, format, price, price_unit, file_name, file_path, file_size, tags, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        `;
        
        db.query(query, [
            datasetData.user_id,
            datasetData.name,
            datasetData.description,
            datasetData.type,
            datasetData.format,
            datasetData.price,
            datasetData.price_unit,
            datasetData.file_name,
            datasetData.file_path,
            datasetData.file_size,
            datasetData.tags,
            datasetData.status || 'pending'
        ], callback);
    }

    static updateStatus(id, status, callback) {
        const query = 'UPDATE datasets SET status = ? WHERE id = ?';
        db.query(query, [status, id], callback);
    }

    static delete(id, callback) {
        const query = 'DELETE FROM datasets WHERE id = ?';
        db.query(query, [id], callback);
    }
}

module.exports = Dataset;