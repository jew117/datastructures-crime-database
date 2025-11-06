// src/routes/authRoutes.js
const express = require('express');
const router = express.Router();

router.get('/ping', (req, res) => {
  res.json({ ok: true });
});

// define real auth routes here...

module.exports = router;
