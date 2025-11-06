const Pricing = require('../models/Pricing');

const pricingController = {
    // Lấy pricing policy
    getPricing: (req, res) => {
        const userId = req.user.id;

        Pricing.findByUserId(userId, (err, results) => {
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

    // Cập nhật pricing policy
    updatePricing: (req, res) => {
        const userId = req.user.id;
        const updateData = req.body;

        Pricing.update(userId, updateData, (err, results) => {
            if (err) {
                return res.status(500).json({
                    success: false,
                    message: 'Lỗi khi cập nhật pricing policy',
                    error: err
                });
            }

            res.json({
                success: true,
                message: 'Cập nhật pricing policy thành công'
            });
        });
    }
};

module.exports = pricingController;