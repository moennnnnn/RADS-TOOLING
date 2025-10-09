<?php
/**
 * Content Management API
 * Handles CRUD operations for website content
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../../includes/guard.php';

// Ensure only authenticated staff can access
guard_require_staff();

if (!isset($pdo) || !$pdo) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection not available'
    ]);
    exit;
}

// Set JSON header
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Don't display errors in output
ini_set('log_errors', 1);

// Get action parameter
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            getContent();
            break;
            
        case 'save':
            saveContent();
            break;
            
        case 'get_versions':
            getVersions();
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

/**
 * Get content for a specific page
 */
function getContent() {
    global $pdo;
    
    $page = $_GET['page'] ?? 'home';
    
    // Validate page parameter
    $validPages = ['home', 'about', 'privacy', 'terms', 'global'];
    if (!in_array($page, $validPages)) {
        throw new Exception('Invalid page specified: ' . $page);
    }
    
    try {
        // Query the database for published content
        $stmt = $pdo->prepare("
            SELECT content_data, updated_at, updated_by
            FROM rt_cms_pages 
            WHERE page_key = ? AND status = 'published'
            ORDER BY updated_at DESC 
            LIMIT 1
        ");
        
        $stmt->execute([$page]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $content = json_decode($result['content_data'], true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON in database: ' . json_last_error_msg());
            }
            
            echo json_encode([
                'success' => true,
                'content' => $content,
                'updated_at' => $result['updated_at'],
                'updated_by' => $result['updated_by']
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            // Return default empty content if no published version exists
            echo json_encode([
                'success' => true,
                'content' => getDefaultContent($page),
                'updated_at' => null,
                'updated_by' => 'System'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

/**
 * Save content (draft or published)
 */
function saveContent() {
    global $pdo;
    
    $page = $_POST['page'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    
    // Validate inputs
    $validPages = ['home', 'about', 'privacy', 'terms', 'global'];
    if (!in_array($page, $validPages)) {
        throw new Exception('Invalid page specified');
    }
    
    $validStatuses = ['draft', 'published'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status specified');
    }
    
    // Collect content data based on page
    $contentData = [];
    
    switch ($page) {
        case 'home':
            $contentData = [
                'hero_headline' => $_POST['home_hero_headline'] ?? '',
                'hero_subtext' => $_POST['home_hero_subtext'] ?? '',
                'promo_text' => $_POST['home_promo_text'] ?? '',
                'bg_color' => $_POST['home_bg_color'] ?? '#f5f7fa',
                'container_color' => $_POST['home_container_color'] ?? '#ffffff'
            ];
            break;
            
        case 'about':
            $contentData = [
                'about_mission' => $_POST['about_mission'] ?? '',
                'about_vision' => $_POST['about_vision'] ?? '',
                'about_narrative' => $_POST['about_narrative'] ?? '',
                'about_address' => $_POST['about_address'] ?? '',
                'about_phone' => $_POST['about_phone'] ?? '',
                'about_email' => $_POST['about_email'] ?? '',
                'about_hours_weekday' => $_POST['about_hours_weekday'] ?? '',
                'about_hours_sunday' => $_POST['about_hours_sunday'] ?? '',
                'bg_color' => $_POST['about_bg_color'] ?? '#f5f7fa',
                'container_color' => $_POST['about_container_color'] ?? '#ffffff'
            ];
            break;
            
        case 'privacy':
            $contentData = [
                'content' => $_POST['privacy_content'] ?? '',
                'bg_color' => $_POST['privacy_bg_color'] ?? '#f5f7fa',
                'container_color' => $_POST['privacy_container_color'] ?? '#ffffff'
            ];
            break;
            
        case 'terms':
            $contentData = [
                'content' => $_POST['terms_content'] ?? '',
                'bg_color' => $_POST['terms_bg_color'] ?? '#f5f7fa',
                'container_color' => $_POST['terms_container_color'] ?? '#ffffff'
            ];
            break;
            
        case 'global':
            $contentData = [
                'nav_home' => $_POST['nav_home'] ?? 'Home',
                'nav_about' => $_POST['nav_about'] ?? 'About',
                'nav_products' => $_POST['nav_products'] ?? 'Products',
                'global_phone' => $_POST['global_phone'] ?? '',
                'global_email' => $_POST['global_email'] ?? '',
                'footer_about' => $_POST['footer_about'] ?? '',
                'footer_links' => $_POST['footer_links'] ?? '',
                'footer_categories' => $_POST['footer_categories'] ?? '',
                'footer_contact' => $_POST['footer_contact'] ?? '',
                'social_facebook' => $_POST['social_facebook'] ?? '',
                'social_instagram' => $_POST['social_instagram'] ?? '',
                'social_twitter' => $_POST['social_twitter'] ?? '',
                'social_email' => $_POST['social_email'] ?? '',
                'footer_copyright' => $_POST['footer_copyright'] ?? ''
            ];
            break;
    }
    
    // Get staff info from session
    $staffName = $_SESSION['staff']['full_name'] ?? $_SESSION['admin_name'] ?? 'Admin';
    
    // Get page display name
    $pageNames = [
        'home' => 'Homepage',
        'about' => 'About Us',
        'privacy' => 'Privacy Policy',
        'terms' => 'Terms & Conditions',
        'global' => 'Navigation & Footer'
    ];
    $pageName = $pageNames[$page] ?? ucfirst($page);
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // If publishing, delete any existing published version
        if ($status === 'published') {
            $stmt = $pdo->prepare("
                DELETE FROM rt_cms_pages 
                WHERE page_key = ? AND status = 'published'
            ");
            $stmt->execute([$page]);
        }
        
        // Get next version number
        $stmt = $pdo->prepare("
            SELECT COALESCE(MAX(version), 0) + 1 as next_version 
            FROM rt_cms_pages 
            WHERE page_key = ?
        ");
        $stmt->execute([$page]);
        $versionResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $nextVersion = $versionResult['next_version'] ?? 1;
        
        // Insert new content
        $stmt = $pdo->prepare("
            INSERT INTO rt_cms_pages 
            (page_key, page_name, content_data, status, version, updated_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $contentJson = json_encode($contentData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        if ($contentJson === false) {
            throw new Exception('Failed to encode content as JSON: ' . json_last_error_msg());
        }
        
        $stmt->execute([
            $page,
            $pageName,
            $contentJson,
            $status,
            $nextVersion,
            $staffName
        ]);
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => $status === 'draft' ? 'Draft saved successfully' : 'Content published successfully',
            'version' => $nextVersion
        ]);
        
    } catch (PDOException $e) {
        // Rollback on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

/**
 * Get version history for a page
 */
function getVersions() {
    global $pdo;
    
    $page = $_GET['page'] ?? 'home';
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, version, status, updated_by, 
                   DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
            FROM rt_cms_pages
            WHERE page_key = ?
            ORDER BY updated_at DESC
            LIMIT 20
        ");
        
        $stmt->execute([$page]);
        $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'versions' => $versions
        ]);
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

/**
 * Get default content structure for a page
 */
function getDefaultContent($page) {
    $defaults = [
        'home' => [
            'hero_headline' => '<h1>Welcome to RADS Tooling</h1>',
            'hero_subtext' => '<p>Your trusted partner in precision tooling solutions</p>',
            'promo_text' => '<p>Special offers available now!</p>',
            'bg_color' => '#f5f7fa',
            'container_color' => '#ffffff'
        ],
        'about' => [
            'about_mission' => '<p>To provide high-quality tooling solutions.</p>',
            'about_vision' => '<p>To become the leading provider in the Philippines.</p>',
            'about_narrative' => '<p>Founded with a passion for precision and innovation.</p>',
            'about_address' => 'Green Breeze, Piela, Dasmariñas, Cavite',
            'about_phone' => '+63 (976) 228-4270',
            'about_email' => 'RadsTooling@gmail.com',
            'about_hours_weekday' => 'Mon-Sat: 8:00 AM - 5:00 PM',
            'about_hours_sunday' => 'Closed',
            'bg_color' => '#f5f7fa',
            'container_color' => '#ffffff'
        ],
        'privacy' => [
            'content' => '<h2>Privacy Policy</h2><p>Content coming soon...</p>',
            'bg_color' => '#f5f7fa',
            'container_color' => '#ffffff'
        ],
        'terms' => [
            'content' => '<h2>Terms & Conditions</h2><p>Content coming soon...</p>',
            'bg_color' => '#f5f7fa',
            'container_color' => '#ffffff'
        ],
        'global' => [
            'nav_home' => 'Home',
            'nav_about' => 'About',
            'nav_products' => 'Products',
            'global_phone' => '+63 (976) 228-4270',
            'global_email' => 'RadsTooling@gmail.com',
            'footer_about' => 'Premium custom cabinet manufacturer',
            'footer_links' => '',
            'footer_categories' => '',
            'footer_contact' => '',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_twitter' => '',
            'social_email' => 'RadsTooling@gmail.com',
            'footer_copyright' => '© 2025 RADS TOOLING INC. All rights reserved.'
        ]
    ];
    
    return $defaults[$page] ?? [];
}