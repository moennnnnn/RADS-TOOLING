<?php

/**
 * Reusable Navbar Component with Logo
 *
 * This component renders the navbar with logo from global CMS settings
 * Supports both text and image logos
 *
 * Usage:
 *   include __DIR__ . '/../backend/components/navbar.php';
 *   renderNavbar($currentPage); // Optional parameter for highlighting active page
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

    // Determine base URL based on current context
    $baseUrl = '/RADS-TOOLING';

    // Determine logo link based on user context
    $isCustomerArea = strpos($_SERVER['PHP_SELF'], '/customer/') !== false;
    $logoLink = $isCustomerArea ? "$baseUrl/customer/homepage.php" : "$baseUrl/public/index.php";

?>
    <div class="navbar-brand">
        <a href="<?php echo htmlspecialchars($logoLink); ?>" class="logo-link">
            <?php if ($logoType === 'image' && !empty($logoImage)): ?>
                <img src="<?php echo htmlspecialchars($logoImage); ?>"
                    alt="<?php echo htmlspecialchars($logoText); ?>"
                    class="logo-image"
                    style="max-height: 50px; height: auto; width: auto;">
            <?php else: ?>
                <span class="logo-text"><?php echo substr(htmlspecialchars($logoText), 0, 1); ?></span><?php echo substr(htmlspecialchars($logoText), 1); ?>
            <?php endif; ?>
        </a>
    </div>
<?php
}
?>