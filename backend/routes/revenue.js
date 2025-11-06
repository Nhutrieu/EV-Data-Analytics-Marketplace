const express = require('express');
const router = express.Router();
const db = require('../models/db');

// Tổng quan doanh thu
router.get('/summary', (req, res) => {
  const now = new Date();
  const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
  const stats = {};

  db.query('SELECT SUM(amount) AS total FROM transactions', (err, result) => {
    if (err) return res.status(500).send('Lỗi server');
    stats.total = result[0].total || 0;

    db.query('SELECT SUM(amount) AS month FROM transactions WHERE timestamp >= ?', [monthStart], (err, result) => {
      if (err) return res.status(500).send('Lỗi server');
      stats.month = result[0].month || 0;

      db.query('SELECT AVG(amount) AS avg FROM transactions', (err, result) => {
        if (err) return res.status(500).send('Lỗi server');
        stats.avg = result[0].avg || 0;

        db.query('SELECT COUNT(*) AS count FROM datasets', (err, result) => {
          if (err) return res.status(500).send('Lỗi server');
          stats.datasets = result[0].count;

          db.query('SELECT COUNT(DISTINCT buyer) AS users FROM transactions', (err, result) => {
            if (err) return res.status(500).send('Lỗi server');
            stats.users = result[0].users;
            res.send(stats);
          });
        });
      });
    });
  });
});

module.exports = router;
