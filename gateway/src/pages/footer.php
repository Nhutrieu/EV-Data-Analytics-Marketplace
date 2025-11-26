<footer class="ev-footer">
  <div class="ev-footer-container">
    <!-- Giới thiệu -->
    <div class="ev-footer-col">
      <h3>EV Data Marketplace</h3>
      <p>Nền tảng chia sẻ & phân tích dữ liệu xe điện hàng đầu Việt Nam.</p>
    </div>

    <!-- Liên kết nhanh -->
    <div class="ev-footer-col">
      <h4>Liên kết nhanh</h4>
      <a href="home_logged.php">Trang chủ</a>
      <a href="datasets.php">Bộ dữ liệu</a>
      <a href="dashboard.php">Dashboard</a>
      <a href="contact.php">Liên hệ</a>
      <a href="about.php">Giới thiệu</a>
    </div>

    <!-- Kết nối mạng xã hội -->
    <div class="ev-footer-col">
      <h4>Kết nối</h4>
      <div class="socials">
        <a href="https://facebook.com/YourPage" target="_blank" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="https://linkedin.com/in/YourProfile" target="_blank" title="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
        <a href="https://github.com/YourRepo" target="_blank" title="GitHub"><i class="fa-brands fa-github"></i></a>
        <a href="https://twitter.com/YourProfile" target="_blank" title="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
      </div>
    </div>
  </div>

  <!-- Phần copyright -->
  
</footer>

<style>
.ev-footer {
  background: #0b1220;
  color: #c9d4e2;
  font-family: "Inter", sans-serif;
  border-top: 1px solid rgba(255,255,255,0.05);
  padding-top: 40px;
  margin-top: 60px;
}

.ev-footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 30px;
}

.ev-footer h3 {
  color: #06b6d4;
  margin-bottom: 12px;
}
.ev-footer h4 {
  color: #06b6d4;
  margin-bottom: 12px;
}

.ev-footer a {
  color: #9baec8;
  text-decoration: none;
  display: block;
  margin: 6px 0;
  transition: 0.3s;
}
.ev-footer a:hover {
  color: #06b6d4;
}

.socials {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}
.socials a {
  background: rgba(255,255,255,0.08);
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: #9baec8;
  transition: 0.3s;
}
.socials a:hover {
  background: #06b6d4;
  color: white;
  transform: translateY(-3px);
}

.ev-footer-bottom {
  text-align: center;
  border-top: 1px solid rgba(255,255,255,0.05);
  padding: 20px;
  font-size: 0.9rem;
  color: #9baec8;
  margin-top: 30px;
}

@media (max-width: 768px) {
  .ev-footer-container {
    grid-template-columns: 1fr;
    text-align: center;
    gap: 25px;
  }
  .socials {
    justify-content: center;
  }
}
</style>
