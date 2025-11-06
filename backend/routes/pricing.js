const express = require('express');
const router = express.Router();
const db = require('../models/db');

// Lưu chính sách giá mặc định
router.post('/save-pricing', (req, res) => {
  const { model, price, currency } = req.body;
  const sql = `
    REPLACE INTO pricing_policy (id, model, price, currency)
    VALUES (1, ?, ?, ?)
  `;
  db.query(sql, [model, price, currency], err => {
    if (err) return res.status(500).send('Lỗi server');
    res.send({ success: true });
  });
});

// Lưu điều khoản sử dụng
router.post('/save-terms', (req, res) => {
  const { usage_rights, license_terms } = req.body;
  const sql = `
    UPDATE pricing_policy
    SET usage_rights = ?, license_terms = ?
    WHERE id = 1
  `;
  db.query(sql, [usage_rights, license_terms], err => {
    if (err) return res.status(500).send('Lỗi server');
    res.send({ success: true });
  });
});

// Lấy chính sách hiện tại
router.get('/get', (req, res) => {
  db.query('SELECT * FROM pricing_policy WHERE id = 1', (err, result) => {
    if (err) return res.status(500).send('Lỗi server');
    res.send(result[0]);
  });
});

// Lấy danh sách chính sách giá (cho frontend hiển thị)
router.get('/', (req, res) => {
  const sql = `SELECT * FROM pricing_policy`;
  db.query(sql, (err, result) => {
    if (err) return res.status(500).send('Lỗi khi lấy chính sách giá');
    res.json(result);
  });
});

module.exports = router;
