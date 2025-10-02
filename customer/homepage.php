<?php
// /RADS-TOOLING/customer/homepage.php - Customer homepage with full access
require_once __DIR__ . '/../backend/config/app.php';
session_start();

// Ensure user is logged in as customer
$user = $_SESSION['user'] ?? null;
$isCustomer = $user && (($user['aud'] ?? '') === 'customer');

if (!$isCustomer) {
    header('Location: /RADS-TOOLING/customer/cust_login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Welcome <?= htmlspecialchars($user['name'] ?? $user['username']) ?> - RADS TOOLING</title>
  <!-- Use same stylesheets as landing page -->
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/Homepage.css" />
  <link rel="stylesheet" href="/RADS-TOOLING/assets/css/about.css" />
  <link rel="stylesheet" href="/RADS-TOOLING/assets/css/helpcenter.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="page-wrapper">

<header class="navbar">
  <div class="navbar-top">
    <div class="logo">
      <span class="big-blue-italic">R</span><span class="black-italic">ADS </span>
      <span class="big-blue-italic">T</span><span class="black-italic">OOLING</span>
    </div>

    <form class="search-bar" action="/RADS-TOOLING/products.php" method="get">
      <input type="text" name="q" placeholder="Search products..." />
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <div class="nav-icons">
      <!-- Customer is always logged in here -->
      <div class="user-dropdown" style="position:relative;">
        <a href="#" class="user-menu-toggle" style="text-decoration:none;">
          <i class="fas fa-user"></i> Welcome, <?= htmlspecialchars($user['name'] ?? $user['username']) ?>
          <i class="fas fa-chevron-down" style="font-size:0.8em;margin-left:5px;"></i>
        </a>
        <div class="user-dropdown-menu" style="display:none;position:absolute;top:100%;right:0;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,0.1);border-radius:5px;min-width:200px;z-index:1000;">
          <a href="/RADS-TOOLING/customer/profile.php" style="display:block;padding:10px 15px;text-decoration:none;color:#333;border-bottom:1px solid #eee;">
            <i class="fas fa-user-circle"></i> My Profile
          </a>
          <a href="/RADS-TOOLING/customer/orders.php" style="display:block;padding:10px 15px;text-decoration:none;color:#333;border-bottom:1px solid #eee;">
            <i class="fas fa-receipt"></i> My Orders
          </a>
          <a href="/RADS-TOOLING/customer/customizations.php" style="display:block;padding:10px 15px;text-decoration:none;color:#333;border-bottom:1px solid #eee;">
            <i class="fas fa-tools"></i> My Customizations
          </a>
          <button onclick="logoutUser()" style="width:100%;padding:10px 15px;background:none;border:none;text-align:left;color:#d32f2f;cursor:pointer;">
            <i class="fas fa-right-from-bracket"></i> Logout
          </button>
        </div>
      </div>
      
      <!-- Cart with item count -->
      <a href="/RADS-TOOLING/cart.php" class="cart-link" style="position:relative;">
        <i class="fas fa-shopping-cart"></i>
        <span id="cartCount" class="cart-badge" style="position:absolute;top:-8px;right:-8px;background:#e74c3c;color:white;border-radius:50%;padding:2px 6px;font-size:0.7em;min-width:18px;text-align:center;">0</span>
      </a>
    </div>
  </div>

  <div class="navbar-bottom">
    <nav>
      <a href="/RADS-TOOLING/customer/homepage.php" class="active">Home</a>
      <a href="/RADS-TOOLING/public/about.php">About Us</a>
      <a href="/RADS-TOOLING/products.php">Products</a>
      <a href="/RADS-TOOLING/customer/custom-builder.php">Custom Builder</a>
      <a href="/RADS-TOOLING/customer/orders.php">My Orders</a>
    </nav>
  </div>
</header>

<main>
  <!-- Welcome Section for Customers -->
  <section class="hero-section">
    <div class="hero-text">
      <h2><span class="big-blue-italic">W</span>elcome back, <?= htmlspecialchars($user['name'] ?? $user['username']) ?>!</h2>
      <p>Continue customizing your cabinets, browse our latest products, or check your order status.</p>
      <div style="margin-top:12px">
        <a class="login-btn" href="/RADS-TOOLING/customize.php">Start Customizing</a>
        <a class="login-btn" style="background:#6e7ca1" href="/RADS-TOOLING/products.php">Browse Products</a>
        <a class="login-btn" style="background:#27ae60" href="/RADS-TOOLING/cart.php">View Cart (<span id="cartCountText">0</span>)</a>
      </div>
    </div>
    <div class="hero-image">
      <img src="/assets/images/cabinet-hero.jpg" alt="Cabinet Preview" />
    </div>
  </section>

  <!-- Quick Actions for Customers -->
  <section style="padding:2rem 5%;background:#f8f9fa;">
    <h3 style="text-align:center;margin-bottom:2rem;">Quick Actions</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem;max-width:1000px;margin:0 auto;">
      
      <div style="background:#fff;padding:1.5rem;border-radius:10px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <i class="fas fa-plus-circle" style="font-size:2rem;color:#3498db;margin-bottom:1rem;"></i>
        <h4>Create Custom Order</h4>
        <p>Design your perfect cabinet with our customization tools.</p>
        <a href="/RADS-TOOLING/customize.php" class="login-btn" style="font-size:0.9rem;padding:8px 16px;">Start Building</a>
      </div>

      <div style="background:#fff;padding:1.5rem;border-radius:10px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <i class="fas fa-receipt" style="font-size:2rem;color:#e67e22;margin-bottom:1rem;"></i>
        <h4>Track Orders</h4>
        <p>View your order history and track current deliveries.</p>
        <a href="/RADS-TOOLING/customer/orders.php" class="login-btn" style="font-size:0.9rem;padding:8px 16px;background:#e67e22;">View Orders</a>
      </div>

      <div style="background:#fff;padding:1.5rem;border-radius:10px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <i class="fas fa-shopping-cart" style="font-size:2rem;color:#27ae60;margin-bottom:1rem;"></i>
        <h4>Complete Purchase</h4>
        <p>Review and checkout items in your cart.</p>
        <a href="/RADS-TOOLING/cart.php" class="login-btn" style="font-size:0.9rem;padding:8px 16px;background:#27ae60;">Go to Cart</a>
      </div>

    </div>
  </section>

  <!-- Same Cabinets Carousel as landing page -->
  <section class="cabinets-section">
    <h3>Featured Cabinets</h3>
    <div class="carousel">
      <button class="carousel-btn prev" type="button">&#8249;</button>
      <div class="carousel-track">
        <div class="cabinet-item" data-product-id="1">
          <img src="/assets/images/cabs/cab1.jpg" alt="Cabinet 1" onerror="this.src='https://via.placeholder.com/300x350?text=Cabinet+1'">
          <div class="cabinet-overlay">
            <button onclick="addToCart(1)" class="add-to-cart-btn">Add to Cart</button>
            <button onclick="customizeProduct(1)" class="customize-btn">Customize</button>
          </div>
        </div>
        <div class="cabinet-item" data-product-id="2">
          <img src="/assets/images/cabs/cab2.jpg" alt="Cabinet 2" onerror="this.src='https://via.placeholder.com/300x350?text=Cabinet+2'">
          <div class="cabinet-overlay">
            <button onclick="addToCart(2)" class="add-to-cart-btn">Add to Cart</button>
            <button onclick="customizeProduct(2)" class="customize-btn">Customize</button>
          </div>
        </div>
        <!-- Add more cabinet items -->
      </div>
      <button class="carousel-btn next" type="button">&#8250;</button>
    </div>
  </section>

  <!-- Same Video Section -->
  <section class="video-section">
    <div class="video-flex">
      <div class="video-caption">
        <h2><span class="big-blue-italic">C</span>rafted with passion and precision</h2>
        <p>Every cabinet is made with attention to detail and quality craftsmanship.</p>
      </div>
      <div class="video-wrapper">
        <video autoplay muted loop playsinline controls>
          <source src="/assets/videos/crafting.mp4" type="video/mp4" />
          Your browser does not support the video tag.
        </video>
      </div>
    </div>
  </section>

  <!-- Customer Support Chat -->
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

<!-- Same Footer -->
<footer class="custom-footer">
  <div class="footer-columns">
    <div>
      <p class="footer-heading">Customer Support</p>
      <a href="/RADS-TOOLING/help.php">Help Center</a>
      <a href="/RADS-TOOLING/faqs.php">FAQs</a>
      <a href="/RADS-TOOLING/customer/feedback.php">Give Feedback</a>
    </div>
    <div>
      <p class="footer-heading">Categories</p>
      <a href="/RADS-TOOLING/products.php?cat=living">Living Room</a>
      <a href="/RADS-TOOLING/products.php?cat=bedroom">Bedroom</a>
      <a href="/RADS-TOOLING/products.php?cat=kitchen">Kitchen</a>
      <a href="/RADS-TOOLING/products.php?cat=bathroom">Bathroom</a>
      <a href="/RADS-TOOLING/products.php?cat=commercial">Commercial Cabinet</a>
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
  </div>
</footer>

</div><!-- /.page-wrapper -->

<style>
/* Additional styles for customer homepage */
.cabinet-item {
  position: relative;
  display: inline-block;
  margin-right: 20px;
}

.cabinet-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  opacity: 0;
  transition: opacity 0.3s;
  gap: 10px;
}

.cabinet-item:hover .cabinet-overlay {
  opacity: 1;
}

.add-to-cart-btn, .customize-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 5px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
}

.add-to-cart-btn {
  background: #27ae60;
  color: white;
}

.customize-btn {
  background: #3498db;
  color: white;
}

.add-to-cart-btn:hover, .customize-btn:hover {
  transform: translateY(-2px);
}

.cart-badge {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.user-dropdown:hover .user-dropdown-menu {
  display: block !important;
}
</style>

<script>
// Cart functionality
let cartItems = JSON.parse(localStorage.getItem('cart') || '[]');
updateCartDisplay();

function updateCartDisplay() {
  document.getElementById('cartCount').textContent = cartItems.length;
  document.getElementById('cartCountText').textContent = cartItems.length;
}

async function addToCart(productId) {
  try {
    // You'll need to create this API endpoint
    const response = await fetch('/RADS-TOOLING/backend/api/cart.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      credentials: 'same-origin',
      body: JSON.stringify({
        action: 'add',
        product_id: productId,
        quantity: 1
      })
    });
    
    const result = await response.json();
    if (result.success) {
      cartItems.push({id: productId, quantity: 1});
      localStorage.setItem('cart', JSON.stringify(cartItems));
      updateCartDisplay();
      alert('Added to cart successfully!');
    } else {
      alert('Failed to add to cart: ' + result.message);
    }
  } catch (error) {
    console.error('Cart error:', error);
    // Fallback to localStorage only
    cartItems.push({id: productId, quantity: 1});
    localStorage.setItem('cart', JSON.stringify(cartItems));
    updateCartDisplay();
    alert('Added to cart!');
  }
}

function customizeProduct(productId) {
  window.location.href = `/RADS-TOOLING/customize.php?product=${productId}`;
}

async function logoutUser() {
  try {
    const response = await fetch('/RADS-TOOLING/backend/api/auth.php?action=logout', {
      method: 'POST',
      credentials: 'same-origin'
    });
    const result = await response.json();
    
    if (result.success) {
      localStorage.removeItem('cart'); // Clear cart on logout
      window.location.href = '/RADS-TOOLING/public/index.php';
    }
  } catch (error) {
    console.error('Logout error:', error);
    window.location.href = '/RADS-TOOLING/public/index.php';
  }
}

// Same carousel and chat functionality as landing page
(function(){
  const track = document.querySelector('.carousel-track');
  const nextBtn = document.querySelector('.carousel-btn.next');
  const prevBtn = document.querySelector('.carousel-btn.prev');
  const scrollAmount = 320;
  nextBtn?.addEventListener('click', () => track?.scrollBy({ left: scrollAmount, behavior: 'smooth' }));
  prevBtn?.addEventListener('click', () => track?.scrollBy({ left: -scrollAmount, behavior: 'smooth' }));
})();

(function(){
  const helpBtn = document.getElementById("helpBtn");
  const chatPopup = document.getElementById("chatPopup");
  const chatClose = document.getElementById("chatClose");
  helpBtn?.addEventListener('click', () => { chatPopup.style.display = "flex"; helpBtn.style.display = "none"; });
  chatClose?.addEventListener('click', () => { chatPopup.style.display = "none"; helpBtn.style.display = "block"; });
})();
</script>
</body>
</html>