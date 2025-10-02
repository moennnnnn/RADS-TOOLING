  <?php
// /index.php — PUBLIC landing page (no guard here)
require_once __DIR__ . '/../backend/config/app.php';
session_start();
$user = $_SESSION['user'] ?? null;
$isCustomer = $user && (($user['aud'] ?? '') === 'customer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RADS TOOLING</title>
  <!-- Standardize to lowercase paths and absolute URLs -->
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/Homepage.css" />
  <link rel="stylesheet" href="/assets/css/about.css" />
  <link rel="stylesheet" href="/assets/css/helpcenter.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<body>
<div class="page-wrapper">

<header class="navbar">
  <div class="navbar-top">
    <div class="logo">
      <span class="big-blue-italic">R</span><span class="black-italic">ADS </span>
      <span class="big-blue-italic">T</span><span class="black-italic">OOLING</span>
    </div>

    <form class="search-bar" action="/products.php" method="get">
      <input type="text" name="q" placeholder="Search..." />
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <div class="nav-icons">
      <?php if ($isCustomer): ?>
        <a href="/RADS-TOOLING/customer/account.php"><i class="fas fa-user"></i> My Account</a>
        <a href="/RADS-TOOLING/cart.php"><i class="fas fa-shopping-cart"></i></a>
        <form method="post" action="/backend/api/auth.php?action=logout" style="display:inline">
          <button class="linklike" style="background:none;border:0;cursor:pointer"><i class="fas fa-right-from-bracket"></i> Logout</button>
        </form>
      <?php else: ?>
        <a href="/RADS-TOOLING/customer/cust_login.php"><i class="fas fa-user"></i> Login</a>
        <a href="/RADS-TOOLING/customer/register.php"><i class="fas fa-user-plus"></i> Signup</a>
        <a href="/RADS-TOOLING/cart.php"><i class="fas fa-shopping-cart"></i></a>
      <?php endif; ?>
      <a href="/RADS-TOOLING/admin/login.php" title="Staff"><i class="fas fa-lock"></i></a>
    </div>
  </div>

  <div class="navbar-bottom">
    <nav>
      <a href="/" class="active">Home</a>
      <a href="/RADS-TOOLING/public/about.php">About Us</a>
      <a href="/RADS-TOOLING/public/products.php">Products</a>
    </nav>
  </div>
</header>

<main>
  <!-- Hero -->
  <section class="hero-section">
    <div class="hero-text">
      <h2><span class="big-blue-italic">C</span>ustomize your <br> cabinets online</h2>
      <p>Choose your style, size, select finishes, and visualize your design in real time.</p>
      <div style="margin-top:12px">
        <a class="login-btn" href="/customize.php">Start Customizing</a>
        <a class="login-btn" style="background:#6e7ca1" href="/products.php">Browse Cabinets</a>
      </div>
    </div>
    <div class="hero-image">
      <img src="/assets/images/cabinet-hero.jpg" alt="Cabinet Preview" />
    </div>
  </section>

  <!-- Cabinets Carousel -->
  <section class="cabinets-section">
    <h3>Cabinets</h3>
    <div class="carousel">
      <button class="carousel-btn prev" type="button">&#8249;</button>
      <div class="carousel-track">
        <!-- Replace with DB-driven images later -->
        <img src="/assets/images/cabs/cab1.jpg" alt="Cabinet 1" onerror="this.src='https://via.placeholder.com/300x350?text=Cabinet+1'">
        <img src="/assets/images/cabs/cab2.jpg" alt="Cabinet 2" onerror="this.src='https://via.placeholder.com/300x350?text=Cabinet+2'">
        <img src="/assets/images/cabs/cab3.jpg" alt="Cabinet 3" onerror="this.src='https://via.placeholder.com/300x350?text=Cabinet+3'">
        <img src="/assets/images/cabs/cab4.jpg" alt="Cabinet 4" onerror="this.src='https://via.placeholder.com/300x350?text=Cabinet+4'">
        <img src="/assets/images/cabs/cab5.jpg" alt="Cabinet 5" onerror="this.src='https://via.placeholder.com/300x350?text=Cabinet+5'">
      </div>
      <button class="carousel-btn next" type="button">&#8250;</button>
    </div>
  </section>

  <!-- Video -->
  <section class="video-section">
    <div class="video-flex">
      <div class="video-caption">
        <h2><span class="big-blue-italic">C</span>rafted with passion and precision</h2>
        <p>Choose your style, size, select finishes, and visualize your design in real time.</p>
      </div>
      <div class="video-wrapper">
        <video autoplay muted loop playsinline controls>
          <source src="/assets/videos/crafting.mp4" type="video/mp4" />
          Your browser does not support the video tag.
        </video>
      </div>
    </div>
  </section>

  <!-- Chat -->
  <button class="message-btn" id="helpBtn">Need Help?</button>
  <div id="chatPopup" class="chat-popup" role="dialog" aria-modal="true" aria-label="Support Chat">
    <div class="chat-header">
      Chat with Us
      <span id="chatClose" style="float:right; cursor:pointer;">&times;</span>
    </div>
    <div class="chat-body"></div>
    <div class="chat-input">
      <input type="text" placeholder="Write a message..." />
      <button class="chat-send" type="button">&#10148;</button>
    </div>
  </div>
</main>

<footer class="custom-footer">
  <div class="footer-columns">
    <div>
      <p class="footer-heading">About Store</p>
      <a href="/help.php">Help Center</a>
      <a href="/faqs.php">FAQs</a>
      <a href="/feedback.php">Feedbacks</a>
    </div>
    <div>
      <p class="footer-heading">Categories</p>
      <a href="/products.php?cat=living">Living Room</a>
      <a href="/products.php?cat=bedroom">Bedroom</a>
      <a href="/products.php?cat=kitchen">Kitchen</a>
      <a href="/products.php?cat=bathroom">Bathroom</a>
      <a href="/products.php?cat=commercial">Commercial Cabinet</a>
    </div>
    <div class="footer-contact">
      <p class="footer-heading brand-name">
        <span class="blue-italic">R</span>ADS <span class="blue-italic">T</span>OOLING
      </p>
      <a href="mailto:RadsTooling@gmail.com"><i class="fas fa-envelope"></i> RadsTooling@gmail.com</a>
      <a href="https://facebook.com/RadsTooling" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i> Rads Tooling</a>
      <a href="https://instagram.com/RadsTooling" target="_blank" rel="noopener"><i class="fab fa-instagram"></i> @RadsTooling</a>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy;<?= date('Y') ?> <em>Rads Tooling INC.</em></p>
    <div class="footer-bottom-links">
      <a href="/privacy.php">Privacy Policy</a>
      <a href="/terms.php">Terms and Conditions</a>
    </div>
  </div>
</footer>

</div><!-- /.page-wrapper -->

<script>
  // Carousel
  (function(){
    const track = document.querySelector('.carousel-track');
    const nextBtn = document.querySelector('.carousel-btn.next');
    const prevBtn = document.querySelector('.carousel-btn.prev');
    const scrollAmount = 320;
    nextBtn?.addEventListener('click', () => track?.scrollBy({ left: scrollAmount, behavior: 'smooth' }));
    prevBtn?.addEventListener('click', () => track?.scrollBy({ left: -scrollAmount, behavior: 'smooth' }));
  })();

  // Chat
  (function(){
    const helpBtn = document.getElementById("helpBtn");
    const chatPopup = document.getElementById("chatPopup");
    const chatClose = document.getElementById("chatClose");
    helpBtn?.addEventListener('click', () => { chatPopup.style.display = "flex"; helpBtn.style.display = "none"; });
    chatClose?.addEventListener('click', () => { chatPopup.style.display = "none"; helpBtn.style.display = "block"; });
  })();

  // Optional: generic gate for buttons/links that need login
  // Add class="requires-auth" to any element to force login before proceeding.
  document.addEventListener('click', async (e) => {
    const el = e.target.closest('.requires-auth');
    if (!el) return;
    e.preventDefault();
    try {
      const r = await fetch('/backend/api/auth.php?action=check_session', {credentials:'same-origin'});
      const j = await r.json();
      if (j.success && j.who === 'customer') {
        location.href = el.getAttribute('href') || el.dataset.href || '/customer/index.php';
      } else {
        const next = el.getAttribute('href') || el.dataset.href || location.pathname;
        location.href = '/customer/login.php?next=' + encodeURIComponent(next);
      }
    } catch {
      const next = el.getAttribute('href') || el.dataset.href || location.pathname;
      location.href = '/customer/login.php?next=' + encodeURIComponent(next);
    }
  });
</script>
</body>
</html>
