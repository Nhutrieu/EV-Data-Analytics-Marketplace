const express = require('express');
const router = express.Router();
const Dataset = require('../models/Dataset');

// TẠM THỜI BỎ QUA AUTH - COMMENT HOẶC XÓA DÒNG NÀY
// router.use(auth);

// Lấy tất cả datasets
router.get('/', (req, res) => {
    // Giả sử user_id = 1 cho demo
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

// Tạo dataset mới
router.post('/', (req, res) => {
    const datasetData = {
        user_id: 1, // demo user
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

// Cập nhật trạng thái dataset
router.patch('/:id/status', (req, res) => {
    const { status } = req.body;
    const datasetId = req.params.id;

    Dataset.updateStatus(datasetId, status, (err, results) => {
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
router.delete('/:id', (req, res) => {
    const datasetId = req.params.id;

    Dataset.delete(datasetId, (err, results) => {
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

module.exports = router;