<?php
// /public/index.php - PUBLIC landing page (no auth required)
require_once __DIR__ . '/../backend/config/app.php';
require_once __DIR__ . '/../backend/lib/cms_helper.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// CRITICAL: Check if we're in preview mode
$isPreview = isset($GLOBALS['cms_preview_content']) && !empty($GLOBALS['cms_preview_content']);

// If in preview, use the content passed by cms_preview.php
// Otherwise, fetch published content for public view
if ($isPreview) {
  $cmsContent = $GLOBALS['cms_preview_content'];
} else {
  $cmsContent = getCMSContent('home_public'); // Gets published by default
}

$user = $_SESSION['user'] ?? null;
$isCustomer = $user && (($user['aud'] ?? '') === 'customer');

// If customer is logged in, redirect to customer homepage
if ($isCustomer) {
  header('Location: /RADS-TOOLING/customer/homepage.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RADS TOOLING - Custom Cabinet Solutions</title>
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/Homepage.css" />
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/chat-widget.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
  <div class="page-wrapper">

    <!-- HEADER -->
    <header class="navbar">
      <div class="navbar-container">
        <div class="navbar-brand">
          <a href="/RADS-TOOLING/public/index.php" class="logo-link">
            <span class="logo-text">R</span>ADS <span class="logo-text">T</span>OOLING
          </a>
        </div>

        <form class="search-container" action="/RADS-TOOLING/public/products.php" method="get">
          <input type="text" name="q" class="search-input" placeholder="Search cabinets..." />
          <button type="submit" class="search-btn" aria-label="Search">
            <span class="material-symbols-rounded">search</span>
          </button>
        </form>

        <div class="navbar-actions">
          <a href="/RADS-TOOLING/customer/cust_login.php" class="nav-link">
            <span class="material-symbols-rounded">login</span>
            <span>Login</span>
          </a>
          <a href="/RADS-TOOLING/customer/register.php" class="nav-link">
            <span class="material-symbols-rounded">person_add</span>
            <span>Sign Up</span>
          </a>
          <a href="/RADS-TOOLING/admin/login.php" class="nav-link-icon" title="Staff Login">
            <span class="material-symbols-rounded">admin_panel_settings</span>
          </a>
        </div>
      </div>

      <nav class="navbar-menu">
        <a href="/RADS-TOOLING/public/index.php" class="nav-menu-item active">Home</a>
        <a href="/RADS-TOOLING/public/about.php" class="nav-menu-item">About Us</a>
        <a href="/RADS-TOOLING/public/products.php" class="nav-menu-item">Products</a>
        <a href="#testimonials" class="nav-menu-item">Testimonials</a>
      </nav>
    </header>

    <main>
      <!-- HERO SECTION -->
      <section class="hero-section">
        <div class="hero-content-wrapper">
          <div class="hero-text-content">
            <?php echo $cmsContent['hero_headline'] ?? '<h1>Welcome to RADS Tooling</h1>'; ?>
            <?php echo $cmsContent['hero_subtitle'] ?? '<p>Your trusted partner in custom cabinets</p>'; ?>
            <div class="hero-actions">
              <a href="/RADS-TOOLING/customer/register.php" class="btn-cta-primary">
                <i class="fas fa-rocket"></i> Get Started Free
              </a>
              <a href="/RADS-TOOLING/public/products.php" class="btn-cta-secondary">
                <i class="fas fa-th-large"></i> Browse Gallery
              </a>
            </div>

            <!-- Trust Indicators -->
            <div class="trust-badges">
              <div class="badge">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $cmsContent['trust_badge_1'] ?? '17+ Years Experience'; ?></span>
              </div>
              <div class="badge">
                <i class="fas fa-star"></i>
                <span><?php echo $cmsContent['trust_badge_2'] ?? '100+ Happy Clients'; ?></span>
              </div>
              <div class="badge">
                <i class="fas fa-tools"></i>
                <span><?php echo $cmsContent['trust_badge_3'] ?? 'Premium Quality'; ?></span>
              </div>
            </div>
          </div>
          <div class="hero-image-content">
            <img src="<?php echo $cmsContent['hero_image'] ?? '/RADS-TOOLING/assets/images/cabinet-hero.jpg'; ?>" alt="Custom Cabinets">
          </div>
        </div>
  </div>
  </section>

  <!-- FEATURES SECTION -->
  <section class="features-section">
    <div class="section-header">
      <?php echo $cmsContent['features_title'] ?? '<h2>Why Choose RADS TOOLING?</h2>'; ?>
      <?php echo $cmsContent['features_subtitle'] ?? '<p>Everything you need to create your perfect cabinet</p>'; ?>
    </div>

    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-cube"></i>
        </div>
        <h3>3D Customization</h3>
        <p>Visualize your cabinet in real-time with our 360° preview tool. Choose colors, materials, and dimensions.</p>
        <a href="/RADS-TOOLING/customer/register.php" class="feature-link">Start Customizing →</a>
      </div>

      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-shipping-fast"></i>
        </div>
        <h3>Track Your Orders</h3>
        <p>Monitor every step from production to delivery with real-time status updates.</p>
        <a href="/RADS-TOOLING/customer/cust_login.php" class="feature-link">Login to Track →</a>
      </div>

      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-credit-card"></i>
        </div>
        <h3>Fast Checkout</h3>
        <p>Secure payment via GCash QR code. Pay 30% down payment to start production.</p>
        <a href="/RADS-TOOLING/customer/register.php" class="feature-link">Create Account →</a>
      </div>
    </div>
  </section>

  <!-- CABINETS GALLERY CAROUSEL -->
  <section class="cabinets-section">
    <div class="section-header">
      <h2>Featured <span class="highlight">Cabinets</span></h2>
      <p>Explore our collection of premium custom cabinets</p>
    </div>

    <div class="carousel-container">
      <button class="carousel-btn prev" type="button" aria-label="Previous">
        <span class="material-symbols-rounded">chevron_left</span>
      </button>

      <div class="carousel-track">
        <?php
        $carouselImages = $cmsContent['carousel_images'] ?? [];
        foreach ($carouselImages as $image):
        ?>
          <div class="carousel-item">
            <img src="<?php echo htmlspecialchars($image['image']); ?>"
              alt="<?php echo htmlspecialchars($image['title']); ?>">
            <div class="carousel-caption">
              <h4><?php echo htmlspecialchars($image['title']); ?></h4>
              <p><?php echo htmlspecialchars($image['description']); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <button class="carousel-btn next" type="button" aria-label="Next">
        <span class="material-symbols-rounded">chevron_right</span>
      </button>
    </div>

    <div class="carousel-dots"></div>
  </section>

  <!-- VIDEO SECTION -->
  <section class="video-section">
    <div class="video-content">
      <div class="video-text">
        <h2>
          <?php echo $cmsContent['video_title'] ?? '<h2>Crafted with Passion</h2>'; ?>
          <?php echo $cmsContent['video_subtitle'] ?? '<p>Watch our craftsmen at work</p>'; ?> </h2>
        <ul class="video-features">
          <li><i class="fas fa-check"></i> Premium hardwood materials</li>
          <li><i class="fas fa-check"></i> Expert craftsmanship</li>
          <li><i class="fas fa-check"></i> Quality assurance tested</li>
          <li><i class="fas fa-check"></i> Custom finishing options</li>
        </ul>
      </div>

      <div class="video-wrapper">
        <video controls playsinline poster="/RADS-TOOLING/assets/videos/crafting.mp4">
          <source src="/RADS-TOOLING/assets/videos/crafting.mp4" type="video/mp4" />
          Your browser does not support the video tag.
        </video>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS SECTION -->
  <section class="testimonials-section" id="testimonials">
    <div class="section-header">
      <h2>What Our <span class="highlight">Customers Say</span></h2>
      <p>Real feedback from satisfied clients</p>
    </div>

    <div class="testimonials-grid" id="testimonialsContainer">
      <!-- Testimonials will be loaded here via JavaScript -->
      <div class="loading">
        <i class="fas fa-spinner fa-spin"></i> Loading testimonials...
      </div>
    </div>
  </section>

  <!-- CTA BANNER -->
  <section class="cta-banner">
    <div class="cta-content">
      <?php echo $cmsContent['cta_headline'] ?? '<h2>Ready to Design Your Dream Cabinet?</h2>'; ?>
      <?php echo $cmsContent['cta_text'] ?? '<p>Join hundreds of satisfied customers</p>'; ?>
      <div class="cta-buttons">
        <a href="/RADS-TOOLING/customer/register.php" class="btn-cta-primary btn-large">
          <i class="fas fa-user-plus"></i> Create Free Account
        </a>
        <a href="/RADS-TOOLING/public/products.php" class="btn-cta-secondary btn-large">
          <i class="fas fa-images"></i> View Gallery
        </a>
      </div>
    </div>
  </section>

  <!-- RADS-TOOLING Chat Support Widget (Guest Mode) -->
  <button id="rtChatBtn" class="rt-chat-btn">
    <span class="material-symbols-rounded">chat</span>
    Need Help?
  </button>

  <div id="rtChatPopup" class="rt-chat-popup">
    <div class="rt-chat-header">
      <span>Rads Tooling - Chat Support</span>
    </div>

    <!-- Message: Please Login -->
    <div class="rt-chat-messages" style="padding: 40px 20px; text-align: center;">
      <div style="margin-bottom: 20px;">
        <i class="fas fa-lock" style="font-size: 48px; color: #1f4e74; opacity: 0.6;"></i>
      </div>
      <h3 style="color: #333; margin-bottom: 10px;">Chat Support Unavailable</h3>
      <p style="color: #666; margin-bottom: 20px;">
        Please login or create an account to chat with our support team and get instant answers to your questions.
      </p>
      <a href="/RADS-TOOLING/customer/cust_login.php"
        style="display: inline-block; padding: 12px 24px; background: #1f4e74; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
        <i class="fas fa-sign-in-alt"></i> Login Now
      </a>
    </div>
  </div>

  <script>
    // Simple toggle for public page (no chat functionality until login)
    document.addEventListener('DOMContentLoaded', function() {
      const chatBtn = document.getElementById('rtChatBtn');
      const chatPopup = document.getElementById('rtChatPopup');
      const chatClose = document.getElementById('rtChatClose');

      if (chatBtn && chatPopup && chatClose) {
        chatBtn.addEventListener('click', function() {
          chatPopup.style.display = 'flex';
          chatBtn.style.display = 'none';
        });

        chatClose.addEventListener('click', function() {
          chatPopup.style.display = 'none';
          chatBtn.style.display = 'flex';
        });
      }
    });
  </script>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer-content">
      <!-- About Section -->
      <div class="footer-section">
        <h3><?php echo $cmsContent['footer_company_name'] ?? 'About RADS TOOLING'; ?></h3>
        <p class="footer-description">
          <?php echo $cmsContent['footer_description'] ?? 'Premium custom cabinet manufacturer serving clients since 2007.
            Quality craftsmanship, affordable prices, and exceptional service.'; ?>
        </p>
        <div class="footer-social">
          <a href="#" class="social-icon" aria-label="Facebook">
            <span class="material-symbols-rounded">facebook</span>
          </a>
          <a href="#" class="social-icon" aria-label="Instagram">
            <span class="material-symbols-rounded">photo_camera</span>
          </a>
          <a href="mailto:RadsTooling@gmail.com" class="social-icon" aria-label="Email">
            <span class="material-symbols-rounded">mail</span>
          </a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="footer-section">
        <h3>Quick Links</h3>
        <ul class="footer-links">
          <li><a href="/RADS-TOOLING/public/about.php">About Us</a></li>
          <li><a href="/RADS-TOOLING/public/products.php">Products</a></li>
          <li><a href="/RADS-TOOLING/customer/register.php">Sign Up</a></li>
          <li><a href="/RADS-TOOLING/customer/cust_login.php">Login</a></li>
        </ul>
      </div>

      <!-- Categories -->
      <div class="footer-section">
        <h3>Categories</h3>
        <ul class="footer-links">
          <li><a href="/RADS-TOOLING/public/products.php?type=Kitchen">Kitchen</a></li>
          <li><a href="/RADS-TOOLING/public/products.php?type=Bedroom">Bedroom</a></li>
          <li><a href="/RADS-TOOLING/public/products.php?type=Living Room">Living Room</a></li>
          <li><a href="/RADS-TOOLING/public/products.php?type=Bathroom">Bathroom</a></li>
          <li><a href="/RADS-TOOLING/public/products.php?type=Commercial">Commercial</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="footer-section">
        <h3>Contact Info</h3>
        <div class="contact-info-item">
          <span class="material-symbols-rounded">location_on</span>
          <span><?php echo $cmsContent['footer_address'] ?? 'Green Breeze, Piela, Dasmariñas, Cavite'; ?></span>
        </div>
        <div class="contact-info-item">
          <span class="material-symbols-rounded">mail</span>
          <a href="mailto:<?php echo $cmsContent['footer_email'] ?? 'RadsTooling@gmail.com'; ?>">
            <?php echo $cmsContent['footer_email'] ?? 'RadsTooling@gmail.com'; ?>
          </a>
        </div>
        <div class="contact-info-item">
          <span class="material-symbols-rounded">phone</span>
          <span><?php echo $cmsContent['footer_phone'] ?? '+63 976 228 4270'; ?></span>
        </div>
        <div class="contact-info-item">
          <span class="material-symbols-rounded">schedule</span>
          <span><?php echo $cmsContent['footer_hours'] ?? 'Mon-Sat: 8:00 AM - 5:00 PM'; ?></span>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p class="footer-copyright">
        <?php echo $cmsContent['footer_copyright'] ?? '© 2025 RADS TOOLING INC. All rights reserved.'; ?>
      </p>
      <div class="footer-legal">
        <a href="/RADS-TOOLING/public/privacy.php">Privacy Policy</a>
        <a href="/RADS-TOOLING/public/terms.php">Terms & Conditions</a>
      </div>
    </div>
  </footer>

  </div><!-- /.page-wrapper -->

  <script>
    // ========== CAROUSEL ==========
    (function() {
      const track = document.querySelector('.carousel-track');
      const items = document.querySelectorAll('.carousel-item');
      const nextBtn = document.querySelector('.carousel-btn.next');
      const prevBtn = document.querySelector('.carousel-btn.prev');
      const dotsContainer = document.querySelector('.carousel-dots');

      if (!track || items.length === 0) return;

      let currentIndex = 0;
      const itemWidth = items[0].offsetWidth + 20; // including gap

      // Create dots
      items.forEach((_, index) => {
        const dot = document.createElement('span');
        dot.classList.add('dot');
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToSlide(index));
        dotsContainer.appendChild(dot);
      });

      const dots = document.querySelectorAll('.dot');

      function updateCarousel() {
        track.style.transform = `translateX(-${currentIndex * itemWidth}px)`;
        dots.forEach((dot, index) => {
          dot.classList.toggle('active', index === currentIndex);
        });
      }

      function goToSlide(index) {
        currentIndex = index;
        updateCarousel();
      }

      nextBtn.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % items.length;
        updateCarousel();
      });

      prevBtn.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + items.length) % items.length;
        updateCarousel();
      });

      // Auto-play
      setInterval(() => {
        currentIndex = (currentIndex + 1) % items.length;
        updateCarousel();
      }, 5000);
    })();

    // ========== TESTIMONIALS ==========
    (function() {
      const container = document.getElementById('testimonialsContainer');

      fetch('/RADS-TOOLING/backend/api/testimonials.php')
        .then(res => res.json())
        .then(data => {
          if (data.success && data.testimonials.length > 0) {
            container.innerHTML = data.testimonials.map(t => `
          <div class="testimonial-card">
            <div class="testimonial-header">
              <div class="testimonial-avatar">
                ${t.customer_name.charAt(0).toUpperCase()}
              </div>
              <div class="testimonial-info">
                <h4>${escapeHtml(t.customer_name)}</h4>
                <div class="testimonial-rating">
                  ${'<i class="fas fa-star"></i>'.repeat(t.rating)}
                  ${'<i class="far fa-star"></i>'.repeat(5 - t.rating)}
                </div>
              </div>
            </div>
            <p class="testimonial-comment">"${escapeHtml(t.comment)}"</p>
            <span class="testimonial-date">${formatDate(t.created_at)}</span>
          </div>
        `).join('');
          } else {
            container.innerHTML = '<p class="no-testimonials">No testimonials yet. Be the first to share your experience!</p>';
          }
        })
        .catch(() => {
          container.innerHTML = '<p class="error">Unable to load testimonials.</p>';
        });

      function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }

      function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      }
    })();

    // ========== SMOOTH SCROLL ==========
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
  </script>

  <script src="/RADS-TOOLING/assets/JS/chat_widget.js"></script>

</body>

</html>