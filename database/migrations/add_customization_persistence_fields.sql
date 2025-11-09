-- Migration: Add Customization Persistence Fields to Orders and Order Items
-- Description: Adds fields to support persisting product customizations through cart → checkout → orders
-- Date: 2025-11-09

-- ===========================================================
-- STEP 1: Add columns to `orders` table
-- ===========================================================

ALTER TABLE `orders`
ADD COLUMN `addons_total` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Sum of all customization add-on prices' AFTER `total_amount`,
ADD COLUMN `base_total` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Base product price before add-ons' AFTER `addons_total`,
ADD COLUMN `grand_total` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Base + addons + VAT (total_amount)' AFTER `base_total`,
ADD COLUMN `customizations` TEXT DEFAULT NULL COMMENT 'JSON snapshot of selected customizations' AFTER `grand_total`,
ADD COLUMN `is_customized` TINYINT(1) DEFAULT 0 COMMENT '1 if order contains customized items' AFTER `customizations`;

-- ===========================================================
-- STEP 2: Add column to `order_items` table
-- ===========================================================

ALTER TABLE `order_items`
ADD COLUMN `item_customizations` TEXT DEFAULT NULL COMMENT 'JSON snapshot of customizations for this item' AFTER `image`,
ADD COLUMN `addons_price` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Total add-ons price for this item' AFTER `item_customizations`,
ADD COLUMN `base_price` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Base price before customizations' AFTER `addons_price`;

-- ===========================================================
-- STEP 3: Add index for performance
-- ===========================================================

ALTER TABLE `orders`
ADD INDEX `idx_is_customized` (`is_customized`);

-- ===========================================================
-- MIGRATION NOTES:
-- ===========================================================
-- 1. `customizations` and `item_customizations` use TEXT for MariaDB compatibility
-- 2. Data is stored as JSON string and parsed in application layer
-- 3. Snapshot approach ensures historical accuracy even if customization options change
-- 4. `grand_total` duplicates `total_amount` for clarity (can be synced via trigger if needed)
-- 5. `addons_total` = SUM(texture_price + color_price + handle_price + size_adjustments)
-- 6. `base_total` = SUM(base_product_prices)
-- 7. Server-side validation MUST recalculate all prices; client values are for preview only
