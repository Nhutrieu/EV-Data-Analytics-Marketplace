<header class="site-header">
  <div class="container">
    <div class="logo">
      <a href="home_logged.php">
        <img src="../public/assets/images/logo.png" alt="EV Data" height="40">
        <span>EV Data Marketplace</span>
      </a>
    </div>
    <nav class="nav">
      <a href="home_logged.php" class="active">Trang chủ</a>
      <a href="datasets.php">Dữ liệu</a>
      <a href="dashboard.php">Dashboard</a>
      <a href="contact.php">Liên hệ</a>
      <form action="logout.php" method="POST" style="display:inline;">
        <button type="submit" class="btn-logout">Đăng xuất</button>
      </form>
    </nav>
  </div>
</header>

<style>
/* Header dark theme */
.site-header {
  background: var(--card);
  border-bottom: 1px solid rgba(255,255,255,0.05);
  padding: 12px 0;
  color: var(--muted);
}

.site-header .container {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo a {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: #e6eef6;
  font-weight: 700;
  font-size: 18px;
}

.logo img {
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(6,182,212,0.3);
}

.nav {
  display: flex;
  align-items: center;
  gap: 18px;
}

.nav a {
  color: var(--muted);
  text-decoration: none;
  font-weight: 600;
  position: relative;
  padding: 6px 8px;
  border-radius: 8px;
  transition: all 0.2s;
}

.nav a:hover,
.nav a.active {
  color: var(--accent);
  background: rgba(6,182,212,0.08);
}

.nav a.active::after {
  content: "";
  position: absolute;
  bottom: -4px;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--accent);
  border-radius: 4px;
}

.btn-logout {
  background: linear-gradient(90deg, var(--accent), var(--green));
  color: #04141a;
  border: none;
  padding: 8px 16px;
  font-weight: 700;
  border-radius: 10px;
  cursor: pointer;
  box-shadow: 0 0 10px rgba(6,182,212,0.3);
  transition: all 0.2s;
}

.btn-logout:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 20px rgba(6,182,212,0.45);
}

@media (max-width: 768px) {
  .site-header .container {
    flex-direction: column;
    gap: 10px;
  }
  .nav {
    flex-wrap: wrap;
    justify-content: center;
  }
}
</style>
