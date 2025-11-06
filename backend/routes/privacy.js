const express = require('express');
const router = express.Router();
const db = require('../models/db');

// Lưu cài đặt bảo mật
router.post('/save-settings', (req, res) => {
  const { anonymize, standard, retention, access } = req.body;
  const sql = `
    REPLACE INTO privacy_settings (id, anonymize, standard, retention_months, access_control)
    VALUES (1, ?, ?, ?, ?)
  `;
  db.query(sql, [anonymize, standard, retention, access], err => {
    if (err) return res.status(500).send('Lỗi server');
    res.send({ success: true });
  });
});

// Lấy cài đặt hiện tại
router.get('/get-settings', (req, res) => {
  db.query('SELECT * FROM privacy_settings WHERE id = 1', (err, result) => {
    if (err) return res.status(500).send('Lỗi server');
    res.send(result[0]);
  });
});

module.exports = router;
