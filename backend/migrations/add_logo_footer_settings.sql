-- Migration: Add Logo Settings and Footer Settings to CMS
-- Date: 2025-11-24
-- Description: Adds global logo and footer settings as new pages in rt_cms_pages

-- 1. Insert Logo Settings (default to text logo)
INSERT INTO `rt_cms_pages` (`page_key`, `page_name`, `content_data`, `status`, `version`, `updated_by`, `created_at`, `updated_at`)
VALUES (
    'logo_settings',
    'Logo Settings',
    '{"logo_type":"text","logo_text":"RADS TOOLING","logo_image":""}',
    'published',
    1,
    'System',
    NOW(),
    NOW()
);

-- 2. Insert Footer Settings (migrate from home_customer)
-- Using existing footer data as defaults
INSERT INTO `rt_cms_pages` (`page_key`, `page_name`, `content_data`, `status`, `version`, `updated_by`, `created_at`, `updated_at`)
VALUES (
    'footer_settings',
    'Footer Settings',
    '{"footer_company_name":"About RADS TOOLING","footer_description":"Premium custom cabinet manufacturer serving clients since 2007. Quality craftsmanship, affordable prices, and exceptional service.","footer_email":"RadsTooling@gmail.com","footer_phone":"+63 976 228 4270","footer_address":"Green Breeze, Piela, Dasmariñas, Cavite","footer_hours":"Mon-Sat: 8:00 AM - 5:00 PM","footer_facebook":"","footer_copyright":"© 2025 RADS TOOLING INC. All rights reserved."}',
    'published',
    1,
    'System',
    NOW(),
    NOW()
);

-- Verification queries (optional - comment out if not needed)
-- SELECT * FROM rt_cms_pages WHERE page_key IN ('logo_settings', 'footer_settings');
