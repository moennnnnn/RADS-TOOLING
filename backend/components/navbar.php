<?php

/**
 * Reusable Navbar/Logo Component
 * Includes Modular CSS automatically.
 */

if (!function_exists('getLogoSettings')) {
    require_once __DIR__ . '/../lib/cms_helper.php';
}

function renderNavbar($activePage = '')
{
    $logoSettings = getLogoSettings();

    $logoType = $logoSettings['logo_type'] ?? 'text';
    $logoText = $logoSettings['logo_text'] ?? 'RADS TOOLING';
    $logoImage = $logoSettings['logo_image'] ?? '';

    // Determine Link Destination
    $baseUrl = '';
    $isCustomerArea = strpos($_SERVER['PHP_SELF'], '/customer/') !== false;
    // Kung admin, sa admin dashboard; kung customer, sa homepage; else public home
    if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
        $logoLink = "$baseUrl/admin/index.php";
    } elseif ($isCustomerArea) {
        $logoLink = "$baseUrl/customer/homepage.php";
    } else {
        $logoLink = "$baseUrl/public/index.php";
    }

    // AUTO-INJECT CSS: Dito natin ililink ang navbar.css
    echo '<link rel="stylesheet" href="/assets/CSS/navbar.css?v=' . time() . '">';
?>
    <div class="navbar-brand">
        <a href="<?php echo htmlspecialchars($logoLink); ?>" class="logo-link">
            <?php if ($logoType === 'image' && !empty($logoImage)): ?>
                <img src="<?php echo htmlspecialchars($logoImage); ?>"
                    alt="<?php echo htmlspecialchars($logoText); ?>"
                    class="logo-image">
            <?php else: ?>
                <span class="logo-text">
                    <span style="color: inherit;"><?php echo substr(htmlspecialchars($logoText), 0, 1); ?></span><?php echo substr(htmlspecialchars($logoText), 1); ?>
                </span>
            <?php endif; ?>
        </a>
    </div>
<?php
}
?>