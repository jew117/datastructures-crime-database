const pool = require('../db');
const bcrypt = require('bcryptjs');

// Login
exports.login = async (req, res) => {
  const { username, password } = req.body;

  // Find user by username
  const [rows] = await pool.query(
    'SELECT user_id, username, password_hash, role FROM users WHERE username = ?',
    [username]
  );

  if (rows.length === 0) {
    return res.status(401).json({ error: 'Invalid username or password' });
  }

  const user = rows[0];
  const match = await bcrypt.compare(password, user.password_hash);

  if (!match) {
    return res.status(401).json({ error: 'Invalid username or password' });
  }

  // Store user info in session
  req.session.user = {
    id: user.user_id,
    username: user.username,
    role: user.role,
  };

  res.json({ message: 'Logged in successfully', role: user.role });
};

// Logout
exports.logout = (req, res) => {
  req.session.destroy(() => {
    res.json({ message: 'Logged out successfully' });
  });
};

// Get logged-in user details
exports.me = (req, res) => {
  if (!req.session.user) {
    return res.status(401).json({ loggedIn: false });
  }
  res.json({ loggedIn: true, user: req.session.user });
};
