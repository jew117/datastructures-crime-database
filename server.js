const express = require('express');
const session = require('express-session');
const path = require('path');
const app = express();

// Helper: determine if value is an Express router-like object/function
function looksLikeRouter(r) {
  return typeof r === 'function' || (r && Array.isArray(r.stack));
}

// Safe require that accepts common export shapes (module.exports = router, exports.default, or { router })
function safeRequire(relPath) {
  try {
    const mod = require(relPath);
    // try default export (ESM transpiled), or .router property, or the module itself
    const candidate = (mod && mod.default) || (mod && mod.router) || mod;
    if (looksLikeRouter(candidate)) return candidate;
    const modKeys = mod && typeof mod === 'object' ? Object.keys(mod).join(',') : typeof mod;
    throw new TypeError(`Module at "${relPath}" is not an Express router. export keys: ${modKeys}`);
  } catch (err) {
    console.error(`Failed to load route "${relPath}":`, err.message);
    throw err;
  }
}

// Import routes (use safeRequire to get clearer errors)
const authRoutes = safeRequire('./routes/authRoutes');
const crimeRoutes = safeRequire('./routes/crimeRoutes');
const noteRoutes = safeRequire('./routes/noteRoutes');
const adminCrimeRoutes = safeRequire('./routes/adminCrimeRoutes');

// Middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(
  session({
    secret: 'super-secret-key', // Change this later
    resave: false,
    saveUninitialized: false,
  })
);
app.use(express.static(path.join(__dirname, '..', 'public')));
app.use('/auth', authRoutes);
app.use('/crimes', crimeRoutes);
app.use('/notes', noteRoutes);
app.use('/admin', adminCrimeRoutes);

// Start server
app.listen(3000, () => {
  console.log('✅ Server running on http://localhost:3000');
});
