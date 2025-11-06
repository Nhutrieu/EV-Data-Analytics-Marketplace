const express = require('express');
const router = express.Router();
const Dataset = require('../models/Dataset');
const Pricing = require('../models/Pricing');

// Public routes - không cần auth

// Lấy tất cả datasets (public)
router.get('/datasets', (req, res) => {
    Dataset.findByUserId(1, (err, results) => {
        if (err) {
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

// Lấy pricing policy (public)
router.get('/pricing', (req, res) => {
    Pricing.findByUserId(1, (err, results) => {
        if (err) {
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

// Tạo dataset mới (public - cho demo)
router.post('/datasets', (req, res) => {
    const datasetData = {
        user_id: 1,
        ...req.body
    };

    Dataset.create(datasetData, (err, results) => {
        if (err) {
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

module.exports = router;