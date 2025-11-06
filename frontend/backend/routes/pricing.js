const express = require('express');
const router = express.Router();
const Pricing = require('../models/Pricing');

// TẠM THỜI BỎ QUA AUTH - COMMENT HOẶC XÓA DÒNG NÀY
// router.use(auth);

// Lấy pricing policy
router.get('/', (req, res) => {
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

// Cập nhật pricing policy
router.put('/', (req, res) => {
    const updateData = req.body;

    Pricing.update(1, updateData, (err, results) => {
        if (err) {
            return res.status(500).json({
                success: false,
                message: 'Lỗi khi cập nhật pricing policy',
                error: err.message
            });
        }

        res.json({
            success: true,
            message: 'Cập nhật pricing policy thành công'
        });
    });
});

module.exports = router;