<?php

declare(strict_types=1);

// ✅ Session (safe)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ✅ Need a pid (accept ?pid= or ?id=)
$pid = (int)($_GET['pid'] ?? ($_GET['id'] ?? 0));
if ($pid <= 0) {
    header('Location: /RADS-TOOLING/backend/customer/products.php');
    exit;
}

// ✅ Correct app + helpers (wag yung ../config/app.php)
require_once __DIR__ . '/../backend/config/app.php';
require_once __DIR__ . '/../backend/lib/cms_helper.php';

// ✅ Preview vs Auth
$isPreview = isset($GLOBALS['cms_preview_content']) && !empty($GLOBALS['cms_preview_content']);

if (!$isPreview) {
    require_once __DIR__ . '/../includes/guard.php';
    guard_require_customer();

    $user = $_SESSION['user'] ?? null;
    $isCustomer = $user && (($user['aud'] ?? '') === 'customer');
    if (!$isCustomer) {
        header('Location: /RADS-TOOLING/customer/cust_login.php');
        exit;
    }
} else {
    $user = $_SESSION['user'] ?? null; // fallback for name/avatar
}

// (Optional) CMS content, not used here pero ok lang
$content = $isPreview ? $GLOBALS['cms_preview_content'] : getCMSContent('about');

// ✅ Safe vars for UI
$customerName = htmlspecialchars($user['name'] ?? $user['username'] ?? 'Customer', ENT_QUOTES, 'UTF-8');
$customerId   = (int)($user['id'] ?? 0);
$img          = $_SESSION['user']['profile_image'] ?? '';
$avatarHtml   = $img
    ? '<img src="/RADS-TOOLING/' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') . '?v=' . time() . '" alt="Avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">'
    : strtoupper(substr($customerName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rads Tooling - <?= $customerName ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/Homepage.css" />
    <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/chat-widget.css">
    <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/customize.css">
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

                <form class="search-container" action="/RADS-TOOLING/public/products.php" method="get">
                    <input type="text" name="q" class="search-input" placeholder="Search cabinets..." />
                    <button type="submit" class="search-btn" aria-label="Search">
                        <span class="material-symbols-rounded">search</span>
                    </button>
                </form>

                <div class="navbar-actions">
                    <!-- Profile Dropdown -->
                    <div class="profile-menu">
                        <button class="profile-toggle" id="profileToggle" type="button">
                            <div class="profile-avatar-wrapper">
                                <div class="profile-avatar" id="nav-avatar"><?= $avatarHtml ?></div>
                            </div>
                            <div class="profile-info">
                                <span class="profile-name"><?= $customerName ?></span>
                                <span class="material-symbols-rounded dropdown-icon">expand_more</span>
                            </div>
                        </button>

                        <div class="profile-dropdown" id="profileDropdown">
                            <div class="profile-dropdown-header">
                                <div class="dropdown-avatar" id="dd-avatar"><?= $avatarHtml ?></div>
                                <div class="dropdown-user-info">
                                    <div class="dropdown-name" id="dd-name"><?= $customerName ?></div>
                                    <div class="dropdown-email" id="userEmailDisplay">Loading...</div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="/RADS-TOOLING/customer/profile.php" class="dropdown-item"><span class="material-symbols-rounded">person</span><span>My Profile</span></a>
                            <a href="/RADS-TOOLING/customer/orders.php" class="dropdown-item"><span class="material-symbols-rounded">receipt_long</span><span>My Orders</span></a>
                            <a href="/RADS-TOOLING/customer/customizations.php" class="dropdown-item"><span class="material-symbols-rounded">palette</span><span>My Designs</span></a>
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

        <!-- === CUSTOMIZER UI START === -->
        <div class="cz-topbar">
            <a href="/RADS-TOOLING/customer/products.php" class="cz-back">←</a>
            <div class="cz-breadcrumb">CUSTOMIZING</div>
        </div>

        <div class="cz-shell">
            <header class="cz-header">
                <h2>Customize your Cabinet</h2>
                <div class="cz-product-meta">
                    <div id="prodTitle">—</div>
                    <div id="priceBox" class="cz-price">₱ 0.00</div>
                </div>
            </header>

            <div class="cz-main">
                <!-- LEFT -->
                <section class="cz-left">
                    <div class="cz-stage">
                        <button id="stepPrev" class="cz-step-btn" aria-label="Previous step">‹</button>
                        <div id="mediaBox" class="cz-media"><!-- model-viewer injected --></div>
                        <button id="stepNext" class="cz-step-btn" aria-label="Next step">›</button>
                    </div>

                    <div class="cz-sizebar">
                        <span class="cz-size-label">SIZE:</span>
                        <div class="cz-size-legend">Use the sliders to adjust (cm). You can change later.</div>
                    </div>

                    <div id="breakdown" class="cz-breakdown"></div>
                </section>

                <!-- RIGHT -->
                <aside class="cz-right">
                    <div class="cz-step-title">
                        <span id="stepLabel">Step 1 · Size</span>
                    </div>

                    <section id="sec-size" class="cz-sec" hidden>
                        <h3>Size</h3>
                        <div class="cz-grid">
                            <label>Width (cm) <input type="range" id="w" min="0" max="0" value="0"></label>
                            <label>Height (cm) <input type="range" id="h" min="0" max="0" value="0"></label>
                            <label>Depth (cm) <input type="range" id="d" min="0" max="0" value="0"></label>
                            <label>Doors <input type="range" id="doors" min="0" max="10" value="0"></label>
                            <label>Layers <input type="range" id="layers" min="0" max="10" value="0"></label>
                        </div>
                    </section>

                    <section id="sec-textures" class="cz-sec" hidden>
                        <h3>Textures</h3>
                        <div class="cz-partbar">
                            <div class="part-tabs">
                                <button data-part="door" class="active">Door</button>
                                <button data-part="body">Body</button>
                                <button data-part="inside">Inside</button>
                            </div>
                        </div>
                        <div id="textures" class="cz-list"></div>
                    </section>

                    <section id="sec-colors" class="cz-sec" hidden>
                        <h3>Colors</h3>
                        <div class="cz-partbar">
                            <div class="part-tabs">
                                <button data-part="door" class="active">Door</button>
                                <button data-part="body">Body</button>
                                <button data-part="inside">Inside</button>
                            </div>
                        </div>
                        <div id="colors" class="cz-list"></div>
                    </section>

                    <section id="sec-handles" class="cz-sec" hidden>
                        <h3>Handles</h3>
                        <div id="handles" class="cz-list"></div>
                    </section>

                    <div class="cz-actions">
                        <button id="toCart" class="btn main">ADD TO CART</button>
                        <a class="btn ghost" href="/RADS-TOOLING/backend/customer/buynow.php?pid=<?= (int)$pid ?>">BUY NOW</a>
                    </div>
                </aside>
            </div>
        </div>
        <!-- === CUSTOMIZER UI END === -->

    </div><!-- /.page-wrapper -->

    <!-- Scripts -->
    <script>
        window.PID = <?= (int)$pid ?>;
    </script>
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <script src="/RADS-TOOLING/assets/JS/customize.js" defer></script>
</body>

</html>