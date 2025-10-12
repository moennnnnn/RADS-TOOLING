<?php

while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../../includes/guard.php';

guard_require_staff();

// CRITICAL: Stop ALL output before JSON
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Clean any output buffer
if (ob_get_level()) ob_end_clean();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'get') {
        getContent();
    } elseif ($action === 'save') {
        saveContent();
    } elseif ($action === 'publish') {
        publishContent();
    } elseif ($action === 'discard') {
        discardDraft();
    } elseif ($action === 'upload_image') {
        uploadImage();
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

function getContent()
{
    global $pdo;

    $page = $_GET['page'] ?? 'home_public';
    $status = $_GET['status'] ?? 'draft';

    $validPages = ['home_public', 'home_customer', 'about', 'privacy', 'terms'];
    if (!in_array($page, $validPages)) {
        throw new Exception('Invalid page');
    }

    // Try to get requested status (draft or published)
    $stmt = $pdo->prepare("
        SELECT content_data, updated_at, updated_by, status
        FROM rt_cms_pages 
        WHERE page_key = ? AND status = ?
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$page, $status]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no draft, fall back to published
    if (!$result && $status === 'draft') {
        $stmt->execute([$page, 'published']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($result) {
        $content = json_decode($result['content_data'], true);
        echo json_encode([
            'success' => true,
            'content' => $content,
            'status' => $result['status'],
            'updated_at' => $result['updated_at'],
            'updated_by' => $result['updated_by']
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        // Return default content
        echo json_encode([
            'success' => true,
            'content' => getDefaultContent($page),
            'status' => 'published',
            'updated_at' => null,
            'updated_by' => 'System'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

function saveContent()
{
    global $pdo;

    $page = $_POST['page'] ?? '';
    $content = $_POST['content'] ?? '';

    $validPages = ['home_public', 'home_customer', 'about', 'privacy', 'terms'];
    if (!in_array($page, $validPages)) {
        throw new Exception('Invalid page');
    }

    $contentData = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON content: ' . json_last_error_msg());
    }

    $staffName = $_SESSION['staff']['full_name'] ?? 'Admin';

    $pageNames = [
        'home_public' => 'Public Homepage',
        'home_customer' => 'Customer Homepage',
        'about' => 'About Us',
        'privacy' => 'Privacy Policy',
        'terms' => 'Terms & Conditions'
    ];

    // ADD: Check if content actually changed
    $stmt = $pdo->prepare("
        SELECT content_data 
        FROM rt_cms_pages 
        WHERE page_key = ? AND status = 'draft' 
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$page]);
    $existingDraft = $stmt->fetch(PDO::FETCH_ASSOC);

    $newContentJson = json_encode($contentData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // If draft exists and content is identical, don't save
    if ($existingDraft && $existingDraft['content_data'] === $newContentJson) {
        echo json_encode([
            'success' => true,
            'message' => 'No changes detected',
            'unchanged' => true
        ]);
        return;
    }

    $pdo->beginTransaction();

    // Only delete and insert if content changed
    $stmt = $pdo->prepare("DELETE FROM rt_cms_pages WHERE page_key = ? AND status = 'draft'");
    $stmt->execute([$page]);

    // Get next version
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(version), 0) + 1 as next_version FROM rt_cms_pages WHERE page_key = ?");
    $stmt->execute([$page]);
    $nextVersion = $stmt->fetch(PDO::FETCH_ASSOC)['next_version'];

    // Insert new draft
    $stmt = $pdo->prepare("
        INSERT INTO rt_cms_pages (page_key, page_name, content_data, status, version, updated_by)
        VALUES (?, ?, ?, 'draft', ?, ?)
    ");

    $stmt->execute([$page, $pageNames[$page], $newContentJson, $nextVersion, $staffName]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Draft saved successfully'
    ]);
}

function publishContent()
{
    global $pdo;

    $page = $_POST['page'] ?? '';

    $validPages = ['home_public', 'home_customer', 'about', 'privacy', 'terms'];
    if (!in_array($page, $validPages)) {
        throw new Exception('Invalid page');
    }

    $pdo->beginTransaction();

    // Get latest draft
    $stmt = $pdo->prepare("SELECT * FROM rt_cms_pages WHERE page_key = ? AND status = 'draft' ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$page]);
    $draft = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$draft) {
        throw new Exception('No draft to publish');
    }

    // Delete old published version
    $stmt = $pdo->prepare("DELETE FROM rt_cms_pages WHERE page_key = ? AND status = 'published'");
    $stmt->execute([$page]);

    // Insert draft as published
    $stmt = $pdo->prepare("
        INSERT INTO rt_cms_pages (page_key, page_name, content_data, status, version, updated_by)
        VALUES (?, ?, ?, 'published', ?, ?)
    ");
    $stmt->execute([$draft['page_key'], $draft['page_name'], $draft['content_data'], $draft['version'], $draft['updated_by']]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Content published successfully'
    ]);
}

function discardDraft()
{
    global $pdo;

    $page = $_POST['page'] ?? '';

    $validPages = ['home_public', 'home_customer', 'about', 'privacy', 'terms'];
    if (!in_array($page, $validPages)) {
        throw new Exception('Invalid page');
    }

    $stmt = $pdo->prepare("DELETE FROM rt_cms_pages WHERE page_key = ? AND status = 'draft'");
    $stmt->execute([$page]);

    echo json_encode([
        'success' => true,
        'message' => 'Draft discarded successfully'
    ]);
}

function getDefaultContent($page)
{
    $defaults = [
        'home_public' => [
            'hero_headline' => '<h1>Welcome to RADS Tooling</h1>',
            'hero_subtitle' => '<p>Your trusted partner in custom cabinets</p>',
            'carousel_images' => [],
            'footer_email' => 'RadsTooling@gmail.com',
            'footer_phone' => '+63 976 228 4270'
        ],
        'home_customer' => [
            'welcome_message' => '<h1>Welcome back, {{customer_name}}!</h1>',
            'intro_text' => '<p>Explore our latest designs</p>'
        ],
        'about' => [
            'content' => '<h1>About Us</h1><p>Content goes here...</p>'
        ],
        'privacy' => [
            'content' => '<h1>Privacy Policy</h1><p>Content goes here...</p>'
        ],
        'terms' => [
            'content' => '<h1>Terms & Conditions</h1><p>Content goes here...</p>'
        ]
    ];

    return $defaults[$page] ?? [];
}

function uploadImage()
{
    if (!isset($_FILES['image'])) {
        throw new Exception('No file uploaded');
    }

    $file = $_FILES['image'];
    $group = $_POST['group'] ?? 'general';

    // Use your existing upload.php function
    require_once __DIR__ . '/../lib/upload.php';

    $result = uploadImage($file, $group);

    echo json_encode($result);
}
