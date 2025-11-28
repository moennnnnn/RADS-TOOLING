<?php
// /public/privacy.php - Public Privacy Page
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
    $content = $GLOBALS['cms_preview_content'];
} else {
    $content = getCMSContent('privacy'); // Gets published by default
}

$user = $_SESSION['user'] ?? null;
$isCustomer = $user && (($user['aud'] ?? '') === 'customer');

// If customer is logged in, redirect to customer view
if ($isCustomer) {
    header('Location: /customer/privacy.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RADS TOOLING - Custom Cabinet Solutions</title>
    <link rel="stylesheet" href="/assets/CSS/Homepage.css" />
    <link rel="stylesheet" href="/assets/CSS/about.css">
    <link rel="stylesheet" href="/assets/CSS/chat-widget.css">
    <link rel="stylesheet" href="/assets/CSS/responsive.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
</head>

<body>
    <div class="page-wrapper">

        <!-- HEADER -->
        <header class="navbar">
            <div class="navbar-container">
                <?php
                require_once __DIR__ . '/../backend/components/navbar.php';
                renderNavbar();
                ?>
                <form class="search-container" action="/public/products.php" method="get">
                    <input type="text" name="q" class="search-input" placeholder="Search cabinets..." />
                    <button type="submit" class="search-btn" aria-label="Search">
                        <span class="material-symbols-rounded">search</span>
                    </button>
                </form>

                <div class="navbar-actions">
                    <a href="/customer/cust_login.php" class="nav-link">
                        <span class="material-symbols-rounded">login</span>
                        <span>Login</span>
                    </a>
                    <a href="/customer/register.php" class="nav-link">
                        <span class="material-symbols-rounded">person_add</span>
                        <span>Sign Up</span>
                    </a>
                    <a href="/admin/login.php" class="nav-link-icon" title="Staff Login">
                        <span class="material-symbols-rounded">admin_panel_settings</span>
                    </a>
                </div>
            </div>

            <nav class="navbar-menu">
                <a href="/public/index.php" class="nav-menu-item">Home</a>
                <a href="/public/about.php" class="nav-menu-item">About Us</a>
                <a href="/public/products.php" class="nav-menu-item">Products</a>
                <a href="/public/testimonials.php" class="nav-menu-item">Testimonials</a>
            </nav>
        </header>


        <!-- Main Content -->
        <main class="rt-policy-main-content">
            <div class="rt-container">
                <div class="rt-about-narrative" style="margin-top: 3rem;">
                    <div class="rt-policy-content">
                        <?php echo $content['content'] ?? '<h1>Privacy Policy</h1><p>Loading content...</p>'; ?>
                    </div>
                </div>
            </div>
        </main>

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
                    Please login to chat with our support team and get instant answers to your questions.
                </p>
                <a href="/customer/cust_login.php"
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
        <?php
        require_once __DIR__ . '/../backend/components/footer.php';
        renderFooter();
        ?>

        <!-- Mobile Menu Script -->
        <script>
            document.querySelector('.rt-nav-toggle').addEventListener('click', function() {
                document.querySelector('.rt-nav-menu').classList.toggle('active');
            });
        </script>
        <script>
            // ========== CHAT BUTTON ==========
            (function() {
                const chatBtn = document.getElementById('chatBtn');
                const chatPopup = document.getElementById('chatPopup');
                const chatClose = document.getElementById('chatClose');

                chatBtn?.addEventListener('click', () => {
                    chatPopup.style.display = 'flex';
                    chatBtn.style.display = 'none';
                });

                chatClose?.addEventListener('click', () => {
                    chatPopup.style.display = 'none';
                    chatBtn.style.display = 'flex';
                });
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
        <!-- Add before closing </body> tag -->
        <script src="/assets/JS/chat_widget.js"></script>
</body>

</html>