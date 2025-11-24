<?php
// backend/lib/cms_helper.php

/**
 * Get CMS JSON for a page.
 * - published by default
 * - when $preferDraft=true, tries draft then falls back to published
 */

require_once __DIR__ . '/../backend/config/app.php';
function getCMSContent(string $pageKey, bool $preferDraft = false): array {
    global $pdo; // from backend/config/app.php

    $sql = "
      SELECT content_data
      FROM rt_cms_pages
      WHERE page_key = :k AND status = :s
      ORDER BY updated_at DESC
      LIMIT 1
    ";

    $try = function(string $status) use ($pdo, $sql, $pageKey) {
        $st = $pdo->prepare($sql);
        $st->execute([':k' => $pageKey, ':s' => $status]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $data = json_decode($row['content_data'] ?? '{}', true);
        return is_array($data) ? $data : [];
    };

    if ($preferDraft) {
        return $try('draft') ?? $try('published') ?? [];
    }
    return $try('published') ?? $try('draft') ?? [];
}

/** Replace template tokens like {{customer_name}} */
function cmsTokens(string $html, array $vars = []): string {
    $map = [
        '{{customer_name}}' => $vars['customer_name'] ?? '{Customer Name}',
    ];
    return strtr($html, $map);
}

/**
 * Get global logo settings from CMS
 * Returns logo configuration including type (text/image), text, and image path
 */
function getLogoSettings(bool $preferDraft = false): array {
    $settings = getCMSContent('logo_settings', $preferDraft);

    // Provide defaults if settings don't exist
    return array_merge([
        'logo_type' => 'text',
        'logo_text' => 'RADS TOOLING',
        'logo_image' => ''
    ], $settings);
}

/**
 * Get global footer settings from CMS
 * Returns footer configuration including contact info, social links, etc.
 */
function getFooterSettings(bool $preferDraft = false): array {
    $settings = getCMSContent('footer_settings', $preferDraft);

    // Provide defaults if settings don't exist
    return array_merge([
        'footer_company_name' => 'About RADS TOOLING',
        'footer_description' => 'Premium custom cabinet manufacturer serving clients since 2007.',
        'footer_email' => 'RadsTooling@gmail.com',
        'footer_phone' => '+63 976 228 4270',
        'footer_address' => 'Green Breeze, Piela, Dasmariñas, Cavite',
        'footer_hours' => 'Mon-Sat: 8:00 AM - 5:00 PM',
        'footer_facebook' => '',
        'footer_copyright' => '© 2025 RADS TOOLING INC. All rights reserved.'
    ], $settings);
}
