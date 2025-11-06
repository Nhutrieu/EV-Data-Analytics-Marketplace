const express = require("express");
const router = express.Router();
const db = require("../models/db");

router.get("/", (req, res) => {
  db.query("SELECT * FROM privacy_policy", (err, result) => {
    if (err) return res.status(500).json({ error: err });
    res.json(result);
  });
});

module.exports = router;
