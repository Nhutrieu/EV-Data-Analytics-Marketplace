const express = require("express");
const router = express.Router();
const db = require("../models/db");

router.get("/", (req, res) => {
  const sql = `
    SELECT t.id, d.name AS dataset_name, t.buyer, t.amount, t.method, t.status, t.timestamp
    FROM transactions t
    JOIN datasets d ON t.dataset_id = d.id
    ORDER BY t.timestamp DESC
  `;
  db.query(sql, (err, result) => {
    if (err) return res.status(500).json({ error: err });
    res.json(result);
  });
});

module.exports = router;
