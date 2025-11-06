const express = require('express');
const router = express.Router();
const db = require('../models/db');

// Lấy danh sách dữ liệu
router.get('/list', (req, res) => {
  db.query('SELECT * FROM datasets ORDER BY created_at DESC', (err, result) => {
    if (err) return res.status(500).send('Lỗi server');
    res.send(result);
  });
});

// Thêm dữ liệu mới
router.post('/add', (req, res) => {
  const {
    name, type, format, price, priceUnit,
    description, tags, fileName, fileSize
  } = req.body;

  const sql = `
    INSERT INTO datasets (
      name, type, format, price, price_unit,
      description, tags, file_name, file_size,
      status, admin_status, downloads
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', 0)
  `;

  db.query(sql, [name, type, format, price, priceUnit, description, tags, fileName, fileSize], (err) => {
    if (err) return res.status(500).send('Lỗi server');
    res.send({ success: true });
  });
});

// Cập nhật dữ liệu
router.put('/update/:id', (req, res) => {
  const id = req.params.id;
  const {
    name, type, format, price, priceUnit, description
  } = req.body;

  const sql = `
    UPDATE datasets
    SET name = ?, type = ?, format = ?, price = ?, price_unit = ?, description = ?
    WHERE id = ?
  `;

  db.query(sql, [name, type, format, price, priceUnit, description, id], (err) => {
    if (err) return res.status(500).send('Lỗi server');
    res.send({ success: true });
  });
});

// Tạm ngừng / tiếp tục dữ liệu
router.put('/status/:id', (req, res) => {
  const id = req.params.id;
  const { status } = req.body;

  db.query('UPDATE datasets SET status = ? WHERE id = ?', [status, id], (err) => {
    if (err) return res.status(500).send('Lỗi server');
    res.send({ success: true });
  });
});

// Xóa dữ liệu
router.delete('/delete/:id', (req, res) => {
  const id = req.params.id;

  db.query('DELETE FROM datasets WHERE id = ?', [id], (err) => {
    if (err) return res.status(500).send('Lỗi server');
    res.send({ success: true });
  });
});

// Gửi lại dữ liệu bị từ chối
router.put('/resubmit/:id', (req, res) => {
  const id = req.params.id;

  db.query(`
    UPDATE datasets
    SET status = 'pending', admin_status = 'pending', admin_note = ''
    WHERE id = ?
  `, [id], (err) => {
    if (err) return res.status(500).send('Lỗi server');
    res.send({ success: true });
  });
});

module.exports = router;
