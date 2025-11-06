const Dataset = require('../models/Dataset');
const path = require('path');
const fs = require('fs');

const datasetController = {
    // Lấy tất cả datasets của user
    getDatasets: (req, res) => {
        const userId = req.user.id;
        
        Dataset.findByUserId(userId, (err, results) => {
            if (err) {
                return res.status(500).json({
                    success: false,
                    message: 'Lỗi server',
                    error: err
                });
            }
            
            res.json({
                success: true,
                data: results
            });
        });
    },

    // Tạo dataset mới
    createDataset: (req, res) => {
        const userId = req.user.id;
        
        if (!req.file) {
            return res.status(400).json({
                success: false,
                message: 'Vui lòng tải lên file'
            });
        }

        const datasetData = {
            user_id: userId,
            name: req.body.name,
            description: req.body.description,
            type: req.body.type,
            format: req.body.format,
            price: req.body.price,
            price_unit: req.body.price_unit,
            file_name: req.file.originalname,
            file_path: `/uploads/${req.file.filename}`,
            file_size: req.file.size,
            tags: req.body.tags,
            status: 'pending'
        };

        Dataset.create(datasetData, (err, results) => {
            if (err) {
                // Xóa file đã upload nếu có lỗi
                if (req.file) {
                    fs.unlinkSync(req.file.path);
                }
                return res.status(500).json({
                    success: false,
                    message: 'Lỗi khi tạo dataset',
                    error: err
                });
            }

            res.json({
                success: true,
                message: 'Tạo dataset thành công',
                data: { id: results.insertId, ...datasetData }
            });
        });
    },

    // Cập nhật dataset
    updateDataset: (req, res) => {
        const datasetId = req.params.id;
        const updateData = req.body;

        Dataset.update(datasetId, updateData, (err, results) => {
            if (err) {
                return res.status(500).json({
                    success: false,
                    message: 'Lỗi khi cập nhật dataset',
                    error: err
                });
            }

            res.json({
                success: true,
                message: 'Cập nhật dataset thành công'
            });
        });
    },

    // Xóa dataset
    deleteDataset: (req, res) => {
        const datasetId = req.params.id;

        // Lấy thông tin file để xóa
        Dataset.findById(datasetId, (err, results) => {
            if (err || results.length === 0) {
                return res.status(404).json({
                    success: false,
                    message: 'Dataset không tồn tại'
                });
            }

            const dataset = results[0];
            
            // Xóa dataset từ database
            Dataset.delete(datasetId, (err, results) => {
                if (err) {
                    return res.status(500).json({
                        success: false,
                        message: 'Lỗi khi xóa dataset',
                        error: err
                    });
                }

                // Xóa file vật lý
                const filePath = path.join(__dirname, '..', dataset.file_path);
                if (fs.existsSync(filePath)) {
                    fs.unlinkSync(filePath);
                }

                res.json({
                    success: true,
                    message: 'Xóa dataset thành công'
                });
            });
        });
    },

    // Toggle trạng thái dataset
    toggleStatus: (req, res) => {
        const datasetId = req.params.id;
        const { status } = req.body;

        Dataset.updateStatus(datasetId, status, (err, results) => {
            if (err) {
                return res.status(500).json({
                    success: false,
                    message: 'Lỗi khi cập nhật trạng thái',
                    error: err
                });
            }

            res.json({
                success: true,
                message: 'Cập nhật trạng thái thành công'
            });
        });
    },

    // Download file
    downloadDataset: (req, res) => {
        const datasetId = req.params.id;

        Dataset.findById(datasetId, (err, results) => {
            if (err || results.length === 0) {
                return res.status(404).json({
                    success: false,
                    message: 'Dataset không tồn tại'
                });
            }

            const dataset = results[0];
            const filePath = path.join(__dirname, '..', dataset.file_path);

            if (!fs.existsSync(filePath)) {
                return res.status(404).json({
                    success: false,
                    message: 'File không tồn tại'
                });
            }

            // Tăng số lượt download
            Dataset.incrementDownloads(datasetId, (err) => {
                if (err) {
                    console.error('Lỗi khi tăng download count:', err);
                }
            });

            res.download(filePath, dataset.file_name);
        });
    }
};

module.exports = datasetController;