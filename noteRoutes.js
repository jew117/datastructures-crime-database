const express = require('express');
const router = express.Router();

router.get('/', (req, res) => res.json({ message: 'notes list' }));
router.post('/', (req, res) => res.json({ message: 'note created', body: req.body }));

module.exports = router;