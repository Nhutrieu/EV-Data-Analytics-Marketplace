const express = require('express');
const router = express.Router();
const db = require('../models/db');

// Lấy thống kê tổng quan cho dashboard
router.get('/summary', (req, res) => {
  const stats = {};

  db.query('SELECT COUNT(*) AS datasets FROM datasets', (err, result) => {
    if (err) return res.status(500).send('Lỗi khi lấy số bộ dữ liệu');
    stats.datasets = result[0].datasets;

    db.query('SELECT SUM(downloads) AS downloads FROM datasets', (err, result) => {
      if (err) return res.status(500).send('Lỗi khi lấy số lượt tải');
      stats.downloads = result[0].downloads || 0;

      db.query('SELECT SUM(amount) AS revenue FROM transactions', (err, result) => {
        if (err) return res.status(500).send('Lỗi khi lấy doanh thu');
        stats.revenue = result[0].revenue || 0;

        db.query('SELECT COUNT(DISTINCT buyer) AS users FROM transactions', (err, result) => {
          if (err) return res.status(500).send('Lỗi khi lấy số người dùng');
          stats.users = result[0].users || 0;

          res.send(stats);
        });
      });
    });
  });
});

// Lấy hoạt động gần đây
router.get('/activity', (req, res) => {
  const sql = `
    SELECT type, actor, title, amount, timestamp
    FROM recent_activity
    ORDER BY timestamp DESC
    LIMIT 5
  `;
  db.query(sql, (err, result) => {
    if (err) return res.status(500).send('Lỗi khi lấy hoạt động gần đây');
    res.send(result);
  });
});

module.exports = router;
