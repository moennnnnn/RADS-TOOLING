<?php
// /RADS-TOOLING/customer/checkout_delivery.php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/guard.php';
guard_require_customer();

$pid = isset($_GET['pid']) ? trim((string)$_GET['pid']) : '';
$user = $_SESSION['user'] ?? null;
$customerName = htmlspecialchars($user['name'] ?? $user['username'] ?? 'Customer');
$customerId   = $user['id'] ?? 0;

$img = $_SESSION['user']['profile_image'] ?? '';
$avatarHtml = $img
  ? '<img src="/RADS-TOOLING/' . htmlspecialchars($img) . '?v=' . time() . '" alt="Avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">'
  : strtoupper(substr($customerName, 0, 1));

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Delivery Details</title><!-- (or Pick-up Details) -->
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
  <!-- Site styles used by navbar/footer -->
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/Homepage.css">
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/chat-widget.css">
  <!-- Page-specific -->
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/checkout.css">
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/checkout_modal.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>


<body>
  <div class="page-wrapper">
    <!-- HEADER -->
    <header class="navbar">
      <div class="navbar-container">
        <div class="navbar-brand">
          <a href="/RADS-TOOLING/customer/homepage.php" class="logo-link">
            <span class="logo-text">R</span>ADS <span class="logo-text">T</span>OOLING
          </a>
        </div>

        <form class="search-container" action="/RADS-TOOLING/customer/products.php" method="get">
          <input type="text" name="q" class="search-input" placeholder="Search cabinets...">
          <button type="submit" class="search-btn" aria-label="Search">
            <span class="material-symbols-rounded">search</span>
          </button>
        </form>

        <div class="navbar-actions">
          <!-- Profile Dropdown -->
          <div class="profile-menu">
            <button class="profile-toggle" id="profileToggle" type="button">
              <div class="profile-avatar-wrapper">
                <div class="profile-avatar" id="nav-avatar">
                  <?= strtoupper(substr($customerName, 0, 1)) ?>
                </div>
              </div>
              <div class="profile-info">
                <span class="profile-name"><?= $customerName ?></span>
                <span class="material-symbols-rounded dropdown-icon">expand_more</span>
              </div>
            </button>

            <div class="profile-dropdown" id="profileDropdown">
              <div class="profile-dropdown-header">
                <div class="dropdown-avatar" id="dd-avatar">
                  <?= strtoupper(substr($customerName, 0, 1)) ?>
                </div>
                <div class="dropdown-user-info">
                  <div class="dropdown-name" id="dd-name"><?= $customerName ?></div>
                  <div class="dropdown-email" id="userEmailDisplay">Loading...</div>
                </div>
              </div>
              <div class="dropdown-divider"></div>
              <a href="/RADS-TOOLING/customer/profile.php" class="dropdown-item">
                <span class="material-symbols-rounded">person</span><span>My Profile</span>
              </a>
              <a href="/RADS-TOOLING/customer/orders.php" class="dropdown-item">
                <span class="material-symbols-rounded">receipt_long</span><span>My Orders</span>
              </a>
              <div class="dropdown-divider"></div>
              <button onclick="showLogoutModal()" class="dropdown-item dropdown-logout" type="button">
                <span class="material-symbols-rounded">logout</span><span>Logout</span>
              </button>
            </div>
          </div>

          <!-- Cart -->
          <a href="/RADS-TOOLING/cart.php" class="cart-button">
            <span class="material-symbols-rounded">shopping_cart</span>
            <span id="cartCount" class="cart-badge">0</span>
          </a>
        </div>
      </div>

      <nav class="navbar-menu">
        <a href="/RADS-TOOLING/customer/homepage.php" class="nav-menu-item">Home</a>
        <a href="/RADS-TOOLING/customer/about.php" class="nav-menu-item">About</a>
        <a href="/RADS-TOOLING/customer/products.php" class="nav-menu-item">Products</a>
      </nav>
    </header>

    <!--  👉 KEEP your existing checkout container (cz-shell + form) right after the header  -->

  <div class="cz-shell">
    <div class="cz-header">
      <a class="cz-back" href="/RADS-TOOLING/customer/products.php">← Back</a>
      <h2>Delivery Details</h2>
    </div>

    <form id="deliveryForm" class="card" method="post"
      action="/RADS-TOOLING/customer/checkout_delivery_review.php">
      <input type="hidden" name="mode" value="delivery">
      <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid, ENT_QUOTES) ?>">

      <div class="form-two">
        <div class="rt-field">
          <label>First Name</label>
          <input class="rt-input" type="text" name="first_name" required>
        </div>
        <div class="rt-field">
          <label>Last Name</label>
          <input class="rt-input" type="text" name="last_name" required>
        </div>
      </div>

      <div class="rt-field">
        <label>Mobile Number</label>
        <div class="phone-row">
          <span class="prefix">+63</span>
          <input id="phoneLocal" class="rt-input" type="text" inputmode="numeric" maxlength="10"
            pattern="\d{10}" placeholder="9123456789" required>
          <input id="phoneFull" type="hidden" name="phone">
        </div>
        <div class="hint">Enter 10 digits only.</div>
      </div>

      <div class="form-two">
        <div class="rt-field">
          <label>Province</label>
          <select id="province" class="rt-input" required></select>
          <input id="provinceInput" class="rt-input" type="text" placeholder="Type province" hidden>
          <input type="hidden" id="provinceVal" name="province">
        </div>
        <div class="rt-field">
          <label>City/Municipality</label>
          <select id="city" class="rt-input" required disabled></select>
          <input id="cityInput" class="rt-input" type="text" placeholder="Type city/municipality" hidden>
          <input type="hidden" id="cityVal" name="city">
        </div>
      </div>

      <div class="rt-field">
        <label>Barangay</label>
        <select id="barangaySelect" class="rt-input" required disabled></select>
        <input id="barangayInput" class="rt-input" type="text" placeholder="Type barangay" hidden>
        <input type="hidden" id="barangayVal" name="barangay">
      </div>


      <div class="form-two">
        <div class="rt-field">
          <label>Street / Block / Lot</label>
          <input class="rt-input" type="text" name="street" required>
        </div>
        <div class="rt-field">
          <label>Postal Code</label>
          <input class="rt-input" type="text" name="postal" required>
        </div>
      </div>

      <div class="form-actions">
        <button type="button" class="rt-btn rt-btn-outline" id="btnClear">Clear</button>
        <button id="btnContinue" type="button" class="rt-btn rt-btn-dark">Continue</button>
      </div>
    </form>

    <!-- Incomplete form modal -->
    <div id="invalidModal" class="rt-modal" hidden>
      <div class="rt-modal__dialog">
        <h3>Incomplete form</h3>
        <p>Please fill the highlighted fields.</p>
        <div class="right">
          <button class="rt-btn rt-btn-dark" data-close="#invalidModal">OK</button>
        </div>
      </div>
    </div>
  </div>
    <!-- FOOTER -->
    <footer class="footer">
      <div class="footer-content">
        <div class="footer-section">
          <h3>About RADS TOOLING</h3>
          <p class="footer-description">
            Premium custom cabinet manufacturer serving clients since 2007.
            Quality craftsmanship, affordable prices, and exceptional service.
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

        <div class="footer-section">
          <h3>Quick Links</h3>
          <ul class="footer-links">
            <li><a href="/RADS-TOOLING/customer/homepage.php">Home</a></li>
            <li><a href="/RADS-TOOLING/customer/about.php">About Us</a></li>
            <li><a href="/RADS-TOOLING/customer/products.php">Products</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h3>Categories</h3>
          <ul class="footer-links">
            <li><a href="/RADS-TOOLING/customer/products.php?type=Kitchen Cabinet">Kitchen Cabinet</a></li>
            <li><a href="/RADS-TOOLING/customer/products.php?type=Wardrobe">Wardrobe</a></li>
            <li><a href="/RADS-TOOLING/customer/products.php?type=Office Cabinet">Office Cabinet</a></li>
            <li><a href="/RADS-TOOLING/customer/products.php?type=Bathroom Cabinet">Bathroom Cabinet</a></li>
            <li><a href="/RADS-TOOLING/customer/products.php?type=Commercial">Storage Cabinet</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h3>Contact Info</h3>
          <div class="contact-info-item">
            <span class="material-symbols-rounded">location_on</span>
            <span>Green Breeze, Piela, Dasmariñas, Cavite</span>
          </div>
          <div class="contact-info-item">
            <span class="material-symbols-rounded">mail</span>
            <a href="mailto:RadsTooling@gmail.com">RadsTooling@gmail.com</a>
          </div>
          <div class="contact-info-item">
            <span class="material-symbols-rounded">schedule</span>
            <span>Mon-Sat: 8:00 AM - 5:00 PM</span>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p class="footer-copyright">© 2025 RADS TOOLING INC. All rights reserved.</p>
        <div class="footer-legal">
          <a href="/RADS-TOOLING/customer/privacy.php">Privacy Policy</a>
          <a href="/RADS-TOOLING/customer/terms.php">Terms & Conditions</a>
        </div>
      </div>
    </footer>
  </div><!-- /.page-wrapper -->

  <script>
  // Simple init for profile dropdown + cart badge
  document.addEventListener('DOMContentLoaded', function () {
    const profileToggle = document.getElementById('profileToggle');
    const profileDropdown = document.getElementById('profileDropdown');
    profileToggle?.addEventListener('click', function (e) {
      e.preventDefault(); e.stopPropagation();
      profileDropdown?.classList.toggle('show');
    });
    document.addEventListener('click', function (e) {
      if (!profileToggle?.contains(e.target) && !profileDropdown?.contains(e.target)) {
        profileDropdown?.classList.remove('show');
      }
    });
    const cartCount = document.getElementById('cartCount');
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    if (cartCount) cartCount.textContent = cart.length || 0;
  });

  function showLogoutModal(){ /* optional: hook up your modal if present */ }
</script>

<script src="/RADS-TOOLING/assets/JS/nav_user.js"></script>
<script src="/RADS-TOOLING/assets/JS/chat_widget.js"></script>
<script src="/RADS-TOOLING/assets/JS/checkout.js" defer></script>
</body>
</html>
