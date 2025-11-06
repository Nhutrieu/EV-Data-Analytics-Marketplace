const express = require('express');
const router = express.Router();
const db = require('../models/db'); // ✅ đường dẫn đúng

router.get('/', (req, res) => {
  db.query('SELECT * FROM system_settings LIMIT 1', (err, result) => {
    if (err) {
      console.error('Lỗi truy vấn system_settings:', err);
      return res.status(500).send('Lỗi truy vấn');
    }
    res.json(result[0]);
  });
});

module.exports = router;
