const express = require('express');
const cors = require('cors');
const mysql = require('mysql2');
require('dotenv').config();

const app = express();

// Middleware - cho phép tất cả origins
app.use(cors({
    origin: ['http://localhost', 'http://127.0.0.1', 'file://'],
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
    credentials: true
}));

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Kết nối database
const connection = mysql.createConnection({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'ev_data_marketplace'
});

// Test route
app.get('/api/test', (req, res) => {
    res.json({
        success: true,
        message: '🚀 Backend EV Data Marketplace đang chạy!',
        database: process.env.DB_NAME,
        timestamp: new Date().toLocaleString('vi-VN')
    });
});

// Lấy tất cả datasets
app.get('/api/datasets', (req, res) => {
    connection.query('SELECT * FROM datasets WHERE user_id = 1 ORDER BY created_at DESC', (err, results) => {
        if (err) {
            console.error('Lỗi database:', err);
            return res.status(500).json({
                success: false,
                message: 'Lỗi server',
                error: err.message
            });
        }
        
        res.json({
            success: true,
            data: results
        });
    });
});

// Lấy pricing policy
app.get('/api/pricing', (req, res) => {
    connection.query('SELECT * FROM pricing_policies WHERE user_id = 1', (err, results) => {
        if (err) {
            console.error('Lỗi database:', err);
            return res.status(500).json({
                success: false,
                message: 'Lỗi server',
                error: err.message
            });
        }
        
        if (results.length === 0) {
            return res.json({
                success: true,
                data: {
                    pricing_model: "per_download",
                    base_price: "199.99",
                    currency: "VND",
                    usage_rights: "research_only",
                    license_terms: "Giấy phép mặc định"
                }
            });
        }
        
        res.json({
            success: true,
            data: results[0]
        });
    });
});

// Tạo dataset mới
app.post('/api/datasets', (req, res) => {
    const datasetData = {
        user_id: 1,
        name: req.body.name,
        description: req.body.description,
        type: req.body.type,
        format: req.body.format,
        price: req.body.price,
        price_unit: req.body.price_unit,
        file_name: req.body.file_name,
        file_path: req.body.file_path || '/uploads/' + req.body.file_name,
        file_size: req.body.file_size,
        tags: req.body.tags,
        status: 'pending',
        downloads: 0
    };

    const query = `
        INSERT INTO datasets (user_id, name, description, type, format, price, price_unit, file_name, file_path, file_size, tags, status, downloads)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `;
    
    connection.query(query, [
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
        datasetData.status,
        datasetData.downloads
    ], (err, results) => {
        if (err) {
            console.error('Lỗi khi tạo dataset:', err);
            return res.status(500).json({
                success: false,
                message: 'Lỗi khi tạo dataset',
                error: err.message
            });
        }

        res.json({
            success: true,
            message: 'Tạo dataset thành công',
            data: { id: results.insertId, ...datasetData }
        });
    });
});

// Cập nhật pricing policy
app.put('/api/pricing', (req, res) => {
    const updateData = req.body;

    // Kiểm tra xem đã có pricing policy chưa
    connection.query('SELECT id FROM pricing_policies WHERE user_id = 1', (err, results) => {
        if (err) {
            return res.status(500).json({
                success: false,
                message: 'Lỗi server',
                error: err.message
            });
        }

        let query, params;
        
        if (results.length > 0) {
            // Update existing
            query = `
                UPDATE pricing_policies 
                SET pricing_model = ?, base_price = ?, currency = ?, usage_rights = ?, license_terms = ?
                WHERE user_id = ?
            `;
            params = [
                updateData.pricing_model,
                updateData.base_price,
                updateData.currency,
                updateData.usage_rights,
                updateData.license_terms,
                1
            ];
        } else {
            // Insert new
            query = `
                INSERT INTO pricing_policies (user_id, pricing_model, base_price, currency, usage_rights, license_terms)
                VALUES (?, ?, ?, ?, ?, ?)
            `;
            params = [
                1,
                updateData.pricing_model,
                updateData.base_price,
                updateData.currency,
                updateData.usage_rights,
                updateData.license_terms
            ];
        }

        connection.query(query, params, (err, results) => {
            if (err) {
                console.error('Lỗi khi cập nhật pricing:', err);
                return res.status(500).json({
                    success: false,
                    message: 'Lỗi khi cập nhật pricing',
                    error: err.message
                });
            }

            res.json({
                success: true,
                message: 'Cập nhật pricing thành công'
            });
        });
    });
});

// Cập nhật trạng thái dataset
app.patch('/api/datasets/:id/status', (req, res) => {
    const { status } = req.body;
    const datasetId = req.params.id;

    connection.query('UPDATE datasets SET status = ? WHERE id = ?', [status, datasetId], (err, results) => {
        if (err) {
            return res.status(500).json({
                success: false,
                message: 'Lỗi khi cập nhật trạng thái',
                error: err.message
            });
        }

        res.json({
            success: true,
            message: 'Cập nhật trạng thái thành công'
        });
    });
});

// Xóa dataset
app.delete('/api/datasets/:id', (req, res) => {
    const datasetId = req.params.id;

    connection.query('DELETE FROM datasets WHERE id = ?', [datasetId], (err, results) => {
        if (err) {
            return res.status(500).json({
                success: false,
                message: 'Lỗi khi xóa dataset',
                error: err.message
            });
        }

        res.json({
            success: true,
            message: 'Xóa dataset thành công'
        });
    });
});

const PORT = process.env.PORT || 5000;

app.listen(PORT, () => {
    console.log('=================================');
    console.log('🚀 EV Data Marketplace Backend');
    console.log('=================================');
    console.log(`📍 Port: ${PORT}`);
    console.log(`📊 Database: ${process.env.DB_NAME}`);
    console.log(`🌐 URL: http://localhost:${PORT}`);
    console.log(`🔗 Test: http://localhost:${PORT}/api/test`);
    console.log(`📁 Datasets: http://localhost:${PORT}/api/datasets`);
    console.log(`💰 Pricing: http://localhost:${PORT}/api/pricing`);
    console.log('=================================');
});

// Thêm route cho root
app.get('/', (req, res) => {
    res.json({
        success: true,
        message: '🚀 EV Data Marketplace Backend API',
        version: '1.0.0',
        endpoints: {
            test: 'GET /api/test',
            datasets: 'GET /api/datasets',
            pricing: 'GET /api/pricing',
            create_dataset: 'POST /api/datasets',
            update_pricing: 'PUT /api/pricing'
        },
        frontend: 'Mở file frontend/pages/*.html để sử dụng giao diện'
    });
});

// Phục vụ file tĩnh
app.use(express.static('public'));

// Route cho root
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});