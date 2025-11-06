const express = require('express');
const router = express.Router();

// Replace with your actual handlers
router.get('/', (req, res) => res.json({ message: 'crimes list' }));
router.get('/:id', (req, res) => res.json({ message: `crime ${req.params.id}` }));

module.exports = router;