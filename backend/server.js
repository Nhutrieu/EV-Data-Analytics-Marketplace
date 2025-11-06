// ==================== IMPORTS ====================
const express = require('express');
const cors = require('cors');
const path = require('path');
require('dotenv').config();

// ==================== APP CONFIG ====================
const app = express();
app.use(cors());
app.use(express.json());

// ==================== ROUTES API ====================
const dashboardRoutes = require('./routes/dashboard');
const dataRoutes = require('./routes/data');
const pricingRoutes = require('./routes/pricing');
const privacyRoutes = require('./routes/privacy');
const revenueRoutes = require('./routes/revenue');
const settingsRoutes = require('./routes/settings');

app.use('/api/dashboard', dashboardRoutes);
app.use('/api/data', dataRoutes);
app.use('/api/pricing', pricingRoutes);
app.use('/api/privacy', privacyRoutes);
app.use('/api/revenue', revenueRoutes);
app.use('/api/settings', settingsRoutes);

// ==================== SERVE FRONTEND ====================
// Phục vụ toàn bộ folder frontend
app.use(express.static(path.join(__dirname, '../frontend')));

// Route trực tiếp cho dashboard.html
app.get('/dashboard.html', (req, res) => {
  res.sendFile(path.join(__dirname, '../frontend/pages/dashboard.html'));
});

// Route cho các page khác
app.get('/:page', (req, res) => {
  const allowedPages = ['dashboard', 'data', 'pricing', 'privacy', 'revenue', 'settings'];
  const pageName = req.params.page;
  if (allowedPages.includes(pageName)) {
    res.sendFile(path.join(__dirname, '../frontend/pages', `${pageName}.html`));
  } else {
    res.status(404).send('❌ Trang không tồn tại');
  }
});

// Route gốc → mở dashboard
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, '../frontend/pages/dashboard.html'));
});

// ==================== START SERVER ====================
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 Server đang chạy tại http://localhost:${PORT}`);
});
