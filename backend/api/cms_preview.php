<?php
require_once __DIR__ . '/../config/app.php';

// Manual session start (bypass guard.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify staff access
if (!isset($_SESSION['staff']) && !isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    die('<h1>Access Denied</h1><p>Staff login required to preview content</p>');
}

$page = $_GET['page'] ?? 'home_public';

// Page file mapping
$pageFiles = [
    'home_public' => '/public/index.php',
    'home_customer' => '/customer/homepage.php',
    'about' => '/public/about.php',
    'privacy' => '/public/privacy.php',
    'terms' => '/public/terms.php'
];

$filePath = __DIR__ . '/../..' . ($pageFiles[$page] ?? '/public/index.php');

if (!file_exists($filePath)) {
    http_response_code(404);
    die('<h1>Preview Error</h1><p>Page file not found: ' . htmlspecialchars($page) . '</p>');
}

// Fetch draft content
$stmt = $pdo->prepare("
    SELECT content_data, status
    FROM rt_cms_pages 
    WHERE page_key = ? AND status = 'draft'
    ORDER BY updated_at DESC 
    LIMIT 1
");
$stmt->execute([$page]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback to published
if (!$result) {
    $stmt = $pdo->prepare("
        SELECT content_data, status
        FROM rt_cms_pages 
        WHERE page_key = ? AND status = 'published'
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$page]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Set preview content globally
$GLOBALS['cms_preview_content'] = $result ? json_decode($result['content_data'], true) : [];

// CRITICAL: Mock customer session for customer homepage
if ($page === 'home_customer') {
    $_SESSION['customer'] = [
        'id' => 999999,
        'full_name' => '[Customer Name]',
        'email' => 'preview@rads.com',
        'username' => 'preview_customer',
        'profile_image' => null
    ];
}

// Capture page output
ob_start();
include $filePath;
$output = ob_get_clean();

// Disable interactive elements
$output = preg_replace('/<a\s+([^>]*)href="([^"]*)"/', '<a $1href="javascript:void(0)" style="pointer-events:none !important; cursor:not-allowed !important; opacity:0.7 !important;"', $output);
$output = preg_replace('/<button([^>]*)>/', '<button$1 disabled style="pointer-events:none !important; cursor:not-allowed !important; opacity:0.7 !important;">', $output);
$output = preg_replace('/<form([^>]*)>/', '<form$1 onsubmit="return false;">', $output);

// Inject preview protection script
// Inject preview protection script
$previewScript = <<<'SCRIPT'
<script>
(function() {
    'use strict';
    
    // Safety check - only run if document exists
    if (typeof document === 'undefined') return;
    
    // Prevent navigation with safer checks
    function preventNavigation(e) {
        if (e && typeof e.preventDefault === 'function') {
            e.preventDefault();
            e.stopPropagation();
        }
        return false;
    }
    
    // Attach listeners only if addEventListener exists
    if (document.addEventListener) {
        document.addEventListener('click', preventNavigation, true);
        document.addEventListener('submit', preventNavigation, true);
    }
    
    // Add watermark safely
    function addWatermark() {
        if (!document.body) {
            // Try again in 100ms if body doesn't exist yet
            setTimeout(addWatermark, 100);
            return;
        }
        
        // Check if already exists
        if (document.getElementById('cms-preview-watermark')) return;
        
        try {
            var watermark = document.createElement('div');
            watermark.id = 'cms-preview-watermark';
            watermark.style.cssText = 'position:fixed;top:10px;right:10px;background:rgba(255,193,7,0.95);color:#000;padding:10px 20px;border-radius:6px;font-weight:bold;z-index:999999;font-size:14px;font-family:Arial,sans-serif;box-shadow:0 2px 8px rgba(0,0,0,0.3);pointer-events:none;';
            watermark.textContent = 'PREVIEW MODE';
            document.body.appendChild(watermark);
        } catch(e) {
            // Silently fail if watermark can't be added
        }
    }
    
    // Multiple attempts to add watermark
    if (document.readyState === 'loading') {
        if (document.addEventListener) {
            document.addEventListener('DOMContentLoaded', addWatermark);
        }
    } else {
        addWatermark();
    }
    setTimeout(addWatermark, 100);
    setTimeout(addWatermark, 500);
})();
</script>
SCRIPT;

$output = str_replace('</body>', $previewScript . '</body>', $output);

// Output
echo $output;
