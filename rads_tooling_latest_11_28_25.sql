-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2025 at 09:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rads_tooling`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Owner','Admin','Secretary') NOT NULL DEFAULT 'Secretary',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `full_name`, `role`, `status`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$tZQpbFsqPgp1f0eCMXI6ZeM9guDbJt6631WkLtuoZcN1eKazk/W4u', 'System Owner44', 'Owner', 'active', NULL, '2025-08-19 12:27:17', '2025-10-23 11:20:58'),
(11, 'kooqi', '$2y$10$0ayuZ7sCCIC3TG65krYecOVpNjQopWI8YYSJ1NCtxyYVy0t7dpR9O', 'Moen', 'Secretary', 'active', NULL, '2025-11-04 06:25:06', '2025-11-07 10:27:34');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `type` enum('banner','promotion','announcement') DEFAULT 'announcement',
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cabinets`
--

CREATE TABLE `cabinets` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `item_type` enum('product','cabinet') NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `cabinet_id` int(11) DEFAULT NULL,
  `customization_id` int(11) DEFAULT NULL,
  `customization_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customization_json`)),
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `id` int(11) NOT NULL,
  `color_name` varchar(50) NOT NULL,
  `color_code` varchar(50) NOT NULL,
  `hex_value` varchar(7) NOT NULL,
  `base_price` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`id`, `color_name`, `color_code`, `hex_value`, `base_price`, `is_active`, `created_at`) VALUES
(9, 'Green', 'GREEN', '#23640c', 200.00, 1, '2025-11-08 16:25:56'),
(10, 'Orange', 'ORANGE', '#db4d00', 900.00, 1, '2025-11-08 19:46:46');

-- --------------------------------------------------------

--
-- Table structure for table `color_allowed_parts`
--

CREATE TABLE `color_allowed_parts` (
  `id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `part_name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_code` varchar(10) DEFAULT NULL,
  `verification_expires` datetime DEFAULT NULL,
  `password_reset_code` varchar(10) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `username`, `full_name`, `email`, `profile_image`, `email_verified`, `verification_code`, `verification_expires`, `password_reset_code`, `password_reset_expires`, `phone`, `address`, `password`, `created_at`, `updated_at`) VALUES
(40, 'capstonk', 'Capstonk Capstoink', 'capstoink@gmail.com', NULL, 1, NULL, NULL, NULL, NULL, '+639856268378', 'blk 1 lot 2345', '$2y$10$4Umt5iaKmtFkNUtIBJfW5uFUkWbj8fLqYDdQXLdbpixt2k.h2i3ZG', '2025-11-03 04:07:14', '2025-11-07 12:32:15'),
(41, 'moenpogi21', 'Moen Secapuri', 'moenpogi045@gmail.com', NULL, 1, NULL, NULL, NULL, NULL, '+639937060282', NULL, '$2y$10$VEdXOXrq/DE1GwFg4v/zMeVVVaFjkhZKiJEb/kS0lnTwezu5fFtGi', '2025-11-04 03:59:03', '2025-11-04 03:59:22');

-- --------------------------------------------------------

--
-- Table structure for table `customizations`
--

CREATE TABLE `customizations` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `cabinet_id` int(11) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `layout` varchar(50) DEFAULT NULL,
  `material` varchar(50) DEFAULT NULL,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `width` decimal(10,2) DEFAULT NULL,
  `height` decimal(10,2) DEFAULT NULL,
  `depth` decimal(10,2) DEFAULT NULL,
  `texture_id` int(11) DEFAULT NULL,
  `color_id` int(11) DEFAULT NULL,
  `handle_id` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT 0.00,
  `measurement_unit` varchar(20) DEFAULT 'cm',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customization_steps`
--

CREATE TABLE `customization_steps` (
  `id` int(11) NOT NULL,
  `customization_id` int(11) NOT NULL,
  `step_name` enum('size','texture','color','handle') NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('pending','released') NOT NULL DEFAULT 'released',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_released` tinyint(1) NOT NULL DEFAULT 1,
  `released_at` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `order_id`, `customer_id`, `rating`, `comment`, `status`, `created_at`, `is_released`, `released_at`, `deleted`) VALUES
(11, 49, 40, 5, 'nice', 'pending', '2025-11-07 04:22:41', 1, NULL, 0),
(12, 50, 40, 5, 'great', 'pending', '2025-11-07 04:22:58', 1, NULL, 0),
(13, 53, 40, 5, 'ang galing', 'pending', '2025-11-07 11:48:12', 1, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `handle_types`
--

CREATE TABLE `handle_types` (
  `id` int(11) NOT NULL,
  `handle_name` varchar(100) NOT NULL,
  `handle_code` varchar(50) NOT NULL,
  `handle_image` varchar(255) NOT NULL,
  `base_price` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `handle_types`
--

INSERT INTO `handle_types` (`id`, `handle_name`, `handle_code`, `handle_image`, `base_price`, `description`, `is_active`, `created_at`) VALUES
(6, 'Straight Bar Handle', 'STRAIGHT_BAR', 'handle_1762676133_69104da5cd88a.jpg', 111.00, 'Straight Bar', 1, '2025-11-09 08:15:35'),
(7, 'Half-Moon Handle', 'HALF_MOON', 'handle_1762677655_691053976656e.jpg', 122.00, 'Half Moon', 1, '2025-11-09 08:40:57');

-- --------------------------------------------------------

--
-- Table structure for table `measurement_units`
--

CREATE TABLE `measurement_units` (
  `id` int(11) NOT NULL,
  `unit_name` varchar(20) NOT NULL,
  `abbreviation` varchar(10) NOT NULL,
  `conversion_to_cm` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_type` enum('customer','admin') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_type` enum('customer','admin') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `read_status` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `addons_total` decimal(12,2) DEFAULT 0.00 COMMENT 'Sum of all customization add-on prices',
  `base_total` decimal(12,2) DEFAULT 0.00 COMMENT 'Base product price before add-ons',
  `grand_total` decimal(12,2) DEFAULT 0.00 COMMENT 'Base + addons + VAT (total_amount)',
  `customizations` text DEFAULT NULL COMMENT 'JSON snapshot of selected customizations',
  `is_customized` tinyint(1) DEFAULT 0 COMMENT '1 if order contains customized items',
  `payment_status` enum('Pending','Partially Paid','Fully Paid') DEFAULT 'Pending',
  `is_installment` tinyint(1) DEFAULT 0 COMMENT '1=split payment, 0=single payment',
  `status` enum('Pending','Processing','Ready for Pickup','Ready for Delivery','In Transit','Delivered','Completed','Cancelled') DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `cancelled_by` enum('customer','admin') DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `received_at` datetime DEFAULT NULL COMMENT 'When customer marked as received',
  `received_by_customer` tinyint(1) DEFAULT 0,
  `is_received` tinyint(1) NOT NULL DEFAULT 0,
  `customer_received_at` datetime DEFAULT NULL,
  `mode` enum('delivery','pickup') NOT NULL DEFAULT 'pickup',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vat` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `customer_id`, `total_amount`, `addons_total`, `base_total`, `grand_total`, `customizations`, `is_customized`, `payment_status`, `is_installment`, `status`, `order_date`, `cancelled_by`, `cancellation_reason`, `cancelled_at`, `received_at`, `received_by_customer`, `is_received`, `customer_received_at`, `mode`, `subtotal`, `vat`, `created_at`) VALUES
(47, 'RT2511043677', 41, 16180.00, 0.00, 0.00, 0.00, NULL, 0, 'Partially Paid', 1, 'Cancelled', '2025-11-04 04:05:18', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 14000.00, 1680.00, '2025-11-04 04:05:18'),
(48, 'RT2511067149', 40, 16180.00, 0.00, 0.00, 0.00, NULL, 0, 'Partially Paid', 1, 'Cancelled', '2025-11-05 21:14:14', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 14000.00, 1680.00, '2025-11-05 21:14:14'),
(49, 'RT2511063157', 40, 15680.00, 0.00, 0.00, 0.00, NULL, 0, 'Partially Paid', 1, 'Completed', '2025-11-05 22:38:39', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 14000.00, 1680.00, '2025-11-05 22:38:39'),
(50, 'RT2511079485', 40, 16180.00, 0.00, 0.00, 0.00, NULL, 0, 'Partially Paid', 1, 'Completed', '2025-11-07 03:29:34', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 14000.00, 1680.00, '2025-11-07 03:29:34'),
(51, 'RT2511070709', 40, 15680.00, 0.00, 0.00, 0.00, NULL, 0, 'Pending', 1, 'Pending', '2025-11-07 03:52:58', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 14000.00, 1680.00, '2025-11-07 03:52:58'),
(52, 'RT2511073519', 40, 15680.00, 0.00, 0.00, 0.00, NULL, 0, 'Pending', 1, 'Pending', '2025-11-07 04:13:58', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 14000.00, 1680.00, '2025-11-07 04:13:58'),
(53, 'RT2511075065', 40, 15680.00, 0.00, 0.00, 0.00, NULL, 0, 'Fully Paid', 1, 'Completed', '2025-11-07 10:37:48', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 14000.00, 1680.00, '2025-11-07 10:37:48'),
(54, 'RT2511073946', 40, 15680.00, 0.00, 0.00, 0.00, NULL, 0, 'Partially Paid', 1, 'Pending', '2025-11-07 10:44:35', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 14000.00, 1680.00, '2025-11-07 10:44:35'),
(55, 'RT2511071729', 40, 16180.00, 0.00, 0.00, 0.00, NULL, 0, 'Fully Paid', 1, 'Processing', '2025-11-07 12:35:14', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 14000.00, 1680.00, '2025-11-07 12:35:14'),
(56, 'RT2511092999', 40, 22500.00, 8500.00, 14000.00, 22500.00, '[{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"door\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":10,\"code\":\"COLOR_10\",\"label\":\"Orange\",\"applies_to\":\"body\",\"price\":900,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"inside\",\"price\":200,\"meta\":null},{\"type\":\"size\",\"id\":0,\"code\":\"SIZE_CUSTOM\",\"label\":\"Custom Size: 210\\u00d7210\\u00d7100 cm\",\"applies_to\":\"all\",\"price\":7200,\"meta\":{\"width\":210,\"height\":210,\"depth\":100,\"unit\":\"cm\",\"width_delta\":2000,\"height_delta\":200,\"depth_delta\":5000}}]', 1, 'Partially Paid', 1, 'Processing', '2025-11-09 06:06:57', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 20089.29, 2410.71, '2025-11-09 06:06:57'),
(57, 'RT2511091945', 40, 27101.00, 26879.00, 222.00, 27101.00, '[{\"type\":\"texture\",\"id\":12,\"code\":\"TEXTURE_12\",\"label\":\"Matte\",\"applies_to\":\"door\",\"price\":234,\"meta\":null},{\"type\":\"texture\",\"id\":10,\"code\":\"TEXTURE_10\",\"label\":\"Oak Wood\",\"applies_to\":\"body\",\"price\":123,\"meta\":null},{\"type\":\"texture\",\"id\":11,\"code\":\"TEXTURE_11\",\"label\":\"DARK WOOD\",\"applies_to\":\"inside\",\"price\":222,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"door\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"body\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":10,\"code\":\"COLOR_10\",\"label\":\"Orange\",\"applies_to\":\"inside\",\"price\":900,\"meta\":null},{\"type\":\"size\",\"id\":0,\"code\":\"SIZE_CUSTOM\",\"label\":\"Custom Size: 300\\u00d7300\\u00d7150 cm\",\"applies_to\":\"all\",\"price\":25000,\"meta\":{\"width\":300,\"height\":300,\"depth\":150,\"unit\":\"cm\",\"width_delta\":20000,\"height_delta\":5000,\"depth_delta\":0}}]', 1, 'Pending', 0, 'Pending', '2025-11-09 08:57:07', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 24197.32, 2903.68, '2025-11-09 08:57:07'),
(58, 'RT2511095588', 40, 27101.00, 26879.00, 222.00, 27101.00, '[{\"type\":\"texture\",\"id\":12,\"code\":\"TEXTURE_12\",\"label\":\"Matte\",\"applies_to\":\"door\",\"price\":234,\"meta\":null},{\"type\":\"texture\",\"id\":10,\"code\":\"TEXTURE_10\",\"label\":\"Oak Wood\",\"applies_to\":\"body\",\"price\":123,\"meta\":null},{\"type\":\"texture\",\"id\":11,\"code\":\"TEXTURE_11\",\"label\":\"DARK WOOD\",\"applies_to\":\"inside\",\"price\":222,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"door\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"body\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":10,\"code\":\"COLOR_10\",\"label\":\"Orange\",\"applies_to\":\"inside\",\"price\":900,\"meta\":null},{\"type\":\"size\",\"id\":0,\"code\":\"SIZE_CUSTOM\",\"label\":\"Custom Size: 300\\u00d7300\\u00d7150 cm\",\"applies_to\":\"all\",\"price\":25000,\"meta\":{\"width\":300,\"height\":300,\"depth\":150,\"unit\":\"cm\",\"width_delta\":20000,\"height_delta\":5000,\"depth_delta\":0}}]', 1, 'Pending', 0, 'Pending', '2025-11-09 09:01:44', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 24197.32, 2903.68, '2025-11-09 09:01:44'),
(59, 'RT2511092721', 40, 344.00, 122.00, 222.00, 344.00, '[{\"type\":\"handle\",\"id\":7,\"code\":\"HANDLE_7\",\"label\":\"Half-Moon Handle\",\"applies_to\":\"all\",\"price\":122,\"meta\":null}]', 1, 'Pending', 0, 'Pending', '2025-11-09 09:06:20', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 307.14, 36.86, '2025-11-09 09:06:20'),
(60, 'RT2511099533', 40, 20222.00, 20000.00, 222.00, 20222.00, '[{\"type\":\"size\",\"id\":0,\"code\":\"SIZE_CUSTOM\",\"label\":\"Custom Size: 300\\u00d7250\\u00d7150 cm\",\"applies_to\":\"all\",\"price\":20000,\"meta\":{\"width\":300,\"height\":250,\"depth\":150,\"unit\":\"cm\",\"width_delta\":20000,\"height_delta\":0,\"depth_delta\":0}}]', 1, 'Pending', 0, 'Pending', '2025-11-09 09:13:45', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 18055.36, 2166.64, '2025-11-09 09:13:45'),
(61, 'RT2511093565', 40, 1990.00, 1879.00, 111.00, 1990.00, '[{\"type\":\"texture\",\"id\":12,\"code\":\"TEXTURE_12\",\"label\":\"Matte\",\"applies_to\":\"door\",\"price\":234,\"meta\":null},{\"type\":\"texture\",\"id\":10,\"code\":\"TEXTURE_10\",\"label\":\"Oak Wood\",\"applies_to\":\"body\",\"price\":123,\"meta\":null},{\"type\":\"texture\",\"id\":11,\"code\":\"TEXTURE_11\",\"label\":\"DARK WOOD\",\"applies_to\":\"inside\",\"price\":222,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"door\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":10,\"code\":\"COLOR_10\",\"label\":\"Orange\",\"applies_to\":\"body\",\"price\":900,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"inside\",\"price\":200,\"meta\":null}]', 1, 'Pending', 1, 'Pending', '2025-11-09 09:53:05', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 1776.79, 213.21, '2025-11-09 09:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `order_addresses`
--

CREATE TABLE `order_addresses` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `type` enum('delivery','pickup') NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `barangay` varchar(120) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `postal` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_addresses`
--

INSERT INTO `order_addresses` (`id`, `order_id`, `type`, `first_name`, `last_name`, `phone`, `email`, `province`, `city`, `barangay`, `street`, `postal`) VALUES
(47, 47, '', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', 'Cavite', 'Alfonso', 'Mangas II', 'Blk 4 Lot 47 Phase A Laterraza Subdivision', '4103'),
(48, 48, '', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', 'Batangas', 'Alitagtag', 'Tadlac', 'Blk 4 Lot 47 Phase A Laterraza Subdivision', '4103'),
(49, 49, '', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', '', '', '', '', ''),
(50, 50, '', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', 'Batangas', 'Agoncillo', 'Bagong Sikat', 'Blk 4 Lot 47 Phase A Laterraza Subdivision', '4103'),
(51, 51, '', 'Capstonk', 'Capstoink', '+639856268378', 'capstoink@gmail.com', '', '', '', '', ''),
(52, 52, '', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', '', '', '', '', ''),
(53, 53, '', 'Capstonk', 'Capstoink', '+639856268378', 'capstoink@gmail.com', '', '', '', '', ''),
(54, 54, '', 'Capstonk', 'Capstoink', '+639856268378', 'capstoink@gmail.com', '', '', '', '', ''),
(55, 55, '', 'Capstonk', 'Capstoink', '+639856268378', 'capstoink@gmail.com', 'Laguna', 'City of Santa Rosa', 'Santo Domingo', 'Blk 4 Lot 47 Phase A Laterraza Subdivision', '4103'),
(56, 56, '', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', '', '', '', '', ''),
(57, 57, '', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', '', '', '', '', ''),
(58, 58, '', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', '', '', '', '', ''),
(59, 59, '', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', '', '', '', '', ''),
(60, 60, '', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', '', '', '', '', ''),
(61, 61, '', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `order_completions`
--

CREATE TABLE `order_completions` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `completed_by` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `completion_type` enum('pickup','delivery') NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `completed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_type` enum('product','cabinet') NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `cabinet_id` int(11) DEFAULT NULL,
  `customization_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `line_total` decimal(12,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL COMMENT 'Product image path',
  `item_customizations` text DEFAULT NULL COMMENT 'JSON snapshot of customizations for this item',
  `addons_price` decimal(10,2) DEFAULT 0.00 COMMENT 'Total add-ons price for this item',
  `base_price` decimal(10,2) DEFAULT 0.00 COMMENT 'Base price before customizations'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_type`, `product_id`, `cabinet_id`, `customization_id`, `quantity`, `unit_price`, `subtotal`, `name`, `qty`, `line_total`, `image`, `item_customizations`, `addons_price`, `base_price`) VALUES
(47, 47, 'product', 47, NULL, NULL, 1, 14000.00, 0.00, 'Storage Cabinet', 1, 14000.00, NULL, NULL, 0.00, 0.00),
(48, 48, 'product', 47, NULL, NULL, 1, 14000.00, 0.00, 'Storage Cabinet', 1, 14000.00, NULL, NULL, 0.00, 0.00),
(49, 49, 'product', 47, NULL, NULL, 1, 14000.00, 0.00, 'Storage Cabinet', 1, 14000.00, NULL, NULL, 0.00, 0.00),
(50, 50, 'product', 47, NULL, NULL, 1, 14000.00, 0.00, 'Storage Cabinet', 1, 14000.00, NULL, NULL, 0.00, 0.00),
(51, 51, 'product', 47, NULL, NULL, 1, 14000.00, 0.00, 'Storage Cabinet', 1, 14000.00, NULL, NULL, 0.00, 0.00),
(52, 52, 'product', 47, NULL, NULL, 1, 14000.00, 0.00, 'Storage Cabinet', 1, 14000.00, NULL, NULL, 0.00, 0.00),
(53, 53, 'product', 47, NULL, NULL, 1, 14000.00, 0.00, 'Storage Cabinet', 1, 14000.00, NULL, NULL, 0.00, 0.00),
(54, 54, 'product', 47, NULL, NULL, 1, 14000.00, 0.00, 'Storage Cabinet', 1, 14000.00, NULL, NULL, 0.00, 0.00),
(55, 55, 'product', 47, NULL, NULL, 1, 14000.00, 0.00, 'Storage Cabinet', 1, 14000.00, NULL, NULL, 0.00, 0.00),
(56, 56, 'product', 47, NULL, NULL, 1, 22500.00, 0.00, 'Storage Cabinet', 1, 22500.00, NULL, '[{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"door\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":10,\"code\":\"COLOR_10\",\"label\":\"Orange\",\"applies_to\":\"body\",\"price\":900,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"inside\",\"price\":200,\"meta\":null},{\"type\":\"size\",\"id\":0,\"code\":\"SIZE_CUSTOM\",\"label\":\"Custom Size: 210\\u00d7210\\u00d7100 cm\",\"applies_to\":\"all\",\"price\":7200,\"meta\":{\"width\":210,\"height\":210,\"depth\":100,\"unit\":\"cm\",\"width_delta\":2000,\"height_delta\":200,\"depth_delta\":5000}}]', 8500.00, 14000.00),
(57, 57, 'product', 50, NULL, NULL, 1, 27101.00, 0.00, 'Wardrobe Cabinet', 1, 27101.00, NULL, '[{\"type\":\"texture\",\"id\":12,\"code\":\"TEXTURE_12\",\"label\":\"Matte\",\"applies_to\":\"door\",\"price\":234,\"meta\":null},{\"type\":\"texture\",\"id\":10,\"code\":\"TEXTURE_10\",\"label\":\"Oak Wood\",\"applies_to\":\"body\",\"price\":123,\"meta\":null},{\"type\":\"texture\",\"id\":11,\"code\":\"TEXTURE_11\",\"label\":\"DARK WOOD\",\"applies_to\":\"inside\",\"price\":222,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"door\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"body\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":10,\"code\":\"COLOR_10\",\"label\":\"Orange\",\"applies_to\":\"inside\",\"price\":900,\"meta\":null},{\"type\":\"size\",\"id\":0,\"code\":\"SIZE_CUSTOM\",\"label\":\"Custom Size: 300\\u00d7300\\u00d7150 cm\",\"applies_to\":\"all\",\"price\":25000,\"meta\":{\"width\":300,\"height\":300,\"depth\":150,\"unit\":\"cm\",\"width_delta\":20000,\"height_delta\":5000,\"depth_delta\":0}}]', 26879.00, 222.00),
(58, 58, 'product', 50, NULL, NULL, 1, 27101.00, 0.00, 'Wardrobe Cabinet', 1, 27101.00, NULL, '[{\"type\":\"texture\",\"id\":12,\"code\":\"TEXTURE_12\",\"label\":\"Matte\",\"applies_to\":\"door\",\"price\":234,\"meta\":null},{\"type\":\"texture\",\"id\":10,\"code\":\"TEXTURE_10\",\"label\":\"Oak Wood\",\"applies_to\":\"body\",\"price\":123,\"meta\":null},{\"type\":\"texture\",\"id\":11,\"code\":\"TEXTURE_11\",\"label\":\"DARK WOOD\",\"applies_to\":\"inside\",\"price\":222,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"door\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"body\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":10,\"code\":\"COLOR_10\",\"label\":\"Orange\",\"applies_to\":\"inside\",\"price\":900,\"meta\":null},{\"type\":\"size\",\"id\":0,\"code\":\"SIZE_CUSTOM\",\"label\":\"Custom Size: 300\\u00d7300\\u00d7150 cm\",\"applies_to\":\"all\",\"price\":25000,\"meta\":{\"width\":300,\"height\":300,\"depth\":150,\"unit\":\"cm\",\"width_delta\":20000,\"height_delta\":5000,\"depth_delta\":0}}]', 26879.00, 222.00),
(59, 59, 'product', 50, NULL, NULL, 1, 344.00, 0.00, 'Wardrobe Cabinet', 1, 344.00, NULL, '[{\"type\":\"handle\",\"id\":7,\"code\":\"HANDLE_7\",\"label\":\"Half-Moon Handle\",\"applies_to\":\"all\",\"price\":122,\"meta\":null}]', 122.00, 222.00),
(60, 60, 'product', 50, NULL, NULL, 1, 20222.00, 0.00, 'Wardrobe Cabinet', 1, 20222.00, NULL, '[{\"type\":\"size\",\"id\":0,\"code\":\"SIZE_CUSTOM\",\"label\":\"Custom Size: 300\\u00d7250\\u00d7150 cm\",\"applies_to\":\"all\",\"price\":20000,\"meta\":{\"width\":300,\"height\":250,\"depth\":150,\"unit\":\"cm\",\"width_delta\":20000,\"height_delta\":0,\"depth_delta\":0}}]', 20000.00, 222.00),
(61, 61, 'product', 49, NULL, NULL, 1, 1990.00, 0.00, 'test', 1, 1990.00, NULL, '[{\"type\":\"texture\",\"id\":12,\"code\":\"TEXTURE_12\",\"label\":\"Matte\",\"applies_to\":\"door\",\"price\":234,\"meta\":null},{\"type\":\"texture\",\"id\":10,\"code\":\"TEXTURE_10\",\"label\":\"Oak Wood\",\"applies_to\":\"body\",\"price\":123,\"meta\":null},{\"type\":\"texture\",\"id\":11,\"code\":\"TEXTURE_11\",\"label\":\"DARK WOOD\",\"applies_to\":\"inside\",\"price\":222,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"door\",\"price\":200,\"meta\":null},{\"type\":\"color\",\"id\":10,\"code\":\"COLOR_10\",\"label\":\"Orange\",\"applies_to\":\"body\",\"price\":900,\"meta\":null},{\"type\":\"color\",\"id\":9,\"code\":\"COLOR_9\",\"label\":\"Green\",\"applies_to\":\"inside\",\"price\":200,\"meta\":null}]', 1879.00, 111.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('Pending','Processing','Completed','Cancelled') NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_type` enum('customer','admin') NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` enum('GCash QR','Cash','Bank Transfer','Cheque') NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `method` enum('gcash','bpi') DEFAULT NULL,
  `deposit_rate` int(11) DEFAULT NULL,
  `amount_due` decimal(12,2) DEFAULT NULL,
  `status` enum('PENDING_VERIFICATION','VERIFIED','REJECTED') DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `amount_paid`, `payment_method`, `qr_code`, `payment_date`, `verified_by`, `verified_at`, `method`, `deposit_rate`, `amount_due`, `status`, `updated_at`) VALUES
(45, 47, 8090.00, 'GCash QR', NULL, '2025-11-04 04:05:18', 1, '2025-11-04 04:06:41', 'gcash', 50, 8090.00, 'VERIFIED', '2025-11-04 04:06:41'),
(46, 48, 4854.00, 'GCash QR', NULL, '2025-11-05 21:14:14', 1, '2025-11-05 21:15:30', 'gcash', 30, 4854.00, 'VERIFIED', '2025-11-05 21:15:30'),
(48, 49, 7840.00, 'GCash QR', NULL, '2025-11-05 22:38:39', 1, '2025-11-05 22:40:38', 'gcash', 50, 7840.00, 'VERIFIED', '2025-11-05 22:40:38'),
(49, 50, 8090.00, 'GCash QR', NULL, '2025-11-07 03:29:35', 1, '2025-11-07 03:30:10', 'bpi', 50, 8090.00, 'VERIFIED', '2025-11-07 03:30:10'),
(50, 51, 0.00, 'GCash QR', NULL, '2025-11-07 03:52:58', NULL, NULL, 'gcash', 50, 7840.00, '', '2025-11-07 03:52:58'),
(51, 52, 0.00, 'GCash QR', NULL, '2025-11-07 04:13:58', NULL, NULL, 'gcash', 50, 7840.00, '', '2025-11-07 04:13:58'),
(52, 53, 15680.00, 'GCash QR', NULL, '2025-11-07 10:37:48', 1, '2025-11-07 11:46:57', 'gcash', 50, 7840.00, 'VERIFIED', '2025-11-07 11:46:57'),
(53, 54, 4704.00, 'GCash QR', NULL, '2025-11-07 10:44:36', 1, '2025-11-07 10:45:16', 'gcash', 30, 4704.00, 'VERIFIED', '2025-11-07 10:45:16'),
(54, 55, 16180.00, 'GCash QR', NULL, '2025-11-07 12:35:14', 1, '2025-11-09 08:08:58', 'gcash', 50, 8090.00, 'VERIFIED', '2025-11-09 08:08:58'),
(56, 56, 11250.00, 'GCash QR', NULL, '2025-11-09 06:06:57', 1, '2025-11-09 06:08:26', 'gcash', 50, 11250.00, 'VERIFIED', '2025-11-09 06:08:26'),
(57, 57, 0.00, 'GCash QR', NULL, '2025-11-09 08:57:08', NULL, NULL, 'gcash', 100, 27101.00, '', '2025-11-09 08:57:23'),
(60, 58, 0.00, 'GCash QR', NULL, '2025-11-09 09:01:44', NULL, NULL, 'gcash', 100, 27101.00, '', '2025-11-09 09:01:44'),
(61, 59, 0.00, 'GCash QR', NULL, '2025-11-09 09:06:20', NULL, NULL, 'gcash', 100, 344.00, '', '2025-11-09 09:06:20'),
(62, 60, 0.00, 'GCash QR', NULL, '2025-11-09 09:13:45', NULL, NULL, 'gcash', 100, 20222.00, '', '2025-11-09 09:13:45'),
(63, 61, 0.00, 'GCash QR', NULL, '2025-11-09 09:53:05', NULL, NULL, 'gcash', 50, 995.00, '', '2025-11-09 09:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `payment_installments`
--

CREATE TABLE `payment_installments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL COMMENT 'FK to orders table',
  `installment_number` int(11) NOT NULL COMMENT 'Sequence: 1=deposit, 2=2nd payment, etc.',
  `amount_due` decimal(12,2) NOT NULL COMMENT 'Amount to pay for this installment',
  `amount_paid` decimal(12,2) DEFAULT 0.00 COMMENT 'Actual amount paid',
  `due_date` date DEFAULT NULL COMMENT 'Optional payment deadline',
  `status` enum('PENDING','PAID','OVERDUE') DEFAULT 'PENDING',
  `payment_method` enum('gcash','bpi','cash') DEFAULT NULL,
  `reference_number` varchar(120) DEFAULT NULL COMMENT 'GCash/BPI reference',
  `screenshot_path` varchar(255) DEFAULT NULL COMMENT 'Payment proof path',
  `verified_by` int(11) DEFAULT NULL COMMENT 'Admin who verified',
  `verified_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL COMMENT 'Admin remarks',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_installments`
--

INSERT INTO `payment_installments` (`id`, `order_id`, `installment_number`, `amount_due`, `amount_paid`, `due_date`, `status`, `payment_method`, `reference_number`, `screenshot_path`, `verified_by`, `verified_at`, `notes`, `created_at`, `updated_at`) VALUES
(42, 47, 1, 8090.00, 8090.00, NULL, 'PAID', NULL, NULL, NULL, NULL, '2025-11-04 12:06:41', NULL, '2025-11-04 04:05:19', '2025-11-04 04:06:41'),
(43, 47, 2, 8090.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-04 04:05:19', '2025-11-04 04:05:19'),
(44, 48, 1, 4854.00, 4854.00, NULL, 'PAID', NULL, NULL, NULL, NULL, '2025-11-06 05:15:30', NULL, '2025-11-05 21:14:14', '2025-11-05 21:15:30'),
(45, 48, 2, 11326.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-05 21:14:14', '2025-11-05 21:14:14'),
(46, 49, 1, 7840.00, 7840.00, NULL, 'PAID', NULL, NULL, NULL, NULL, '2025-11-06 06:40:38', NULL, '2025-11-05 22:38:39', '2025-11-05 22:40:38'),
(47, 49, 2, 7840.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-05 22:38:39', '2025-11-05 22:38:39'),
(48, 50, 1, 8090.00, 8090.00, NULL, 'PAID', NULL, NULL, NULL, NULL, '2025-11-07 11:30:10', NULL, '2025-11-07 03:29:35', '2025-11-07 03:30:10'),
(49, 50, 2, 8090.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-07 03:29:35', '2025-11-07 03:29:35'),
(50, 51, 1, 7840.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-07 03:52:58', '2025-11-07 03:52:58'),
(51, 51, 2, 7840.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-07 03:52:58', '2025-11-07 03:52:58'),
(52, 52, 1, 7840.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-07 04:13:58', '2025-11-07 04:13:58'),
(53, 52, 2, 7840.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-07 04:13:58', '2025-11-07 04:13:58'),
(54, 53, 1, 7840.00, 7840.00, NULL, 'PAID', NULL, NULL, NULL, NULL, '2025-11-07 18:38:27', NULL, '2025-11-07 10:37:49', '2025-11-07 10:38:27'),
(55, 53, 2, 7840.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-07 10:37:49', '2025-11-07 10:37:49'),
(56, 54, 1, 4704.00, 4704.00, NULL, 'PAID', NULL, NULL, NULL, NULL, '2025-11-07 18:45:16', NULL, '2025-11-07 10:44:36', '2025-11-07 10:45:16'),
(57, 54, 2, 10976.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-07 10:44:36', '2025-11-07 10:44:36'),
(58, 55, 1, 16180.00, 16180.00, NULL, 'PAID', NULL, NULL, NULL, NULL, '2025-11-09 16:08:58', NULL, '2025-11-07 12:35:14', '2025-11-09 08:08:58'),
(59, 56, 1, 11250.00, 11250.00, NULL, 'PAID', NULL, NULL, NULL, NULL, '2025-11-09 14:08:26', NULL, '2025-11-09 06:06:57', '2025-11-09 06:08:26'),
(60, 56, 2, 11250.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 06:06:57', '2025-11-09 06:06:57'),
(61, 57, 1, 27101.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 08:57:08', '2025-11-09 08:57:08'),
(62, 58, 1, 27101.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 09:01:44', '2025-11-09 09:01:44'),
(63, 59, 1, 344.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 09:06:20', '2025-11-09 09:06:20'),
(64, 60, 1, 20222.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 09:13:45', '2025-11-09 09:13:45'),
(65, 61, 1, 995.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 09:53:05', '2025-11-09 09:53:05'),
(66, 61, 2, 995.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 09:53:05', '2025-11-09 09:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `payment_qr`
--

CREATE TABLE `payment_qr` (
  `id` int(11) NOT NULL,
  `method` enum('gcash','bpi') NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_qr`
--

INSERT INTO `payment_qr` (`id`, `method`, `image_path`, `is_active`) VALUES
(1, 'gcash', 'uploads/qrs/gcash_1761692286.jpg', 1),
(2, 'bpi', 'uploads/qrs/bpi_1761692321.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment_verifications`
--

CREATE TABLE `payment_verifications` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `method` enum('gcash','bpi') NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `reference_number` varchar(120) NOT NULL,
  `amount_reported` decimal(12,2) NOT NULL,
  `screenshot_path` varchar(255) DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `approved_by` int(11) DEFAULT NULL COMMENT 'Admin who approved the payment',
  `approved_at` datetime DEFAULT NULL COMMENT 'Timestamp when payment was approved',
  `rejected_by` int(11) DEFAULT NULL COMMENT 'Admin who rejected the payment',
  `rejected_at` datetime DEFAULT NULL COMMENT 'Timestamp when payment was rejected',
  `rejection_reason` text DEFAULT NULL COMMENT 'Reason for rejecting payment',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reject_reason` text DEFAULT NULL,
  `rejected_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_verifications`
--

INSERT INTO `payment_verifications` (`id`, `order_id`, `method`, `account_name`, `account_number`, `reference_number`, `amount_reported`, `screenshot_path`, `status`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `created_at`, `reject_reason`, `rejected_reason`) VALUES
(21, 47, 'gcash', 'teest', '2', '12121', 8090.00, 'uploads/payments/proof_47_1762229151.jpg', 'APPROVED', 1, '2025-11-04 12:06:41', NULL, NULL, NULL, '2025-11-04 04:05:51', NULL, NULL),
(22, 48, 'gcash', 'teest', '21212121', '121212121', 4854.00, 'uploads/payments/proof_48_1762377271.png', 'APPROVED', 1, '2025-11-06 05:15:30', NULL, NULL, NULL, '2025-11-05 21:14:31', NULL, NULL),
(23, 49, 'gcash', 'teest', '21212121', '3213231', 7840.00, 'uploads/payments/proof_49_1762382332.png', 'APPROVED', 1, '2025-11-06 06:40:38', NULL, NULL, NULL, '2025-11-05 22:38:52', NULL, NULL),
(24, 50, 'bpi', 'teest', '21212121', '3213231', 8090.00, 'uploads/payments/proof_50_1762486188.jpg', 'APPROVED', 1, '2025-11-07 11:30:10', NULL, NULL, NULL, '2025-11-07 03:29:48', NULL, NULL),
(25, 53, 'gcash', 'teest', '21212121', '3213231', 7840.00, 'uploads/payments/proof_53_1762511880.jpg', 'APPROVED', 1, '2025-11-07 18:38:27', NULL, NULL, NULL, '2025-11-07 10:38:00', NULL, NULL),
(26, 53, 'gcash', 'dsdadas', '212121', '321321312', 3920.00, 'uploads/payments/proof_53_1762512132.jpg', 'APPROVED', 1, '2025-11-07 18:42:40', NULL, NULL, NULL, '2025-11-07 10:42:12', NULL, NULL),
(27, 54, 'gcash', 'teest', '21212121', '3213231', 4704.00, 'uploads/payments/proof_54_1762512288.png', 'APPROVED', 1, '2025-11-07 18:45:16', NULL, NULL, NULL, '2025-11-07 10:44:48', NULL, NULL),
(28, 53, 'gcash', 'dsdadas', '212121', '213213134', 1960.00, 'uploads/payments/proof_53_1762515916.png', 'APPROVED', 1, '2025-11-07 19:45:48', NULL, NULL, NULL, '2025-11-07 11:45:16', NULL, NULL),
(29, 53, 'gcash', 'dsdadas', '212121', '122112132123', 1960.00, 'uploads/payments/proof_53_1762516000.png', 'APPROVED', 1, '2025-11-07 19:46:57', NULL, NULL, NULL, '2025-11-07 11:46:40', NULL, NULL),
(30, 55, 'gcash', 'teest', '21212121', '3213231', 16180.00, 'uploads/payments/proof_55_1762518955.jpg', 'APPROVED', 1, '2025-11-09 16:08:58', NULL, NULL, NULL, '2025-11-07 12:35:55', NULL, NULL),
(31, 56, 'gcash', 'teest', '243213', '321343', 11250.00, 'uploads/payments/proof_56_1762668438.jpg', 'APPROVED', 1, '2025-11-09 14:08:26', NULL, NULL, NULL, '2025-11-09 06:07:18', NULL, NULL),
(32, 61, 'gcash', 'teest', '243213', '321343', 1114.40, 'uploads/payments/proof_61_1762682007.jpg', 'PENDING', NULL, NULL, NULL, NULL, NULL, '2025-11-09 09:53:27', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_verifications_backup_20251026`
--

CREATE TABLE `payment_verifications_backup_20251026` (
  `id` int(11) NOT NULL DEFAULT 0,
  `order_id` int(11) NOT NULL,
  `method` enum('gcash','bpi') NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `reference_number` varchar(120) NOT NULL,
  `amount_reported` decimal(12,2) NOT NULL,
  `screenshot_path` varchar(255) DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_verifications_backup_20251026`
--

INSERT INTO `payment_verifications_backup_20251026` (`id`, `order_id`, `method`, `account_name`, `account_number`, `reference_number`, `amount_reported`, `screenshot_path`, `status`, `created_at`) VALUES
(1, 1, 'gcash', 'dascasdasd', '1231231231', 'd12231123', 123.00, 'uploads/payments/proof_1_1760842501.png', 'APPROVED', '2025-10-19 02:55:01'),
(2, 2, 'bpi', 'dascasdasd', '1231231231', 'd12231123', 123.00, 'uploads/payments/proof_2_1760842557.png', 'REJECTED', '2025-10-19 02:55:57'),
(3, 23, 'gcash', 'test1', 'test2', 'test123', 112.00, 'uploads/payments/proof_23_1760889566.jpg', 'APPROVED', '2025-10-19 15:59:26'),
(4, 24, 'gcash', 'teest', 'tset', 'testt', 612.00, 'uploads/payments/proof_24_1760891708.jpg', 'APPROVED', '2025-10-19 16:35:08'),
(5, 25, 'gcash', 'tset', 'tetset', 'teststs', 56.00, 'uploads/payments/proof_25_1760896011.png', 'APPROVED', '2025-10-19 17:46:51'),
(6, 28, 'gcash', 'sadas', 'dasdas', 'dasdas', 123.00, 'uploads/payments/proof_28_1761010719.png', 'PENDING', '2025-10-21 01:38:39'),
(7, 29, 'bpi', 'ds', 'das', 'da', 12313.00, 'uploads/payments/proof_29_1761015499.png', 'REJECTED', '2025-10-21 02:58:19'),
(8, 30, 'bpi', 'awit', '6969', '6969', 123.00, 'uploads/payments/proof_30_1761015751.jpeg', 'REJECTED', '2025-10-21 03:02:31'),
(9, 31, 'gcash', 'dascasdasd', '1231231231', 'd12231123', 123.00, 'uploads/payments/proof_31_1761015774.png', 'APPROVED', '2025-10-21 03:02:54'),
(10, 32, 'bpi', 'dascasdasd', '1231231231', '123', 123.00, 'uploads/payments/proof_32_1761015835.png', 'APPROVED', '2025-10-21 03:03:55'),
(11, 34, 'bpi', 'dsad', 'asdas', 'ad123123', 123.00, 'uploads/payments/proof_34_1761016158.png', 'APPROVED', '2025-10-21 03:09:18');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL COMMENT 'Primary product image (kept for backward compatibility)',
  `model_3d` varchar(255) DEFAULT NULL,
  `measurement_unit` enum('cm','mm','inch','meter') DEFAULT 'cm',
  `is_customizable` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `status` enum('draft','released') NOT NULL DEFAULT 'draft',
  `released_at` datetime DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `type`, `description`, `price`, `stock`, `image`, `model_3d`, `measurement_unit`, `is_customizable`, `created_at`, `created_by`, `status`, `released_at`, `is_archived`, `archived_at`) VALUES
(47, 'Storage Cabinet', 'Storage Cabinet', 'Elegant storage cabinet', 14000.00, 0, 'uploads/products/cab8_1762371842_398504c69835.jpg', 'test_cab_1762228945_7d9d194d5fba.glb', 'cm', 1, '2025-11-03 04:14:18', 1, 'released', '2025-11-06 03:44:11', 0, NULL),
(48, 'test cab', 'Office Cabinet', 'test', 1111.00, 0, 'uploads/products/cab2_1762604309_3dca021a969f.jpg', 'test_cab_1762604315_0d588adc087e.glb', 'cm', 1, '2025-11-08 12:19:01', 1, 'released', '2025-11-09 00:26:08', 0, NULL),
(49, 'test', 'Wardrobe', 'test', 111.00, 0, 'uploads/products/cab9_1762640352_9b06fb9799ad.jpg', 'cab_test1_1762638914_22c6f804b4bf.glb', 'cm', 1, '2025-11-08 16:26:39', 1, 'released', '2025-11-09 05:55:17', 0, NULL),
(50, 'Wardrobe Cabinet', 'Wardrobe', 'Wardrobe', 222.00, 0, 'uploads/products/cab1_1762664359_ce067d74e047.jpg', 'test_cab_1762664364_1dd20cd45178.glb', 'cm', 1, '2025-11-09 04:59:26', 1, 'released', '2025-11-09 13:00:08', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_colors`
--

CREATE TABLE `product_colors` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `custom_price` decimal(10,2) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_colors`
--

INSERT INTO `product_colors` (`id`, `product_id`, `color_id`, `custom_price`, `display_order`, `is_default`, `created_at`) VALUES
(250, 49, 9, NULL, 0, 0, '2025-11-09 03:21:28'),
(251, 49, 10, NULL, 1, 0, '2025-11-09 03:21:28'),
(254, 48, 9, NULL, 0, 0, '2025-11-09 03:30:27'),
(255, 48, 10, NULL, 1, 0, '2025-11-09 03:30:27'),
(274, 50, 9, NULL, 0, 0, '2025-11-09 08:53:46'),
(275, 50, 10, NULL, 1, 0, '2025-11-09 08:53:46');

-- --------------------------------------------------------

--
-- Table structure for table `product_handles`
--

CREATE TABLE `product_handles` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `handle_id` int(11) NOT NULL,
  `custom_price` decimal(10,2) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_handles`
--

INSERT INTO `product_handles` (`id`, `product_id`, `handle_id`, `custom_price`, `display_order`, `is_default`, `created_at`) VALUES
(49, 50, 7, NULL, 0, 0, '2025-11-09 08:53:46'),
(50, 50, 6, NULL, 1, 0, '2025-11-09 08:53:46');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_path`, `display_order`, `is_primary`, `created_at`) VALUES
(175, 48, 'uploads/products/cab2_1762604309_3dca021a969f.jpg', 0, 1, '2025-11-08 12:19:01'),
(176, 49, 'uploads/products/cab2_1762619191_a2b7d9439d7d.jpg', 0, 1, '2025-11-08 16:26:40'),
(177, 49, 'uploads/products/cab9_1762640352_9b06fb9799ad.jpg', 1, 0, '2025-11-08 22:19:14'),
(178, 49, 'uploads/products/cab10_1762640352_9a7c9e5ac350.jpg', 2, 0, '2025-11-08 22:19:14'),
(179, 49, 'uploads/products/cabinet-hero_1762640352_04d68fe25b0d.jpg', 3, 0, '2025-11-08 22:19:14'),
(180, 50, 'uploads/products/cab1_1762664359_ce067d74e047.jpg', 0, 1, '2025-11-09 04:59:26'),
(181, 50, 'uploads/products/cab2_1762664359_07e3db599216.jpg', 1, 0, '2025-11-09 04:59:26'),
(182, 50, 'uploads/products/cab3_1762664359_841ed3fcbe9f.jpg', 2, 0, '2025-11-09 04:59:26');

--
-- Triggers `product_images`
--
DELIMITER $$
CREATE TRIGGER `after_product_image_delete` AFTER DELETE ON `product_images` FOR EACH ROW BEGIN
    UPDATE `shared_images` 
    SET `reference_count` = GREATEST(0, `reference_count` - 1),
        `last_used` = NOW()
    WHERE `image_path` = OLD.image_path;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_product_image_insert` AFTER INSERT ON `product_images` FOR EACH ROW BEGIN
    INSERT INTO `shared_images` (`image_path`, `reference_count`) 
    VALUES (NEW.image_path, 1)
    ON DUPLICATE KEY UPDATE 
        `reference_count` = `reference_count` + 1,
        `last_used` = NOW();
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_material_map`
--

CREATE TABLE `product_material_map` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `map_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`map_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_size_config`
--

CREATE TABLE `product_size_config` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `dimension_type` enum('width','height','depth') NOT NULL,
  `min_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_value` decimal(10,2) NOT NULL DEFAULT 300.00,
  `default_value` decimal(10,2) NOT NULL DEFAULT 100.00,
  `step_value` decimal(10,2) NOT NULL DEFAULT 1.00,
  `price_per_unit` decimal(10,2) DEFAULT 0.00,
  `measurement_unit` varchar(20) DEFAULT 'cm',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price_block_cm` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_per_block` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_size_config`
--

INSERT INTO `product_size_config` (`id`, `product_id`, `dimension_type`, `min_value`, `max_value`, `default_value`, `step_value`, `price_per_unit`, `measurement_unit`, `created_at`, `price_block_cm`, `price_per_block`) VALUES
(490, 47, 'width', 200.00, 300.00, 200.00, 1.00, 200.00, 'cm', '2025-11-06 12:25:53', 0.00, 0.00),
(491, 47, 'height', 200.00, 300.00, 200.00, 1.00, 0.00, 'cm', '2025-11-06 12:25:53', 10.00, 200.00),
(492, 47, 'depth', 50.00, 180.00, 50.00, 1.00, 100.00, 'cm', '2025-11-06 12:25:53', 0.00, 0.00),
(574, 49, 'width', 200.00, 300.00, 200.00, 1.00, 200.00, 'cm', '2025-11-09 03:21:27', 0.00, 0.00),
(575, 49, 'height', 200.00, 300.00, 200.00, 1.00, 0.00, 'cm', '2025-11-09 03:21:27', 0.00, 0.00),
(576, 49, 'depth', 50.00, 180.00, 50.00, 1.00, 100.00, 'cm', '2025-11-09 03:21:27', 0.00, 0.00),
(580, 48, 'width', 200.00, 300.00, 200.00, 1.00, 200.00, 'cm', '2025-11-09 03:30:27', 0.00, 0.00),
(581, 48, 'height', 200.00, 300.00, 200.00, 1.00, 0.00, 'cm', '2025-11-09 03:30:27', 0.00, 0.00),
(582, 48, 'depth', 50.00, 180.00, 50.00, 1.00, 100.00, 'cm', '2025-11-09 03:30:27', 0.00, 0.00),
(616, 50, 'width', 200.00, 300.00, 200.00, 1.00, 200.00, 'cm', '2025-11-09 08:53:45', 0.00, 0.00),
(617, 50, 'height', 250.00, 300.00, 250.00, 1.00, 100.00, 'cm', '2025-11-09 08:53:45', 0.00, 0.00),
(618, 50, 'depth', 150.00, 250.00, 150.00, 1.00, 0.00, 'cm', '2025-11-09 08:53:45', 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `product_size_config_backup_20251026`
--

CREATE TABLE `product_size_config_backup_20251026` (
  `id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL,
  `dimension_type` enum('width','height','depth') NOT NULL,
  `min_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_value` decimal(10,2) NOT NULL DEFAULT 300.00,
  `default_value` decimal(10,2) NOT NULL DEFAULT 100.00,
  `price_per_unit` decimal(10,2) DEFAULT 0.00,
  `measurement_unit` varchar(20) DEFAULT 'cm',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price_block_cm` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_per_block` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_size_config_backup_20251026`
--

INSERT INTO `product_size_config_backup_20251026` (`id`, `product_id`, `dimension_type`, `min_value`, `max_value`, `default_value`, `price_per_unit`, `measurement_unit`, `created_at`, `price_block_cm`, `price_per_block`) VALUES
(1, 12, 'width', 0.00, 300.00, 100.00, 0.00, 'cm', '2025-10-15 11:27:37', 0.00, 0.00),
(2, 12, 'height', 0.00, 300.00, 100.00, 0.00, 'cm', '2025-10-15 11:27:37', 0.00, 0.00),
(3, 12, 'depth', 0.00, 300.00, 100.00, 0.00, 'cm', '2025-10-15 11:27:37', 0.00, 0.00),
(4, 13, 'width', 0.00, 300.00, 100.00, 0.00, 'cm', '2025-10-15 12:28:09', 0.00, 0.00),
(5, 13, 'height', 0.00, 300.00, 100.00, 0.00, 'cm', '2025-10-15 12:28:10', 0.00, 0.00),
(6, 13, 'depth', 0.00, 300.00, 100.00, 0.00, 'cm', '2025-10-15 12:28:10', 0.00, 0.00),
(76, 14, 'width', 50.00, 200.00, 100.00, 100.00, 'cm', '2025-10-24 19:25:57', 0.00, 0.00),
(77, 14, 'height', 80.00, 280.00, 100.00, 200.00, 'cm', '2025-10-24 19:25:58', 0.00, 0.00),
(78, 14, 'depth', 50.00, 250.00, 100.00, 550.00, 'cm', '2025-10-24 19:25:58', 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `product_textures`
--

CREATE TABLE `product_textures` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `texture_id` int(11) NOT NULL,
  `custom_price` decimal(10,2) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_textures`
--

INSERT INTO `product_textures` (`id`, `product_id`, `texture_id`, `custom_price`, `display_order`, `is_default`, `created_at`) VALUES
(194, 49, 11, NULL, 0, 0, '2025-11-09 03:21:28'),
(195, 49, 12, NULL, 1, 0, '2025-11-09 03:21:28'),
(196, 49, 10, NULL, 2, 0, '2025-11-09 03:21:28'),
(199, 48, 11, NULL, 0, 0, '2025-11-09 03:30:27'),
(200, 48, 10, NULL, 1, 0, '2025-11-09 03:30:27'),
(228, 50, 11, NULL, 0, 0, '2025-11-09 08:53:46'),
(229, 50, 12, NULL, 1, 0, '2025-11-09 08:53:46'),
(230, 50, 10, NULL, 2, 0, '2025-11-09 08:53:46');

-- --------------------------------------------------------

--
-- Table structure for table `product_texture_parts`
--

CREATE TABLE `product_texture_parts` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `texture_id` int(11) NOT NULL,
  `part_key` varchar(32) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rt_chat_messages`
--

CREATE TABLE `rt_chat_messages` (
  `id` bigint(20) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `sender_type` enum('customer','admin','bot') NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rt_chat_messages`
--

INSERT INTO `rt_chat_messages` (`id`, `thread_id`, `sender_type`, `sender_id`, `body`, `created_at`) VALUES
(140, 8, 'customer', 41, 'hi', '2025-11-04 13:06:11'),
(141, 8, 'admin', 1, 'he', '2025-11-04 14:15:39');

-- --------------------------------------------------------

--
-- Table structure for table `rt_chat_threads`
--

CREATE TABLE `rt_chat_threads` (
  `id` int(11) NOT NULL,
  `thread_code` varchar(32) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `customer_email` varchar(160) DEFAULT NULL,
  `customer_phone` varchar(64) DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `last_message_at` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_last_read` datetime DEFAULT NULL,
  `customer_cleared_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rt_chat_threads`
--

INSERT INTO `rt_chat_threads` (`id`, `thread_code`, `customer_id`, `customer_name`, `customer_email`, `customer_phone`, `status`, `last_message_at`, `created_at`, `admin_last_read`, `customer_cleared_at`) VALUES
(8, '658BF52CFCD5E0FDDFEFCBCEB72C893B', 41, 'Moen Secapuri', NULL, NULL, 'open', '2025-11-04 22:15:39', '2025-11-04 13:06:07', '2025-11-22 03:20:16', NULL),
(9, 'E35EC0AF3A728AEDF617B1BE9C46C5B6', 40, 'Capstonk Capstoink', NULL, NULL, 'open', '2025-11-06 19:51:20', '2025-11-06 11:51:20', '2025-11-22 03:20:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rt_cms_pages`
--

CREATE TABLE `rt_cms_pages` (
  `id` int(11) NOT NULL,
  `page_key` varchar(50) NOT NULL,
  `page_name` varchar(100) NOT NULL,
  `content_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`content_data`)),
  `status` enum('draft','published') DEFAULT 'draft',
  `version` int(11) DEFAULT 1,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rt_cms_pages`
--

INSERT INTO `rt_cms_pages` (`id`, `page_key`, `page_name`, `content_data`, `status`, `version`, `updated_by`, `created_at`, `updated_at`) VALUES
(203, 'terms', 'Terms & Conditions', '{\"content\":\"<h1>Terms &amp; Conditions</h1><p><em>Effective Date: January 2024</em></p><h2>1. Acceptance of Terms</h2><p>By accessing and using the RADS Tooling website and services, you accept and agree to be bound by these Terms &amp; Conditions. If you do not agree to these terms, please do not use our services.</p><p>These terms apply to all visitors, users, customers, and others who access or use our services, including but not limited to browsing our website, placing orders, or engaging with our custom tooling services.</p><h2>2. Accounts and Registration</h2><h3>Account Creation</h3><p>To access certain features of our services, you may be required to create an account. When creating an account, you must:</p><ul><li>Provide accurate, current, and complete information</li><li>Maintain and promptly update your account information</li><li>Maintain the security of your password and account</li><li>Notify us immediately of any unauthorized use of your account</li></ul><h3>Account Responsibilities</h3><p>You are responsible for all activities that occur under your account. We reserve the right to suspend or terminate accounts that violate these terms.</p><h2>3. Products and Services</h2><h3>Product Descriptions</h3><p>We make every effort to provide accurate product descriptions, specifications, and images. However:</p><ul><li>Product images are for illustrative purposes and may differ slightly from actual products</li><li>Colors may appear differently depending on your display settings</li><li>We reserve the right to correct any errors or inaccuracies in product information</li></ul><h3>Custom Orders</h3><p>For custom cabinet orders:</p><ul><li>A 30% down payment is required before production begins</li><li>Final measurements and specifications must be approved before manufacturing</li><li>Production timeline will be provided upon order confirmation</li><li>Custom orders cannot be cancelled once production has started</li></ul><h2>4. Pricing and Payment</h2><h3>Pricing</h3><ul><li>All prices are listed in Philippine Pesos (PHP) unless otherwise stated</li><li>Prices are subject to change without notice</li><li>We reserve the right to correct pricing errors</li><li>Shipping and handling fees are additional unless stated otherwise</li></ul><h3>Payment Terms</h3><ul><li>Payment is due at the time of order placement unless otherwise agreed</li><li>We accept major credit cards, debit cards, and bank transfers</li><li>For large orders, payment plans may be available upon approval</li></ul><h2>5. Cancellation Policy</h2><h3>Standard Products</h3><ul><li>Orders may be cancelled within 24 hours of placement for a full refund</li><li>After 24 hours, cancellation fees may apply</li><li>Orders that have been shipped cannot be cancelled</li></ul><h3>Custom Products</h3><ul><li>Custom orders can be cancelled before production begins with a 10% restocking fee</li><li>Once production has started, custom orders cannot be cancelled</li><li>Down payments for cancelled custom orders are non-refundable if production has begun</li></ul><h2>6. Shipping and Delivery</h2><ul><li>Delivery timeframes are estimates and not guaranteed</li><li>Shipping costs are calculated based on weight, size, and destination</li><li>Risk of loss passes to you upon delivery to the carrier</li><li>You must inspect deliveries upon receipt and report any damage within 48 hours</li></ul><h2>7. Returns and Refunds</h2><h3>Return Policy</h3><ul><li>Standard products may be returned within 14 days of delivery</li><li>Products must be unused, in original packaging, and in resalable condition</li><li>Custom-made products are non-returnable</li><li>Customers are responsible for return shipping costs unless the product is defective</li></ul><h3>Refund Processing</h3><ul><li>Refunds will be processed within 7-14 business days of receiving the returned item</li><li>Refunds will be issued to the original payment method</li><li>Shipping fees are non-refundable</li></ul><h2>8. Warranties and Disclaimers</h2><h3>Product Warranty</h3><p>We warrant that our products are free from defects in materials and workmanship for a period of 1 year from the date of delivery. This warranty does not cover:</p><ul><li>Normal wear and tear</li><li>Damage caused by misuse or improper installation</li><li>Modifications or repairs by unauthorized parties</li><li>Damage from accidents or natural disasters</li></ul><h3>Disclaimer</h3><p>EXCEPT AS EXPRESSLY STATED, OUR SERVICES AND PRODUCTS ARE PROVIDED \\\"AS IS\\\" WITHOUT WARRANTY OF ANY KIND.</p><h2>9. Limitation of Liability</h2><p>To the maximum extent permitted by law, RADS Tooling shall not be liable for:</p><ul><li>Indirect, incidental, or consequential damages</li><li>Loss of profits, revenue, or data</li><li>Business interruption</li></ul><p>Our total liability shall not exceed the amount paid by you for the product or service in question.</p><h2>10. Intellectual Property</h2><p>All content on our website, including text, graphics, logos, images, and software, is the property of RADS Tooling and protected by intellectual property laws. You may not:</p><ul><li>Reproduce, distribute, or modify our content without permission</li><li>Use our trademarks or logos without authorization</li><li>Reverse engineer or decompile our software</li></ul><h2>11. Privacy</h2><p>Your use of our services is also governed by our Privacy Policy. Please review our Privacy Policy to understand our data practices.</p><h2>12. Changes to Terms</h2><p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. Your continued use of our services constitutes acceptance of the modified terms.</p><h2>13. Governing Law</h2><p>These terms are governed by the laws of the Republic of the Philippines. Any disputes shall be subject to the exclusive jurisdiction of the courts of Dasmariñas, Cavite.</p><h2>14. Contact Information</h2><p>For questions about these Terms &amp; Conditions, please contact us:</p><ul><li><strong>Company:</strong>&nbsp;RADS TOOLING INC.</li><li><strong>Address:</strong>&nbsp;Green Breeze, Piela, Dasmariñas, Cavite</li><li><strong>Phone:</strong>&nbsp;+63 976 228 4270</li><li><strong>Email:</strong>&nbsp;radstooling@gmail.com\\n</li></ul><p><br></p>\"}', 'draft', 6, 'System Owner', '2025-10-15 03:54:45', '2025-10-14 19:54:45'),
(204, 'terms', 'Terms & Conditions', '{\"content\":\"<h1>Terms &amp; Conditions</h1><p><em>Effective Date: January 2024</em></p><h2>1. Acceptance of Terms</h2><p>By accessing and using the RADS Tooling website and services, you accept and agree to be bound by these Terms &amp; Conditions. If you do not agree to these terms, please do not use our services.</p><p>These terms apply to all visitors, users, customers, and others who access or use our services, including but not limited to browsing our website, placing orders, or engaging with our custom tooling services.</p><h2>2. Accounts and Registration</h2><h3>Account Creation</h3><p>To access certain features of our services, you may be required to create an account. When creating an account, you must:</p><ul><li>Provide accurate, current, and complete information</li><li>Maintain and promptly update your account information</li><li>Maintain the security of your password and account</li><li>Notify us immediately of any unauthorized use of your account</li></ul><h3>Account Responsibilities</h3><p>You are responsible for all activities that occur under your account. We reserve the right to suspend or terminate accounts that violate these terms.</p><h2>3. Products and Services</h2><h3>Product Descriptions</h3><p>We make every effort to provide accurate product descriptions, specifications, and images. However:</p><ul><li>Product images are for illustrative purposes and may differ slightly from actual products</li><li>Colors may appear differently depending on your display settings</li><li>We reserve the right to correct any errors or inaccuracies in product information</li></ul><h3>Custom Orders</h3><p>For custom cabinet orders:</p><ul><li>A 30% down payment is required before production begins</li><li>Final measurements and specifications must be approved before manufacturing</li><li>Production timeline will be provided upon order confirmation</li><li>Custom orders cannot be cancelled once production has started</li></ul><h2>4. Pricing and Payment</h2><h3>Pricing</h3><ul><li>All prices are listed in Philippine Pesos (PHP) unless otherwise stated</li><li>Prices are subject to change without notice</li><li>We reserve the right to correct pricing errors</li><li>Shipping and handling fees are additional unless stated otherwise</li></ul><h3>Payment Terms</h3><ul><li>Payment is due at the time of order placement unless otherwise agreed</li><li>We accept major credit cards, debit cards, and bank transfers</li><li>For large orders, payment plans may be available upon approval</li></ul><h2>5. Cancellation Policy</h2><h3>Standard Products</h3><ul><li>Orders may be cancelled within 24 hours of placement for a full refund</li><li>After 24 hours, cancellation fees may apply</li><li>Orders that have been shipped cannot be cancelled</li></ul><h3>Custom Products</h3><ul><li>Custom orders can be cancelled before production begins with a 10% restocking fee</li><li>Once production has started, custom orders cannot be cancelled</li><li>Down payments for cancelled custom orders are non-refundable if production has begun</li></ul><h2>6. Shipping and Delivery</h2><ul><li>Delivery timeframes are estimates and not guaranteed</li><li>Shipping costs are calculated based on weight, size, and destination</li><li>Risk of loss passes to you upon delivery to the carrier</li><li>You must inspect deliveries upon receipt and report any damage within 48 hours</li></ul><h2>7. Returns and Refunds</h2><h3>Return Policy</h3><ul><li>Standard products may be returned within 14 days of delivery</li><li>Products must be unused, in original packaging, and in resalable condition</li><li>Custom-made products are non-returnable</li><li>Customers are responsible for return shipping costs unless the product is defective</li></ul><h3>Refund Processing</h3><ul><li>Refunds will be processed within 7-14 business days of receiving the returned item</li><li>Refunds will be issued to the original payment method</li><li>Shipping fees are non-refundable</li></ul><h2>8. Warranties and Disclaimers</h2><h3>Product Warranty</h3><p>We warrant that our products are free from defects in materials and workmanship for a period of 1 year from the date of delivery. This warranty does not cover:</p><ul><li>Normal wear and tear</li><li>Damage caused by misuse or improper installation</li><li>Modifications or repairs by unauthorized parties</li><li>Damage from accidents or natural disasters</li></ul><h3>Disclaimer</h3><p>EXCEPT AS EXPRESSLY STATED, OUR SERVICES AND PRODUCTS ARE PROVIDED \\\"AS IS\\\" WITHOUT WARRANTY OF ANY KIND.</p><h2>9. Limitation of Liability</h2><p>To the maximum extent permitted by law, RADS Tooling shall not be liable for:</p><ul><li>Indirect, incidental, or consequential damages</li><li>Loss of profits, revenue, or data</li><li>Business interruption</li></ul><p>Our total liability shall not exceed the amount paid by you for the product or service in question.</p><h2>10. Intellectual Property</h2><p>All content on our website, including text, graphics, logos, images, and software, is the property of RADS Tooling and protected by intellectual property laws. You may not:</p><ul><li>Reproduce, distribute, or modify our content without permission</li><li>Use our trademarks or logos without authorization</li><li>Reverse engineer or decompile our software</li></ul><h2>11. Privacy</h2><p>Your use of our services is also governed by our Privacy Policy. Please review our Privacy Policy to understand our data practices.</p><h2>12. Changes to Terms</h2><p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. Your continued use of our services constitutes acceptance of the modified terms.</p><h2>13. Governing Law</h2><p>These terms are governed by the laws of the Republic of the Philippines. Any disputes shall be subject to the exclusive jurisdiction of the courts of Dasmariñas, Cavite.</p><h2>14. Contact Information</h2><p>For questions about these Terms &amp; Conditions, please contact us:</p><ul><li><strong>Company:</strong>&nbsp;RADS TOOLING INC.</li><li><strong>Address:</strong>&nbsp;Green Breeze, Piela, Dasmariñas, Cavite</li><li><strong>Phone:</strong>&nbsp;+63 976 228 4270</li><li><strong>Email:</strong>&nbsp;radstooling@gmail.com\\n</li></ul><p><br></p>\"}', 'published', 6, 'System Owner', '2025-10-15 03:54:46', '2025-10-14 19:54:46'),
(224, 'about', 'About Us', '{\"about_hero_image\":\"/assets/images/store.jpg\",\"about_headline\":\"About Rads Tooling\",\"about_subheadline\":\"<p>Your trusted partner in precision tooling and industrial solutions</p>\",\"about_mission\":\"<p>To provide high-quality custom cabinets and tooling solutions that exceed customer expectations through superior craftsmanship, innovative design, and exceptional service.</p>\",\"about_vision\":\"<p>To be the leading cabinet manufacturer in Cavite, recognized for quality, reliability, and customer satisfaction.</p>\",\"about_story\":\"<p>Established in 2007, RADS Tooling has been serving customers for over 17 years. We started as a small workshop and have grown into a trusted name in custom cabinet manufacturing. Our commitment to quality and customer satisfaction has made us the preferred choice for homeowners and businesses alike.</p><p>Every cabinet we create is handcrafted by skilled artisans using premium materials and modern techniques. We combine traditional craftsmanship with innovative design to deliver products that stand the test of time.</p>\",\"about_address\":\"Green Breeze, Piela, Dasmariñas, Cavite\",\"about_phone\":\"+63 976 228 4270\",\"about_email\":\"radstooling@gmail.com\",\"about_hours_weekday\":\"Mon-Sat: 8:00 AM - 5:00 PM\",\"about_hours_sunday\":\"Sunday: Closed\",\"about_hero_path\":\"/assets/images/store.jpg\"}', 'published', 9, 'System Owner', '2025-10-15 21:38:09', '2025-10-15 13:38:09'),
(228, 'home_customer', 'Customer Homepage', '{\"welcome_message\":\"<h1>Welcome back, <span style=\\\"color: #2f5b88;\\\">{{customer_name}}</span>!</h1>\",\"intro_text\":\"<p>Explore our latest cabinet designs and continue your projectss</p>\",\"footer_email\":\"RadsTooling@gmail.com\",\"footer_phone\":\"+63 976 228 4270\",\"footer_address\":\"Green Breeze, Piela, Dasmariñas, Cavite\",\"footer_hours\":\"Mon-Sat: 8:00 AM - 5:00 PM\",\"welcome\":\"<h1>Welcome backs, <span style=\\\"color: #2f5b88;\\\">{{customer_name}}</span>!</h1>\",\"intro\":\"<p>Explore our latest cabinet designs and continue your projects</p>\",\"hero_image\":\"/assets/images/cabinet-hero.jpg\",\"cta_primary_text\":\"Start Designing\",\"cta_secondary_text\":\"Browse Products\",\"footer_description\":\"Premium custom cabinet manufacturer serving clients since 2007. Quality craftsmanship, affordable prices, and exceptional service.\",\"customer_hero_image\":\"/uploads/cms/customer/hero-cabinet_1760909601_d391178d23bb8eef.glb\"}', 'published', 7, 'System Owner', '2025-10-20 05:33:39', '2025-10-19 21:33:39'),
(235, 'privacy', 'Privacy Policy', '{\"content\":\"<h1>Privacy Policy</h1><p><em>Last updated: January 2024</em></p><p><br></p><h2>1. Introduction</h2><p>Welcome to RADS Tooling. We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains what information we collect, how we use it, and what rights you have in relation to it.</p><h2>2. Information We Collect</h2><h3>Personal Information</h3><p>We collect personal information that you voluntarily provide to us when you:</p><ul><li>Register for an account on our website</li><li>Place an order for our products</li><li>Request custom tooling solutions</li><li>Subscribe to our newsletter</li><li>Contact us for support or inquiries</li></ul><p>This information may include:</p><ul><li>Name and contact information (email, phone number, address)</li><li>Account credentials (username and password)</li><li>Payment information (credit card details, billing address)</li><li>Order history and preferences</li><li>Custom design specifications and requirements</li></ul><h3>Automatically Collected Information</h3><p>When you visit our website, we automatically collect certain information about your device, including:</p><ul><li>IP address and location data</li><li>Browser type and version</li><li>Device type and operating system</li><li>Pages visited and time spent on our site</li><li>Referring website or source</li></ul><h2>3. How We Use Your Information</h2><p>We use the information we collect to:</p><ul><li>Process and fulfill your orders</li><li>Provide customer support and respond to inquiries</li><li>Send order confirmations and shipping updates</li><li>Create and manage your account</li><li>Customize your experience on our website</li><li>Send marketing communications (with your consent)</li><li>Improve our products and services</li><li>Prevent fraud and enhance security</li><li>Comply with legal obligations</li></ul><h2>4. Cookies and Tracking Technologies</h2><p>We use cookies and similar tracking technologies to:</p><ul><li>Keep you logged in to your account</li><li>Remember your preferences and settings</li><li>Analyze website traffic and usage patterns</li><li>Improve website functionality and user experience</li><li>Deliver targeted advertisements (if applicable)</li></ul><p>You can control cookies through your browser settings. However, disabling cookies may limit some features of our website.</p><h2>5. Data Sharing and Disclosure</h2><p>We do not sell, trade, or rent your personal information to third parties. We may share your information with:</p><ul><li><strong>Service Providers:</strong>&nbsp;Third-party vendors who help us operate our business (e.g., payment processors, shipping companies, email service providers)</li><li><strong>Business Partners:</strong>&nbsp;Trusted partners for custom tooling projects (with your consent)</li><li><strong>Legal Requirements:</strong>&nbsp;When required by law or to protect our rights and safety</li><li><strong>Business Transfers:</strong>&nbsp;In connection with a merger, acquisition, or sale of assets</li></ul><h2>6. Data Security</h2><p>We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p><ul><li>Encryption of sensitive data in transit and at rest</li><li>Regular security assessments and updates</li><li>Limited access to personal information on a need-to-know basis</li><li>Employee training on data protection and privacy</li></ul><p>However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.</p><h2>7. Your Rights and Choices</h2><p>You have the right to:</p><ul><li><strong>Access:</strong>&nbsp;Request a copy of your personal information</li><li><strong>Correction:</strong>&nbsp;Update or correct inaccurate information</li><li><strong>Deletion:</strong>&nbsp;Request deletion of your personal information</li><li><strong>Opt-out:</strong>&nbsp;Unsubscribe from marketing communications</li><li><strong>Data Portability:</strong>&nbsp;Request your data in a portable format</li><li><strong>Withdraw Consent:</strong>&nbsp;Withdraw consent for data processing where applicable</li></ul><p>To exercise these rights, please contact us using the information provided below.</p><h2>8. Children\'s Privacy</h2><p>Our services are not intended for individuals under the age of 18. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.</p><h2>9. International Data Transfers</h2><p>Your information may be transferred to and processed in countries other than your country of residence. These countries may have different data protection laws. We ensure appropriate safeguards are in place to protect your information in accordance with this Privacy Policy.</p><h2>10. Changes to This Policy</h2><p>We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements. We will notify you of any material changes by posting the new Privacy Policy on this page and updating the \\\"Last updated\\\" date.</p><h2>11. Contact Us</h2><p>If you have questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us at:</p><ul><li><strong>Email:</strong>&nbsp;RadsTooling@gmail.com</li><li><strong>Phone:</strong>&nbsp;+63 (976) 228-4270</li><li><strong>Address:</strong>&nbsp;Green Breeze, Piela, Dasmariñas, Cavite, Philippines</li></ul><h2>12. Consent</h2><p>By using our website and services, you consent to the collection and use of your information as described in this Privacy Policy.</p>\"}', 'published', 13, 'System Owner', '2025-10-20 08:32:03', '2025-10-20 00:32:03'),
(250, 'about', 'About Us', '{\"about_hero_image\":\"/assets/images/store.jpg\",\"about_headline\":\"About Rads Tooling\",\"about_subheadline\":\"<p>Your trusted partner in precision tooling and industrial solutions</p>\",\"about_mission\":\"<p>To provide high-quality custom cabinets and tooling solutions that exceed customer expectations through superior craftsmanship, innovative design, and exceptional service.</p>\",\"about_vision\":\"<p>To be the leading cabinet manufacturer in Cavite, recognized for quality, reliability, and customer satisfaction.</p>\",\"about_story\":\"<p>Established in 2007, RADS Tooling has been serving customers for over 17 years. We started as a small workshop and have grown into a trusted name in custom cabinet manufacturing. Our commitment to quality and customer satisfaction has made us the preferred choice for homeowners and businesses alike.</p><p>Every cabinet we create is handcrafted by skilled artisans using premium materials and modern techniques. We combine traditional craftsmanship with innovative design to deliver products that stand the test of time.</p>\",\"about_address\":\"Green Breeze, Piela, Dasmariñas, Cavite\",\"about_phone\":\"+63 976 228 4270\",\"about_email\":\"radstooling@gmail.com\",\"about_hours_weekday\":\"Mon-Sat: 8:00 AM - 5:00 PM\",\"about_hours_sunday\":\"Sunday: Closed\",\"about_hero_path\":\"/uploads/general/Game1_1762031745_86b132605e2f.jpg\"}', 'draft', 10, 'System Owner44', '2025-11-02 05:15:46', '2025-11-01 21:15:46'),
(277, 'privacy', 'Privacy Policy', '{\"content\":\"<h1>Privacy Policy</h1><p><em>Last updated: January 2024</em></p><p><br></p><h2>1. Introduction</h2><p>Welcome to RADS Tooling. We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains what information we collect, how we use it, and what rights you have in relation to it.</p><h2>2. Information We Collect</h2><h3>Personal Information</h3><p>We collect personal information that you voluntarily provide to us when you:</p><ul><li>Register for an account on our website</li><li>Place an order for our products</li><li>Request custom tooling solutions</li><li>Subscribe to our newsletter</li><li>Contact us for support or inquiries</li></ul><p>This information may include:</p><ul><li>Name and contact information (email, phone number, address)</li><li>Account credentials (username and password)</li><li>Payment information (credit card details, billing address)</li><li>Order history and preferences</li><li>Custom design specifications and requirements</li></ul><h3>Automatically Collected Information</h3><p>When you visit our website, we automatically collect certain information about your device, including:</p><ul><li>IP address and location data</li><li>Browser type and version</li><li>Device type and operating system</li><li>Pages visited and time spent on our site</li><li>Referring website or source</li></ul><h2>3. How We Use Your Information</h2><p>We use the information we collect to:</p><ul><li>Process and fulfill your orders</li><li>Provide customer support and respond to inquiries</li><li>Send order confirmations and shipping updates</li><li>Create and manage your account</li><li>Customize your experience on our website</li><li>Send marketing communications (with your consent)</li><li>Improve our products and services</li><li>Prevent fraud and enhance security</li><li>Comply with legal obligations</li></ul><h2>4. Cookies and Tracking Technologies</h2><p>We use cookies and similar tracking technologies to:</p><ul><li>Keep you logged in to your account</li><li>Remember your preferences and settings</li><li>Analyze website traffic and usage patterns</li><li>Improve website functionality and user experience</li><li>Deliver targeted advertisements (if applicable)</li></ul><p>You can control cookies through your browser settings. However, disabling cookies may limit some features of our website.</p><h2>5. Data Sharing and Disclosure</h2><p>We do not sell, trade, or rent your personal information to third parties. We may share your information with:</p><ul><li><strong>Service Providers:</strong>&nbsp;Third-party vendors who help us operate our business (e.g., payment processors, shipping companies, email service providers)</li><li><strong>Business Partners:</strong>&nbsp;Trusted partners for custom tooling projects (with your consent)</li><li><strong>Legal Requirements:</strong>&nbsp;When required by law or to protect our rights and safety</li><li><strong>Business Transfers:</strong>&nbsp;In connection with a merger, acquisition, or sale of assets</li></ul><h2>6. Data Security</h2><p>We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p><ul><li>Encryption of sensitive data in transit and at rest</li><li>Regular security assessments and updates</li><li>Limited access to personal information on a need-to-know basis</li><li>Employee training on data protection and privacy</li></ul><p>However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.</p><h2>7. Your Rights and Choices</h2><p>You have the right to:</p><ul><li><strong>Access:</strong>&nbsp;Request a copy of your personal information</li><li><strong>Correction:</strong>&nbsp;Update or correct inaccurate information</li><li><strong>Deletion:</strong>&nbsp;Request deletion of your personal information</li><li><strong>Opt-out:</strong>&nbsp;Unsubscribe from marketing communications</li><li><strong>Data Portability:</strong>&nbsp;Request your data in a portable format</li><li><strong>Withdraw Consent:</strong>&nbsp;Withdraw consent for data processing where applicable</li></ul><p>To exercise these rights, please contact us using the information provided below.</p><h2>8. Children\'s Privacy</h2><p>Our services are not intended for individuals under the age of 18. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.</p><h2>9. International Data Transfers</h2><p>Your information may be transferred to and processed in countries other than your country of residence. These countries may have different data protection laws. We ensure appropriate safeguards are in place to protect your information in accordance with this Privacy Policy.</p><h2>10. Changes to This Policy</h2><p>We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements. We will notify you of any material changes by posting the new Privacy Policy on this page and updating the \\\"Last updated\\\" date.</p><h2>11. Contact Us</h2><p>If you have questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us at:</p><ul><li><strong>Email:</strong>&nbsp;RadsTooling@gmail.com</li><li><strong>Phone:</strong>&nbsp;+63 (976) 228-4270</li><li><strong>Address:</strong>&nbsp;Green Breeze, Piela, Dasmariñas, Cavite, Philippines</li></ul><h2>12. Consent</h2><p>By using our website and services, you consent to the collection and use of your information as described in this Privacy Policy.</p>\"}', 'draft', 14, 'System Owner44', '2025-11-08 01:58:31', '2025-11-07 17:58:31'),
(283, 'logo_settings', 'Logo Settings', '{\"logo_type\":\"text\",\"logo_text\":\"RADS TOOLING\",\"logo_image\":\"\"}', 'published', 1, 'System', '2025-11-26 09:36:48', '2025-11-26 01:36:48'),
(284, 'footer_settings', 'Footer Settings', '{\"footer_company_name\":\"About RADS TOOLING\",\"footer_description\":\"Premium custom cabinet manufacturer serving clients since 2007. Quality craftsmanship, affordable prices, and exceptional service.\",\"footer_email\":\"RadsTooling@gmail.com\",\"footer_phone\":\"+63 976 228 4270\",\"footer_address\":\"Green Breeze, Piela, Dasmariñas, Cavite\",\"footer_hours\":\"Mon-Sat: 8:00 AM - 5:00 PM\",\"footer_facebook\":\"\",\"footer_copyright\":\"© 2025 RADS TOOLING INC. All rights reserved.\"}', 'published', 1, 'System', '2025-11-26 09:37:02', '2025-11-26 01:37:02'),
(288, 'home_public', 'Public Homepage', '{\"hero_headline\":\"<h1><span style=\\\"color: rgb(47, 91, 136);\\\">C</span>ustomize Your Dream Cabinets</h1>\",\"hero_subtitle\":\"<p>Design, visualize, and order premium custom cabinets online. Choose your style, materials, and finishes with our 360° preview tool.</p>\",\"hero_image\":\"/uploads/general/Cab_Hero_1762146321_6de686ffd819.glb\",\"features_title\":\"<h2>Why Choose <span style=\\\"color: #667eea;\\\">RADS TOOLING</span>?</h2>\",\"features_subtitle\":\"<p>Everything you need to create your perfect cabinet</p>\",\"carousel_images\":[{\"image\":\"/assets/images/cab4.jpg\",\"title\":\"Bathroom Vanity\",\"description\":\"Water-resistant premium materials\"},{\"image\":\"/assets/images/cab3.jpg\",\"title\":\"Living Room Display\",\"description\":\"Showcase your style with custom shelving\"},{\"image\":\"/assets/images/cab1.jpg\",\"title\":\"Modern Kitchen\",\"description\":\"Contemporary design with premium finishes\"},{\"image\":\"/assets/images/cab5.jpg\",\"title\":\"Office Storage\",\"description\":\"Professional workspace solutions\"},{\"image\":\"/assets/images/cab2.jpg\",\"title\":\"Bedroom Wardrobe\",\"description\":\"Spacious storage with elegant styling\"}],\"video_title\":\"<h2><span style=\\\"color: #2f5b88;\\\">C</span>rafted with Passion &amp; Precision</h2>\",\"video_subtitle\":\"<p>Every cabinet is handcrafted by skilled artisans using premium materials. Watch our craftsmen bring your vision to life.</p>\",\"video_url\":\"uploads/general/Cabinets_1762245011_1dce2a5842be.mp4\",\"video_poster\":\"/assets/images/video-poster.jpg\",\"cta_headline\":\"<h2>Ready to Design Your Dream Cabinet?</h2>\",\"cta_text\":\"<p>Join hundreds of satisfied customers who transformed their spaces</p>\",\"footer_company\":\"RADS TOOLING\",\"footer_description\":\"Premium custom cabinet manufacturer serving clients since 2007. Quality craftsmanship, affordable prices, and exceptional service.\",\"footer_address\":\"Green Breeze, Piela, Dasmariñas, Cavite\",\"footer_email\":\"RadsTooling@gmail.com\",\"footer_phone\":\"+63 976 228 4270\",\"footer_hours\":\"Mon-Sat: 8:00 AM - 5:00 PM\",\"footer_copyright\":\"© 2025 RADS TOOLING INC. All rights reserved.\"}', 'draft', 30, 'System Owner44', '2025-11-27 02:32:56', '2025-11-26 18:32:56'),
(289, 'home_public', 'Public Homepage', '{\"hero_headline\":\"<h1><span style=\\\"color: rgb(47, 91, 136);\\\">C</span>ustomize Your Dream Cabinets</h1>\",\"hero_subtitle\":\"<p>Design, visualize, and order premium custom cabinets online. Choose your style, materials, and finishes with our 360° preview tool.</p>\",\"hero_image\":\"/uploads/general/Cab_Hero_1762146321_6de686ffd819.glb\",\"features_title\":\"<h2>Why Choose <span style=\\\"color: #667eea;\\\">RADS TOOLING</span>?</h2>\",\"features_subtitle\":\"<p>Everything you need to create your perfect cabinet</p>\",\"carousel_images\":[{\"image\":\"/assets/images/cab4.jpg\",\"title\":\"Bathroom Vanity\",\"description\":\"Water-resistant premium materials\"},{\"image\":\"/assets/images/cab3.jpg\",\"title\":\"Living Room Display\",\"description\":\"Showcase your style with custom shelving\"},{\"image\":\"/assets/images/cab1.jpg\",\"title\":\"Modern Kitchen\",\"description\":\"Contemporary design with premium finishes\"},{\"image\":\"/assets/images/cab5.jpg\",\"title\":\"Office Storage\",\"description\":\"Professional workspace solutions\"},{\"image\":\"/assets/images/cab2.jpg\",\"title\":\"Bedroom Wardrobe\",\"description\":\"Spacious storage with elegant styling\"}],\"video_title\":\"<h2><span style=\\\"color: #2f5b88;\\\">C</span>rafted with Passion &amp; Precision</h2>\",\"video_subtitle\":\"<p>Every cabinet is handcrafted by skilled artisans using premium materials. Watch our craftsmen bring your vision to life.</p>\",\"video_url\":\"uploads/general/Cabinets_1762245011_1dce2a5842be.mp4\",\"video_poster\":\"/assets/images/video-poster.jpg\",\"cta_headline\":\"<h2>Ready to Design Your Dream Cabinet?</h2>\",\"cta_text\":\"<p>Join hundreds of satisfied customers who transformed their spaces</p>\",\"footer_company\":\"RADS TOOLING\",\"footer_description\":\"Premium custom cabinet manufacturer serving clients since 2007. Quality craftsmanship, affordable prices, and exceptional service.\",\"footer_address\":\"Green Breeze, Piela, Dasmariñas, Cavite\",\"footer_email\":\"RadsTooling@gmail.com\",\"footer_phone\":\"+63 976 228 4270\",\"footer_hours\":\"Mon-Sat: 8:00 AM - 5:00 PM\",\"footer_copyright\":\"© 2025 RADS TOOLING INC. All rights reserved.\"}', 'published', 30, 'System Owner44', '2025-11-27 02:32:59', '2025-11-26 18:32:59');

-- --------------------------------------------------------

--
-- Table structure for table `rt_faqs`
--

CREATE TABLE `rt_faqs` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rt_faqs`
--

INSERT INTO `rt_faqs` (`id`, `question`, `answer`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'Can I customize size/color?', 'Yes! Share your preferred dimensions and color; we will confirm feasibility and pricing.', 1, '2025-10-06 14:42:30', '2025-10-09 10:53:24'),
(10, 'What are your lead times?', 'Typical build time is 7–14 days depending on customization.', 1, '2025-10-07 12:03:42', '2025-10-07 12:03:42'),
(12, 'What are your operating hours?', 'We operate from 8am-5pm', 1, '2025-10-07 14:24:18', '2025-10-07 17:16:12');

-- --------------------------------------------------------

--
-- Table structure for table `shared_images`
--

CREATE TABLE `shared_images` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `reference_count` int(11) DEFAULT 1,
  `first_uploaded` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shared_images`
--

INSERT INTO `shared_images` (`id`, `image_path`, `reference_count`, `first_uploaded`, `last_used`) VALUES
(94, 'uploads/products/cab2_1762604309_3dca021a969f.jpg', 1, '2025-11-08 12:19:01', '2025-11-08 12:19:01'),
(95, 'uploads/products/cab2_1762619191_a2b7d9439d7d.jpg', 1, '2025-11-08 16:26:40', '2025-11-08 16:26:40'),
(96, 'uploads/products/cab9_1762640352_9b06fb9799ad.jpg', 1, '2025-11-08 22:19:14', '2025-11-08 22:19:14'),
(97, 'uploads/products/cab10_1762640352_9a7c9e5ac350.jpg', 1, '2025-11-08 22:19:14', '2025-11-08 22:19:14'),
(98, 'uploads/products/cabinet-hero_1762640352_04d68fe25b0d.jpg', 1, '2025-11-08 22:19:14', '2025-11-08 22:19:14'),
(99, 'uploads/products/cab1_1762664359_ce067d74e047.jpg', 1, '2025-11-09 04:59:26', '2025-11-09 04:59:26'),
(100, 'uploads/products/cab2_1762664359_07e3db599216.jpg', 1, '2025-11-09 04:59:26', '2025-11-09 04:59:26'),
(101, 'uploads/products/cab3_1762664359_841ed3fcbe9f.jpg', 1, '2025-11-09 04:59:26', '2025-11-09 04:59:26');

-- --------------------------------------------------------

--
-- Table structure for table `textures`
--

CREATE TABLE `textures` (
  `id` int(11) NOT NULL,
  `texture_name` varchar(100) NOT NULL,
  `texture_code` varchar(50) NOT NULL,
  `texture_image` varchar(255) NOT NULL,
  `allowed_parts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' CHECK (json_valid(`allowed_parts`)),
  `base_price` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `textures`
--

INSERT INTO `textures` (`id`, `texture_name`, `texture_code`, `texture_image`, `allowed_parts`, `base_price`, `description`, `is_active`, `created_at`) VALUES
(10, 'Oak Wood', 'WOOD_OAK', 'texture_1762619114_690f6eeab9e30.jpg', '[]', 123.00, 'Wood Oak', 1, '2025-11-08 16:25:16'),
(11, 'DARK WOOD', 'DARK_WOOD', 'texture_1762631177_690f9e0954207.jpg', '[]', 222.00, 'Dark Wood', 1, '2025-11-08 19:46:18'),
(12, 'Matte', 'MATTE_GRAY', 'texture_1762639425_690fbe41edfb6.jpg', '[]', 234.00, 'Matte Gray', 1, '2025-11-08 22:03:47');

-- --------------------------------------------------------

--
-- Table structure for table `texture_allowed_parts`
--

CREATE TABLE `texture_allowed_parts` (
  `id` int(11) NOT NULL,
  `texture_id` int(11) NOT NULL,
  `part_name` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `texture_allowed_parts`
--

INSERT INTO `texture_allowed_parts` (`id`, `texture_id`, `part_name`) VALUES
(9, 6, 'body'),
(10, 6, 'door'),
(17, 10, 'body'),
(18, 11, 'interior'),
(19, 12, 'door');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_type` enum('customer','admin') NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_type`, `user_id`, `action`, `details`, `created_at`) VALUES
(69, 'customer', 39, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-03 03:46:21'),
(70, '', 1, 'login', 'Staff login (dashboard)', '2025-11-03 03:47:10'),
(71, '', 1, 'login', 'Staff login (dashboard)', '2025-11-03 04:00:57'),
(72, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-03 04:05:18'),
(73, '', 1, 'login', 'Staff login (dashboard)', '2025-11-03 04:06:42'),
(74, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-03 04:06:57'),
(75, 'customer', 40, 'register', '{\"email\":\"capstoink@gmail.com\",\"username\":\"capstonk\"}', '2025-11-03 04:07:18'),
(76, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-03 04:07:45'),
(77, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-03 04:08:18'),
(78, '', 1, 'login', 'Staff login (dashboard)', '2025-11-03 04:12:55'),
(79, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-03 04:17:21'),
(80, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-03 04:17:25'),
(81, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-03 04:28:26'),
(82, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-03 04:30:50'),
(83, '', 1, 'login', 'Staff login (dashboard)', '2025-11-03 05:04:35'),
(84, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-03 05:05:34'),
(85, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-03 05:34:58'),
(86, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-03 06:04:57'),
(87, '', 1, 'login', 'Staff login (dashboard)', '2025-11-04 03:58:48'),
(88, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-04 03:58:54'),
(89, 'customer', 41, 'register', '{\"email\":\"moenpogi045@gmail.com\",\"username\":\"moenpogi21\"}', '2025-11-04 03:59:06'),
(90, 'customer', 41, 'login', 'Customer login (shop)', '2025-11-04 03:59:30'),
(91, 'customer', 41, 'logout', 'User logged out: Moen Secapuri', '2025-11-04 03:59:35'),
(92, '', 1, 'login', 'Staff login (dashboard)', '2025-11-04 03:59:42'),
(93, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-04 04:00:33'),
(94, 'customer', 41, 'login', 'Customer login (shop)', '2025-11-04 04:00:39'),
(95, 'customer', 41, 'logout', 'User logged out: Moen Secapuri', '2025-11-04 04:01:22'),
(96, '', 1, 'login', 'Staff login (dashboard)', '2025-11-04 04:01:26'),
(97, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-04 04:03:55'),
(98, 'customer', 41, 'login', 'Customer login (shop)', '2025-11-04 04:04:05'),
(99, 'customer', 41, 'logout', 'User logged out: Moen Secapuri', '2025-11-04 04:06:15'),
(100, '', 1, 'login', 'Staff login (dashboard)', '2025-11-04 04:06:21'),
(101, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-04 04:07:31'),
(102, 'customer', 41, 'login', 'Customer login (shop)', '2025-11-04 04:07:36'),
(103, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-04 06:25:20'),
(104, 'customer', 41, 'login', 'Customer login (shop)', '2025-11-04 06:25:30'),
(105, '', 1, 'login', 'Staff login (dashboard)', '2025-11-04 08:19:24'),
(106, 'customer', 41, 'login', 'Customer login (shop)', '2025-11-04 13:06:05'),
(107, 'customer', 41, 'logout', 'User logged out: Moen Secapuri', '2025-11-04 13:06:14'),
(108, '', 1, 'login', 'Staff login (dashboard)', '2025-11-04 13:06:18'),
(109, '', 1, 'login', 'Staff login (dashboard)', '2025-11-05 16:54:24'),
(110, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-05 16:54:33'),
(111, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-05 16:55:34'),
(112, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-05 16:56:07'),
(113, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-05 16:56:13'),
(114, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-05 16:56:51'),
(115, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-05 16:56:57'),
(116, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-05 16:57:00'),
(117, '', 1, 'login', 'Staff login (dashboard)', '2025-11-05 16:57:24'),
(118, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-05 19:44:14'),
(119, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-05 19:44:31'),
(120, '', 1, 'login', 'Staff login (dashboard)', '2025-11-05 20:17:14'),
(121, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-05 20:18:39'),
(122, '', 1, 'login', 'Staff login (dashboard)', '2025-11-05 20:56:42'),
(123, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-05 21:12:06'),
(124, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-05 21:13:38'),
(125, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-05 21:14:47'),
(126, '', 1, 'login', 'Staff login (dashboard)', '2025-11-05 21:14:52'),
(127, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-05 21:16:01'),
(128, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-05 21:16:13'),
(129, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-05 21:16:29'),
(130, '', 1, 'login', 'Staff login (dashboard)', '2025-11-05 21:16:36'),
(131, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-05 21:17:00'),
(132, '', 1, 'login', 'Staff login (dashboard)', '2025-11-05 21:17:06'),
(133, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-05 21:17:30'),
(134, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-05 21:17:37'),
(135, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-05 21:17:49'),
(136, '', 1, 'login', 'Staff login (dashboard)', '2025-11-05 21:17:55'),
(137, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-05 21:18:01'),
(138, '', 1, 'login', 'Staff login (dashboard)', '2025-11-05 21:19:09'),
(139, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-05 22:27:59'),
(140, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-05 22:38:30'),
(141, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-05 22:40:25'),
(142, '', 1, 'login', 'Staff login (dashboard)', '2025-11-05 22:40:29'),
(143, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-05 22:40:47'),
(144, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-05 22:40:54'),
(145, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-05 22:53:44'),
(146, '', 1, 'login', 'Staff login (dashboard)', '2025-11-05 22:53:50'),
(147, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-05 23:18:06'),
(148, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-05 23:18:18'),
(149, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-06 08:45:25'),
(150, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-06 08:56:10'),
(151, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-06 11:40:55'),
(152, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-06 11:50:39'),
(153, '', 1, 'login', 'Staff login (dashboard)', '2025-11-06 11:50:45'),
(154, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-06 11:51:05'),
(155, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-06 11:51:14'),
(156, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-06 12:21:20'),
(157, '', 1, 'login', 'Staff login (dashboard)', '2025-11-06 12:21:28'),
(158, '', 1, 'login', 'Staff login (dashboard)', '2025-11-06 12:21:59'),
(159, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-06 12:23:50'),
(160, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-06 12:23:57'),
(161, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-06 12:25:22'),
(162, '', 1, 'login', 'Staff login (dashboard)', '2025-11-06 12:25:26'),
(163, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-06 12:25:55'),
(164, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-06 12:26:03'),
(165, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 03:29:15'),
(166, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 03:29:57'),
(167, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 03:30:01'),
(168, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 03:30:13'),
(169, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 03:30:23'),
(170, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 03:59:38'),
(171, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 03:59:48'),
(172, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 04:13:07'),
(173, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 04:13:14'),
(174, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 04:14:22'),
(175, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 04:21:08'),
(176, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 04:21:15'),
(177, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 04:21:26'),
(178, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 04:21:36'),
(179, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 04:21:53'),
(180, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 04:22:11'),
(181, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 04:22:20'),
(182, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 04:23:10'),
(183, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 04:23:15'),
(184, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 05:12:42'),
(185, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 05:48:36'),
(186, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 10:27:16'),
(187, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 10:27:48'),
(188, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 10:27:56'),
(189, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 10:28:08'),
(190, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 10:28:14'),
(191, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 10:30:18'),
(192, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 10:36:08'),
(193, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 10:37:04'),
(194, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 10:37:11'),
(195, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 10:37:15'),
(196, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 10:37:21'),
(197, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 10:37:30'),
(198, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 10:38:13'),
(199, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 10:38:19'),
(200, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 10:38:38'),
(201, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 10:38:44'),
(202, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 10:42:23'),
(203, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 10:42:27'),
(204, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 10:42:56'),
(205, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 10:43:03'),
(206, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 10:43:34'),
(207, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 10:43:39'),
(208, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 10:43:46'),
(209, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 10:44:17'),
(210, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 10:45:02'),
(211, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 10:45:06'),
(212, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 10:46:04'),
(213, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 10:46:33'),
(214, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 11:44:27'),
(215, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 11:45:26'),
(216, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 11:45:31'),
(217, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 11:45:56'),
(218, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 11:46:05'),
(219, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 11:46:46'),
(220, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 11:46:49'),
(221, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 11:47:14'),
(222, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 11:47:19'),
(223, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 11:47:31'),
(224, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 11:47:36'),
(225, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 11:47:58'),
(226, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 11:48:03'),
(227, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 11:48:30'),
(228, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 12:31:27'),
(229, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 12:32:21'),
(230, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 12:32:27'),
(231, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 12:32:36'),
(232, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 12:32:49'),
(233, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 12:33:00'),
(234, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 12:33:10'),
(235, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 12:36:12'),
(236, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 12:37:20'),
(237, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 12:37:33'),
(238, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 12:49:30'),
(239, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 13:37:04'),
(240, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 13:37:09'),
(241, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 13:37:49'),
(242, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 13:38:01'),
(243, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 13:58:26'),
(244, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 13:59:15'),
(245, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 13:59:29'),
(246, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 15:31:04'),
(247, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 15:31:09'),
(248, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 17:10:22'),
(249, '', 1, 'login', 'Staff login (dashboard)', '2025-11-07 17:57:26'),
(250, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-07 18:02:50'),
(251, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 18:03:00'),
(252, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 18:03:06'),
(253, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 18:03:14'),
(254, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-07 18:07:31'),
(255, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 18:07:38'),
(256, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 19:02:36'),
(257, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-07 19:12:21'),
(258, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 11:52:02'),
(259, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-08 12:13:28'),
(260, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 12:13:32'),
(261, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 12:14:39'),
(262, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-08 12:22:10'),
(263, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 12:23:56'),
(264, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-08 12:24:03'),
(265, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 12:24:10'),
(266, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-08 12:27:49'),
(267, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 14:08:11'),
(268, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 14:08:48'),
(269, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 15:55:11'),
(270, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-08 16:26:51'),
(271, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 16:27:00'),
(272, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-08 19:27:08'),
(273, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 19:27:19'),
(274, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-08 19:45:19'),
(275, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 19:45:24'),
(276, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-08 19:47:01'),
(277, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 19:47:07'),
(278, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-08 20:28:48'),
(279, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 20:28:52'),
(280, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 20:29:05'),
(281, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-08 20:51:04'),
(282, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 20:51:10'),
(283, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 20:51:26'),
(284, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-08 20:51:51'),
(285, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 20:51:55'),
(286, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-08 20:52:23'),
(287, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 20:52:28'),
(288, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-08 20:52:51'),
(289, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 20:52:59'),
(290, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-08 20:53:40'),
(291, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 20:53:45'),
(292, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-08 21:55:01'),
(293, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 21:55:06'),
(294, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-08 21:55:29'),
(295, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 21:55:54'),
(296, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-08 22:03:06'),
(297, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 22:03:10'),
(298, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-08 22:03:56'),
(299, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 22:04:00'),
(300, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 22:18:47'),
(301, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-08 22:18:56'),
(302, '', 1, 'login', 'Staff login (dashboard)', '2025-11-08 22:19:04'),
(303, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-08 22:19:17'),
(304, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-08 22:19:25'),
(305, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 02:09:50'),
(306, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 02:41:20'),
(307, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 02:41:25'),
(308, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 02:42:37'),
(309, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 02:42:43'),
(310, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 02:43:16'),
(311, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 02:43:50'),
(312, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 02:44:09'),
(313, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 02:56:40'),
(314, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 03:18:00'),
(315, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 03:18:29'),
(316, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 03:18:33'),
(317, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 03:21:32'),
(318, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 03:21:39'),
(319, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 03:30:18'),
(320, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 03:30:22'),
(321, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 03:30:29'),
(322, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 03:30:38'),
(323, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 04:03:17'),
(324, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 04:20:39'),
(325, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 04:20:49'),
(326, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 04:29:45'),
(327, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 04:29:48'),
(328, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 04:58:02'),
(329, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 04:58:32'),
(330, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 04:58:37'),
(331, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 05:00:12'),
(332, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 05:00:18'),
(333, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 05:00:33'),
(334, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 05:00:40'),
(335, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 05:00:55'),
(336, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 05:01:01'),
(337, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 05:02:57'),
(338, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 05:04:39'),
(339, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 05:04:44'),
(340, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 05:07:55'),
(341, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 06:05:26'),
(342, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 06:07:34'),
(343, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 06:07:39'),
(344, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 06:08:45'),
(345, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 06:08:53'),
(346, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 06:20:54'),
(347, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 06:21:00'),
(348, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 06:22:31'),
(349, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 06:23:08'),
(350, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 06:23:40'),
(351, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 06:23:54'),
(352, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 07:27:25'),
(353, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 08:03:30'),
(354, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 08:04:11'),
(355, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 08:09:01'),
(356, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 08:09:14'),
(357, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 08:13:56'),
(358, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 08:14:00'),
(359, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 08:15:46'),
(360, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 08:15:57'),
(361, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 08:31:15'),
(362, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 08:32:46'),
(363, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 08:32:55'),
(364, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 08:33:09'),
(365, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 08:40:25'),
(366, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 08:40:29'),
(367, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 08:41:18'),
(368, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 08:41:25'),
(369, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 08:52:05'),
(370, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 08:52:22'),
(371, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 08:52:29'),
(372, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 08:52:45'),
(373, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 08:53:36'),
(374, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 08:53:40'),
(375, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 08:53:49'),
(376, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 08:55:23'),
(377, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 08:55:34'),
(378, '', 1, 'login', 'Staff login (dashboard)', '2025-11-09 08:55:42'),
(379, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-09 08:55:50'),
(380, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 08:56:29'),
(381, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 09:05:39'),
(382, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 09:05:45'),
(383, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 09:11:40'),
(384, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 09:11:46'),
(385, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 09:12:51'),
(386, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 09:48:31'),
(387, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-09 09:50:48'),
(388, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-09 09:50:53'),
(389, '', 1, 'login', 'Staff login (dashboard)', '2025-11-17 17:10:15'),
(390, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-17 17:10:44'),
(391, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-17 17:10:56'),
(392, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-17 17:12:26'),
(393, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-17 17:12:32'),
(394, 'customer', 40, 'login', 'Customer login (shop)', '2025-11-21 19:19:03'),
(395, 'customer', 40, 'logout', 'User logged out: Capstonk Capstoink', '2025-11-21 19:19:58'),
(396, '', 1, 'login', 'Staff login (dashboard)', '2025-11-21 19:20:03'),
(397, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-21 19:20:49'),
(398, '', 1, 'login', 'Staff login (dashboard)', '2025-11-24 15:02:20'),
(399, '', 1, 'login', 'Staff login (dashboard)', '2025-11-24 16:50:34'),
(400, '', 1, 'login', 'Staff login (dashboard)', '2025-11-24 21:45:26'),
(401, '', 1, 'login', 'Staff login (dashboard)', '2025-11-24 21:46:25'),
(402, '', 1, 'login', 'Staff login (dashboard)', '2025-11-24 21:48:34'),
(403, '', 1, 'login', 'Staff login (dashboard)', '2025-11-25 15:54:37'),
(404, '', 1, 'login', 'Staff login (dashboard)', '2025-11-25 21:37:56'),
(405, '', 1, 'login', 'Staff login (dashboard)', '2025-11-26 00:41:08'),
(406, '', 1, 'login', 'Staff login (dashboard)', '2025-11-26 01:37:12'),
(407, '', 1, 'login', 'Staff login (dashboard)', '2025-11-26 09:48:10'),
(408, '', 1, 'login', 'Staff login (dashboard)', '2025-11-26 18:32:22'),
(409, '', 1, 'login', 'Staff login (dashboard)', '2025-11-26 18:34:11'),
(410, '', 1, 'login', 'Staff login (dashboard)', '2025-11-27 15:43:24'),
(411, '', 1, 'login', 'Staff login (dashboard)', '2025-11-27 16:26:22'),
(412, '', 1, 'logout', 'User logged out: System Owner44', '2025-11-27 17:03:46'),
(413, '', 1, 'login', 'Staff login (dashboard)', '2025-11-27 17:03:55'),
(414, '', 1, 'login', 'Staff login (dashboard)', '2025-11-27 20:34:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_username_unique` (`username`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `cabinets`
--
ALTER TABLE `cabinets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `cabinet_id` (`cabinet_id`),
  ADD KEY `customization_id` (`customization_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_item_type` (`item_type`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `color_code` (`color_code`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `color_allowed_parts`
--
ALTER TABLE `color_allowed_parts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `color_id` (`color_id`,`part_name`),
  ADD KEY `color_id_2` (`color_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username_unique` (`username`),
  ADD UNIQUE KEY `email_unique` (`email`),
  ADD KEY `idx_verification_code` (`verification_code`),
  ADD KEY `idx_password_reset_code` (`password_reset_code`);

--
-- Indexes for table `customizations`
--
ALTER TABLE `customizations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_cabinet_id` (`cabinet_id`);

--
-- Indexes for table `customization_steps`
--
ALTER TABLE `customization_steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cs_customization_id` (`customization_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_order_customer` (`order_id`,`customer_id`),
  ADD KEY `idx_feedback_customer` (`customer_id`),
  ADD KEY `idx_feedback_order` (`order_id`),
  ADD KEY `idx_feedback_status` (`status`);

--
-- Indexes for table `handle_types`
--
ALTER TABLE `handle_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `measurement_units`
--
ALTER TABLE `measurement_units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sender` (`sender_type`,`sender_id`),
  ADD KEY `idx_receiver` (`receiver_type`,`receiver_id`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_orders_customer_status` (`customer_id`,`status`),
  ADD KEY `idx_orders_date` (`order_date`),
  ADD KEY `idx_is_customized` (`is_customized`);

--
-- Indexes for table `order_addresses`
--
ALTER TABLE `order_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_completions`
--
ALTER TABLE `order_completions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `cabinet_id` (`cabinet_id`),
  ADD KEY `customization_id` (`customization_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_item_type` (`item_type`),
  ADD KEY `idx_order_items_order` (`order_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_user_type_id` (`user_type`,`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pay_order` (`order_id`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_payment_date` (`payment_date`);

--
-- Indexes for table `payment_installments`
--
ALTER TABLE `payment_installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_installment_number` (`order_id`,`installment_number`),
  ADD KEY `fk_pi_verified_by` (`verified_by`);

--
-- Indexes for table `payment_qr`
--
ALTER TABLE `payment_qr`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_verifications`
--
ALTER TABLE `payment_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_stock` (`stock`),
  ADD KEY `idx_products_created_by` (`created_by`),
  ADD KEY `idx_products_created_by_20251015` (`created_by`);

--
-- Indexes for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_color` (`product_id`,`color_id`),
  ADD KEY `idx_pc_color_id` (`color_id`),
  ADD KEY `idx_pc_product_id` (`product_id`);

--
-- Indexes for table `product_handles`
--
ALTER TABLE `product_handles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_handle` (`product_id`,`handle_id`),
  ADD KEY `idx_ph_handle_id` (`handle_id`),
  ADD KEY `idx_ph_product_id` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD UNIQUE KEY `idx_product_image_unique` (`product_id`,`image_path`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `idx_image_path` (`image_path`);

--
-- Indexes for table `product_material_map`
--
ALTER TABLE `product_material_map`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product` (`product_id`);

--
-- Indexes for table `product_size_config`
--
ALTER TABLE `product_size_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_psc_product_id` (`product_id`);

--
-- Indexes for table `product_textures`
--
ALTER TABLE `product_textures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pt_product_id` (`product_id`),
  ADD KEY `idx_pt_texture_id` (`texture_id`);

--
-- Indexes for table `product_texture_parts`
--
ALTER TABLE `product_texture_parts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_product_texture_part` (`product_id`,`texture_id`,`part_key`),
  ADD KEY `idx_ptp_product` (`product_id`),
  ADD KEY `idx_ptp_texture` (`texture_id`);

--
-- Indexes for table `rt_chat_messages`
--
ALTER TABLE `rt_chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_thread` (`thread_id`),
  ADD KEY `idx_sender` (`sender_type`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `rt_chat_threads`
--
ALTER TABLE `rt_chat_threads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `thread_code` (`thread_code`),
  ADD KEY `idx_code` (`thread_code`),
  ADD KEY `idx_last_message` (`last_message_at`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_customer_id` (`customer_id`);

--
-- Indexes for table `rt_cms_pages`
--
ALTER TABLE `rt_cms_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_page_key_status` (`page_key`,`status`),
  ADD KEY `idx_updated` (`updated_at`);

--
-- Indexes for table `rt_faqs`
--
ALTER TABLE `rt_faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `shared_images`
--
ALTER TABLE `shared_images`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_shared_image_path` (`image_path`);

--
-- Indexes for table `textures`
--
ALTER TABLE `textures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `texture_allowed_parts`
--
ALTER TABLE `texture_allowed_parts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_texture_part` (`texture_id`,`part_name`),
  ADD KEY `texture_id` (`texture_id`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_type_id` (`user_type`,`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cabinets`
--
ALTER TABLE `cabinets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `color_allowed_parts`
--
ALTER TABLE `color_allowed_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `customizations`
--
ALTER TABLE `customizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customization_steps`
--
ALTER TABLE `customization_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `handle_types`
--
ALTER TABLE `handle_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `measurement_units`
--
ALTER TABLE `measurement_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `order_addresses`
--
ALTER TABLE `order_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `order_completions`
--
ALTER TABLE `order_completions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `payment_installments`
--
ALTER TABLE `payment_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `payment_qr`
--
ALTER TABLE `payment_qr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_verifications`
--
ALTER TABLE `payment_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=276;

--
-- AUTO_INCREMENT for table `product_handles`
--
ALTER TABLE `product_handles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `product_material_map`
--
ALTER TABLE `product_material_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_size_config`
--
ALTER TABLE `product_size_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=619;

--
-- AUTO_INCREMENT for table `product_textures`
--
ALTER TABLE `product_textures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=231;

--
-- AUTO_INCREMENT for table `product_texture_parts`
--
ALTER TABLE `product_texture_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rt_chat_messages`
--
ALTER TABLE `rt_chat_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `rt_chat_threads`
--
ALTER TABLE `rt_chat_threads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rt_cms_pages`
--
ALTER TABLE `rt_cms_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=290;

--
-- AUTO_INCREMENT for table `rt_faqs`
--
ALTER TABLE `rt_faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `shared_images`
--
ALTER TABLE `shared_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `textures`
--
ALTER TABLE `textures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `texture_allowed_parts`
--
ALTER TABLE `texture_allowed_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=415;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`cabinet_id`) REFERENCES `cabinets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_4` FOREIGN KEY (`customization_id`) REFERENCES `customizations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `color_allowed_parts`
--
ALTER TABLE `color_allowed_parts`
  ADD CONSTRAINT `fk_color_allowed_parts_color` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customizations`
--
ALTER TABLE `customizations`
  ADD CONSTRAINT `customizations_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customizations_ibfk_2` FOREIGN KEY (`cabinet_id`) REFERENCES `cabinets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customization_steps`
--
ALTER TABLE `customization_steps`
  ADD CONSTRAINT `fk_cs_customization` FOREIGN KEY (`customization_id`) REFERENCES `customizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `order_addresses`
--
ALTER TABLE `order_addresses`
  ADD CONSTRAINT `order_addresses_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_completions`
--
ALTER TABLE `order_completions`
  ADD CONSTRAINT `fk_oc_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`cabinet_id`) REFERENCES `cabinets` (`id`),
  ADD CONSTRAINT `order_items_ibfk_4` FOREIGN KEY (`customization_id`) REFERENCES `customizations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_installments`
--
ALTER TABLE `payment_installments`
  ADD CONSTRAINT `fk_pi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pi_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_verifications`
--
ALTER TABLE `payment_verifications`
  ADD CONSTRAINT `payment_verifications_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_created_by` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_products_created_by_20251015` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD CONSTRAINT `fk_pc_color` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pc_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_handles`
--
ALTER TABLE `product_handles`
  ADD CONSTRAINT `fk_ph_handle` FOREIGN KEY (`handle_id`) REFERENCES `handle_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ph_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_size_config`
--
ALTER TABLE `product_size_config`
  ADD CONSTRAINT `product_size_config_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_textures`
--
ALTER TABLE `product_textures`
  ADD CONSTRAINT `fk_pt_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pt_texture` FOREIGN KEY (`texture_id`) REFERENCES `textures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_texture_parts`
--
ALTER TABLE `product_texture_parts`
  ADD CONSTRAINT `fk_ptp_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ptp_texture` FOREIGN KEY (`texture_id`) REFERENCES `textures` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rt_chat_messages`
--
ALTER TABLE `rt_chat_messages`
  ADD CONSTRAINT `fk_rt_msg_thread` FOREIGN KEY (`thread_id`) REFERENCES `rt_chat_threads` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
