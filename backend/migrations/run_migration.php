<?php
/**
 * Migration Script: Add Logo and Footer Settings
 * Run this once to add global logo and footer settings to the CMS
 */

require_once __DIR__ . '/../config/database.php';

try {
    // Check if settings already exist
    $check = $pdo->prepare("SELECT page_key FROM rt_cms_pages WHERE page_key IN ('logo_settings', 'footer_settings')");
    $check->execute();
    $existing = $check->fetchAll(PDO::FETCH_COLUMN);

    if (in_array('logo_settings', $existing) && in_array('footer_settings', $existing)) {
        echo "✅ Logo and Footer settings already exist in database.\n";
        exit(0);
    }

    // Start transaction
    $pdo->beginTransaction();

    // 1. Insert Logo Settings (if not exists)
    if (!in_array('logo_settings', $existing)) {
        $logoData = json_encode([
            'logo_type' => 'text',
            'logo_text' => 'RADS TOOLING',
            'logo_image' => ''
        ]);

        $stmt = $pdo->prepare("
            INSERT INTO rt_cms_pages (page_key, page_name, content_data, status, version, updated_by, created_at, updated_at)
            VALUES ('logo_settings', 'Logo Settings', :content_data, 'published', 1, 'System', NOW(), NOW())
        ");
        $stmt->execute(['content_data' => $logoData]);
        echo "✅ Logo Settings created successfully.\n";
    } else {
        echo "ℹ️  Logo Settings already exists.\n";
    }

    // 2. Insert Footer Settings (if not exists)
    if (!in_array('footer_settings', $existing)) {
        $footerData = json_encode([
            'footer_company_name' => 'About RADS TOOLING',
            'footer_description' => 'Premium custom cabinet manufacturer serving clients since 2007. Quality craftsmanship, affordable prices, and exceptional service.',
            'footer_email' => 'RadsTooling@gmail.com',
            'footer_phone' => '+63 976 228 4270',
            'footer_address' => 'Green Breeze, Piela, Dasmariñas, Cavite',
            'footer_hours' => 'Mon-Sat: 8:00 AM - 5:00 PM',
            'footer_facebook' => '',
            'footer_copyright' => '© 2025 RADS TOOLING INC. All rights reserved.'
        ]);

        $stmt = $pdo->prepare("
            INSERT INTO rt_cms_pages (page_key, page_name, content_data, status, version, updated_by, created_at, updated_at)
            VALUES ('footer_settings', 'Footer Settings', :content_data, 'published', 1, 'System', NOW(), NOW())
        ");
        $stmt->execute(['content_data' => $footerData]);
        echo "✅ Footer Settings created successfully.\n";
    } else {
        echo "ℹ️  Footer Settings already exists.\n";
    }

    // Commit transaction
    $pdo->commit();

    // Verify
    $verify = $pdo->query("SELECT page_key, page_name, status FROM rt_cms_pages WHERE page_key IN ('logo_settings', 'footer_settings')");
    $results = $verify->fetchAll(PDO::FETCH_ASSOC);

    echo "\n📋 Current Settings:\n";
    foreach ($results as $row) {
        echo "   - {$row['page_name']} ({$row['page_key']}): {$row['status']}\n";
    }

    echo "\n✨ Migration completed successfully!\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
