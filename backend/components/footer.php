<?php

/**
 * Reusable Footer Component
 *
 * This component renders the footer with content from global CMS settings
 * Automatically fetches footer_settings from the database
 *
 * Usage:
 *   include __DIR__ . '/../backend/components/footer.php';
 *   renderFooter(); // No parameters needed - fetches global settings
 */

if (!function_exists('getFooterSettings')) {
    require_once __DIR__ . '/../lib/cms_helper.php';
}

function renderFooter()
{
    $footer = getFooterSettings();

    $companyName = $footer['footer_company_name'] ?? 'About RADS TOOLING';
    $description = $footer['footer_description'] ?? 'Premium custom cabinet manufacturer serving clients since 2007.';
    $email = $footer['footer_email'] ?? 'RadsTooling@gmail.com';
    $phone = $footer['footer_phone'] ?? '+63 976 228 4270';
    $address = $footer['footer_address'] ?? 'Green Breeze, Piela, Dasmariñas, Cavite';
    $hours = $footer['footer_hours'] ?? 'Mon-Sat: 8:00 AM - 5:00 PM';
    $facebook = $footer['footer_facebook'] ?? '';
    $copyright = $footer['footer_copyright'] ?? '© 2025 RADS TOOLING INC. All rights reserved.';

    $baseUrl = '/RADS-TOOLING';
?>
    <footer class="footer">
        <div class="footer-content">
            <!-- About Section -->
            <div class="footer-section">
                <h3><?php echo htmlspecialchars($companyName); ?></h3>
                <p class="footer-description">
                    <?php echo htmlspecialchars($description); ?>
                </p>
                <div class="footer-social">
                    <?php if (!empty($facebook)): ?>
                        <a href="<?php echo htmlspecialchars($facebook); ?>" target="_blank" class="social-icon" aria-label="Facebook">
                            <span class="material-symbols-rounded">facebook</span>
                        </a>
                    <?php endif; ?>
                    <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="social-icon" aria-label="Email">
                        <span class="material-symbols-rounded">mail</span>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo $baseUrl; ?>/customer/homepage.php">Home</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/customer/about.php">About Us</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/customer/products.php">Products</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/customer/testimonials.php">Testimonials</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="footer-section">
                <h3>Categories</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo $baseUrl; ?>/customer/products.php?type=Kitchen Cabinet">Kitchen Cabinet</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/customer/products.php?type=Wardrobe">Wardrobe</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/customer/products.php?type=Office Cabinet">Office Cabinet</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/customer/products.php?type=Bathroom">Bathroom</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/customer/products.php?type=Storage Cabinet">Storage Cabinet</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-section">
                <h3>Contact Info</h3>
                <div class="contact-info-item">
                    <span class="material-symbols-rounded">location_on</span>
                    <span><?php echo htmlspecialchars($address); ?></span>
                </div>
                <div class="contact-info-item">
                    <span class="material-symbols-rounded">mail</span>
                    <a href="mailto:<?php echo htmlspecialchars($email); ?>">
                        <?php echo htmlspecialchars($email); ?>
                    </a>
                </div>
                <div class="contact-info-item">
                    <span class="material-symbols-rounded">phone</span>
                    <span><?php echo htmlspecialchars($phone); ?></span>
                </div>
                <div class="contact-info-item">
                    <span class="material-symbols-rounded">schedule</span>
                    <span><?php echo htmlspecialchars($hours); ?></span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="footer-copyright">
                <?php echo htmlspecialchars($copyright); ?>
            </p>
            <div class="footer-legal">
                <a href="<?php echo $baseUrl; ?>/customer/privacy.php">Privacy Policy</a>
                <a href="<?php echo $baseUrl; ?>/customer/terms.php">Terms & Conditions</a>
            </div>
        </div>
    </footer>
<?php
}
?>