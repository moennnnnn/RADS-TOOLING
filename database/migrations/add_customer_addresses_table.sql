-- Migration: Add customer_addresses table with PSGC support
-- Created: 2025-11-06
-- Purpose: Enable customers to save multiple addresses with PSGC (Province, City, Barangay)

-- Create customer_addresses table
CREATE TABLE IF NOT EXISTS `customer_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `address_nickname` varchar(50) DEFAULT NULL COMMENT 'Optional nickname (e.g., Home, Office)',
  `full_name` varchar(100) NOT NULL,
  `mobile_number` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,

  -- PSGC fields
  `province` varchar(100) NOT NULL,
  `province_code` varchar(20) DEFAULT NULL COMMENT 'PSGC province code',
  `city_municipality` varchar(100) NOT NULL,
  `city_code` varchar(20) DEFAULT NULL COMMENT 'PSGC city/municipality code',
  `barangay` varchar(100) NOT NULL,
  `barangay_code` varchar(20) DEFAULT NULL COMMENT 'PSGC barangay code',

  -- Address details
  `street_block_lot` text NOT NULL COMMENT 'Complete street address',
  `postal_code` varchar(10) DEFAULT NULL,

  -- Default address flag
  `is_default` tinyint(1) NOT NULL DEFAULT 0,

  -- Timestamps
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),

  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `is_default` (`is_default`),
  CONSTRAINT `customer_addresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add index for faster lookups
CREATE INDEX idx_customer_default ON customer_addresses(customer_id, is_default);

-- Update orders table to store delivery address reference
ALTER TABLE `orders`
ADD COLUMN `delivery_address_id` int(11) DEFAULT NULL COMMENT 'Reference to customer_addresses table',
ADD COLUMN `delivery_contact_name` varchar(100) DEFAULT NULL,
ADD COLUMN `delivery_mobile` varchar(20) DEFAULT NULL,
ADD COLUMN `delivery_email` varchar(255) DEFAULT NULL,
ADD COLUMN `delivery_address_full` text DEFAULT NULL COMMENT 'Full address snapshot at order time',
ADD COLUMN `pickup_contact_name` varchar(100) DEFAULT NULL,
ADD COLUMN `pickup_mobile` varchar(20) DEFAULT NULL,
ADD COLUMN `pickup_email` varchar(255) DEFAULT NULL,
ADD KEY `delivery_address_id` (`delivery_address_id`);

-- Add foreign key constraint (optional - allows NULL for pickup orders)
-- Commented out to avoid errors if address is deleted
-- ALTER TABLE `orders` ADD CONSTRAINT `orders_ibfk_delivery_address`
-- FOREIGN KEY (`delivery_address_id`) REFERENCES `customer_addresses` (`id`) ON DELETE SET NULL;

-- Add T&C acceptance tracking to payment_verifications
ALTER TABLE `payment_verifications`
ADD COLUMN `terms_accepted` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'User accepted T&C before submission',
ADD COLUMN `terms_accepted_at` timestamp NULL DEFAULT NULL COMMENT 'When T&C was accepted';

-- Optional: Migrate existing customer address to customer_addresses table
-- This will create one address record for each customer who has an address in their profile
INSERT INTO `customer_addresses`
  (`customer_id`, `full_name`, `mobile_number`, `email`, `province`, `city_municipality`, `barangay`, `street_block_lot`, `is_default`)
SELECT
  c.id,
  c.full_name,
  COALESCE(c.phone, ''),
  c.email,
  'Metro Manila' as province,
  'Quezon City' as city_municipality,
  '' as barangay,
  COALESCE(c.address, '') as street_block_lot,
  1 as is_default
FROM customers c
WHERE c.address IS NOT NULL
  AND c.address != ''
  AND NOT EXISTS (
    SELECT 1 FROM customer_addresses ca WHERE ca.customer_id = c.id
  );

-- Done!
-- Next steps:
-- 1. Run this migration: mysql -u [user] -p rads_tooling < add_customer_addresses_table.sql
-- 2. Verify tables created: SHOW TABLES LIKE 'customer_addresses';
-- 3. Check structure: DESC customer_addresses;
