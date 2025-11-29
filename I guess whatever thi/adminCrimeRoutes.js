const express = require('express');
const router = express.Router();

// Example admin endpoints — adapt to your real handlers
router.get('/', (req, res) => {
  res.json({ message: 'admin crimes list' });
});

router.get('/:id', (req, res) => {
  res.json({ message: `admin crime ${req.params.id}` });
});

// Export router
module.exports = router;