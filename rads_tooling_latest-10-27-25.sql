-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 05:10 AM
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
(3, 'kooqi', '$2y$10$kZsIWfAU7lDuZtxNEwtX7uI3VMr6cQEWdu.fhK6UufHSUFXMzcsUu', 'Muwen', 'Secretary', 'active', NULL, '2025-09-08 03:55:44', '2025-10-10 15:26:44'),
(5, 'moen1', '$2y$10$09gXq8kOtbBS6yWelnWdCuFcCXEucUmpi.pA5eOCI2XhaN9AJWJHe', 'moenmoen', 'Owner', 'active', 'uploads/avatars/avatar_5_1757631406.jpg', '2025-09-08 16:17:13', '2025-10-26 03:30:27'),
(7, 'admin2', '$2y$10$jhlRo2.mj1nXqS/W7aamzecqCWgRvgfTm7iyELp1PSrDVwmkwYATi', 'Admin2', 'Admin', 'active', 'uploads/avatars/avatar_7_1757533716.png', '2025-09-08 20:35:36', '2025-10-26 06:43:46'),
(8, 'moen', '$2y$10$ZeWD2l4zsfwWCzcmseZt8u2YKVVPBQPZmJlyKVTU/PZ7BVnOxBLNm', 'kooqi', 'Admin', 'active', NULL, '2025-09-10 18:33:15', '2025-10-07 13:06:08'),
(9, 'vim', '$2y$10$2MAPNPEUgvlTru18VV3zIOB5RvGeOnpT5d/rquwmw13czoqnX9Unq', 'Vien Santos', 'Admin', 'active', NULL, '2025-10-09 09:09:01', '2025-10-09 09:09:01'),
(10, 'xyriz', '$2y$10$Y4cSYJIhJLj3eVOCj3Hz5uQB0lnH/sPRkQqm3FudzUWolGVE29f6i', 'Xyriz Salog', 'Admin', 'active', NULL, '2025-10-12 17:50:27', '2025-10-12 17:50:27');

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
  `cabinet_id` int(11) DEFAULT NULL,
  `customization_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
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
(1, 'black lang', 'black nga ', '#000000', 200.00, 1, '2025-10-24 05:18:43'),
(2, 'yellow na', 'YELLOW NAMAN', '#fbff00', 250.00, 1, '2025-10-24 17:28:14');

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
(16, 'kooqi', 'Moen Secapupu', 'moen.secapuri27@gmail.com', 'assets/uploads/avatars/customer_16_1760622859.jpg', 1, NULL, NULL, '884490', '2025-10-10 15:17:17', '+639192004234', 'Blk 4 Lot 47 Laterraza Phase A', '$2y$10$5nm.I29rcsZTm3CvPHayYOC3B7GHa/qY8seqlWXxjinHaB0sJRhDy', '2025-10-06 01:16:30', '2025-10-16 13:54:19'),
(17, 'moen', 'Moen Secapuri', 'kooqicookie@gmail.com', NULL, 1, NULL, NULL, NULL, NULL, '+639192088080', NULL, '$2y$10$l5kgj0CZ8/g1knnrhReSAOPJV5FDSIP3pMlJgqxJ5YSAax51bFLzG', '2025-10-06 01:32:09', '2025-10-08 17:08:04'),
(32, 'vim', 'Vien Santos', 'viensantos98@gmail.com', 'assets/uploads/avatars/customer_32_1760996431.jpg', 1, NULL, NULL, NULL, NULL, '+639873214351', 'BLk 19 2123', '$2y$10$mNScO5nQXywSg9cjUHne9uXmxLxs0NO8MPADa1jsvteQZIVk96HZq', '2025-10-15 15:20:52', '2025-10-23 13:49:22'),
(33, 'abcde', 'vien ezekiel santos', 'santos2284@cgscavite.com', NULL, 1, NULL, NULL, NULL, NULL, '+639762284470', NULL, '$2y$10$WS97oPps2IKkYlwAtHrDveoPncRwsTShJ6M7n9Kp3ODa1cwQU3lhq', '2025-10-20 22:01:48', '2025-10-20 22:02:25'),
(34, 'ayeut', 'irish ferreras', 'ferreras122402@gmail.com', NULL, 1, NULL, NULL, NULL, NULL, '+639123123123', NULL, '$2y$10$IB5YffBRCGiFG0PUPOgVpO3EebsHznCp.yPpPbXWUVNitcWsuqrZG', '2025-10-20 22:04:50', '2025-10-20 22:05:45');

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
  `status` enum('pending','released') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_released` tinyint(1) DEFAULT 0,
  `released_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `order_id`, `customer_id`, `rating`, `comment`, `status`, `created_at`, `is_released`, `released_at`) VALUES
(1, 23, 16, 5, 'great', 'released', '2025-10-24 04:17:26', 1, '2025-10-25 03:53:18');

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
(1, 'tset', 'test', 'handle_1761470325_68fde775f0e09.jpg', 500.00, 'test', 1, '2025-10-26 09:18:47');

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

INSERT INTO `orders` (`id`, `order_code`, `customer_id`, `total_amount`, `payment_status`, `is_installment`, `status`, `order_date`, `cancelled_by`, `cancellation_reason`, `cancelled_at`, `received_at`, `received_by_customer`, `is_received`, `customer_received_at`, `mode`, `subtotal`, `vat`, `created_at`) VALUES
(1, 'RT2510191662', 32, 6100.00, 'Partially Paid', 1, 'Processing', '2025-10-19 02:54:47', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 5000.00, 600.00, '2025-10-19 02:54:47'),
(2, 'RT2510197711', 32, 6100.00, 'Pending', 0, 'Pending', '2025-10-19 02:55:40', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 5000.00, 600.00, '2025-10-19 02:55:40'),
(3, 'RT2510199547', 32, 22400500.00, 'Pending', 0, 'Pending', '2025-10-19 02:58:49', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 20000000.00, 2400000.00, '2025-10-19 02:58:49'),
(4, 'RT2510195084', 32, 612.00, 'Pending', 0, 'Pending', '2025-10-19 07:43:37', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 07:43:37'),
(5, 'RT2510196971', 32, 612.00, 'Pending', 0, 'Processing', '2025-10-19 07:43:45', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 07:43:45'),
(6, 'RT2510194314', 32, 612.00, 'Pending', 0, 'Pending', '2025-10-19 08:10:05', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 08:10:05'),
(7, 'RT2510192809', 32, 612.00, 'Pending', 0, 'Pending', '2025-10-19 08:10:23', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 08:10:23'),
(8, 'RT2510190690', 32, 612.00, 'Pending', 0, 'Pending', '2025-10-19 08:12:19', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 08:12:19'),
(9, 'RT2510197430', 32, 612.00, 'Pending', 0, 'Pending', '2025-10-19 10:53:53', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 10:53:53'),
(10, 'RT2510197755', 32, 5600.00, 'Pending', 0, 'Completed', '2025-10-19 13:04:03', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 5000.00, 600.00, '2025-10-19 13:04:03'),
(11, 'RT2510197721', 16, 112.00, 'Pending', 0, 'Pending', '2025-10-19 13:57:28', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 100.00, 12.00, '2025-10-19 13:57:28'),
(12, 'RT2510195567', 16, 112.00, 'Pending', 0, 'Pending', '2025-10-19 13:57:39', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 100.00, 12.00, '2025-10-19 13:57:39'),
(13, 'RT2510191542', 16, 112.00, 'Pending', 0, 'Pending', '2025-10-19 13:57:46', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 100.00, 12.00, '2025-10-19 13:57:46'),
(14, 'RT2510194004', 16, 612.00, 'Pending', 0, 'Pending', '2025-10-19 14:18:43', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 14:18:43'),
(15, 'RT2510193173', 16, 612.00, 'Pending', 0, 'Pending', '2025-10-19 14:18:50', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 14:18:50'),
(16, 'RT2510199905', 16, 612.00, 'Pending', 0, 'Pending', '2025-10-19 14:23:25', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 14:23:25'),
(17, 'RT2510198555', 16, 612.00, 'Pending', 0, 'Pending', '2025-10-19 14:23:31', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 14:23:31'),
(18, 'RT2510195032', 16, 612.00, 'Pending', 0, 'Pending', '2025-10-19 14:24:41', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 14:24:41'),
(19, 'RT2510199574', 16, 612.00, 'Pending', 0, 'Pending', '2025-10-19 14:24:56', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 14:24:56'),
(20, 'RT2510195445', 16, 612.00, 'Pending', 0, 'Pending', '2025-10-19 14:45:09', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 14:45:09'),
(21, 'RT2510190307', 16, 612.00, 'Pending', 0, 'Pending', '2025-10-19 15:03:40', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 15:03:40'),
(22, 'RT2510192285', 16, 612.00, 'Pending', 0, 'Pending', '2025-10-19 15:12:34', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 15:12:34'),
(23, 'RT2510199582', 16, 112.00, 'Partially Paid', 0, 'Completed', '2025-10-19 15:58:34', NULL, NULL, NULL, NULL, 1, 1, '2025-10-24 12:02:50', 'pickup', 100.00, 12.00, '2025-10-19 15:58:34'),
(24, 'RT2510201261', 16, 612.00, 'Fully Paid', 0, 'Processing', '2025-10-19 16:34:44', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-19 16:34:44'),
(25, 'RT2510207729', 16, 112.00, 'Partially Paid', 0, 'Processing', '2025-10-19 17:46:39', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 100.00, 12.00, '2025-10-19 17:46:39'),
(26, 'RT2510200726', 32, 612.00, 'Pending', 1, 'Pending', '2025-10-20 11:00:56', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-20 11:00:56'),
(27, 'RT2510214540', 32, 612.00, 'Pending', 1, '', '2025-10-20 22:29:55', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 100.00, 12.00, '2025-10-20 22:29:55'),
(28, 'RT2510217005', 32, 637.76, 'Partially Paid', 1, 'Processing', '2025-10-21 01:38:26', NULL, NULL, NULL, NULL, 0, 0, NULL, 'delivery', 123.00, 14.76, '2025-10-21 01:38:26'),
(29, 'RT2510214493', 32, 112.00, '', 1, '', '2025-10-21 02:58:06', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 100.00, 12.00, '2025-10-21 02:58:06'),
(30, 'RT2510219137', 32, 112.00, '', 1, '', '2025-10-21 03:02:07', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 100.00, 12.00, '2025-10-21 03:02:07'),
(31, 'RT2510213207', 32, 112.00, '', 1, 'Processing', '2025-10-21 03:02:43', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 100.00, 12.00, '2025-10-21 03:02:43'),
(32, 'RT2510213845', 32, 112.00, '', 1, 'Processing', '2025-10-21 03:03:46', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 100.00, 12.00, '2025-10-21 03:03:46'),
(33, 'RT2510213440', 32, 112.00, 'Pending', 1, '', '2025-10-21 03:04:30', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 100.00, 12.00, '2025-10-21 03:04:30'),
(34, 'RT2510214793', 32, 112.00, '', 1, 'Processing', '2025-10-21 03:09:04', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 100.00, 12.00, '2025-10-21 03:09:04'),
(35, 'RT2510228417', 32, 112.00, 'Pending', 1, 'Processing', '2025-10-22 12:49:31', NULL, NULL, NULL, NULL, 0, 0, NULL, 'pickup', 100.00, 12.00, '2025-10-22 12:49:31');

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
(1, 1, 'delivery', 'vien ezekiel', 'santos', '+639762284470', '', 'Camiguin', 'Guinsiliban', 'Maac', 'Barangay Severino, Delas Alas', '4117'),
(2, 2, 'delivery', 'vien ezekiel', 'santos', '', '', 'Benguet', 'Kabayan', 'Gusaran', 'Barangay Severino, Delas Alas', '4117'),
(3, 3, 'delivery', 'vien ezekiel', 'santos', '+639762284470', '', 'Camiguin', 'Mahinog', 'San Jose', 'Barangay Severino, Delas Alas', '4117'),
(4, 4, 'delivery', 'vien ezekiel', 'santos', '+639762284470', '', 'Camiguin', 'Guinsiliban', 'Liong', 'Barangay Severino, Delas Alas', '4117'),
(5, 5, 'delivery', 'vien ezekiel', 'santos', '+639762284470', '', 'Camiguin', 'Guinsiliban', 'Liong', 'Barangay Severino, Delas Alas', '4117'),
(6, 6, 'delivery', 'vien ezekiel', 'santos', '+639762284470', '', 'Camiguin', 'Mahinog', 'San Isidro', 'Barangay Severino, Delas Alas', '4117'),
(7, 7, 'delivery', 'vien ezekiel', 'santos', '+639762284470', '', 'Camiguin', 'Mahinog', 'San Isidro', 'Barangay Severino, Delas Alas', '4117'),
(8, 8, 'delivery', 'vien ezekiel', 'santos', '+639762284470', '', 'Camiguin', 'Mahinog', 'San Isidro', 'Barangay Severino, Delas Alas', '4117'),
(9, 9, 'delivery', 'vien ezekiel', 'santos', '+639762284470', '', 'Camiguin', 'Guinsiliban', 'Cantaan', 'Barangay Severino, Delas Alas', '4117'),
(10, 10, 'pickup', 'vien ezekiel', 'santos', '+639762284470', 'viensantos98@gmail.com', '', '', '', '', ''),
(11, 11, 'pickup', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', '', '', '', '', ''),
(12, 12, 'pickup', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', '', '', '', '', ''),
(13, 13, 'pickup', 'Moen', 'Secapuri', '+639937060282', 'moenpogi045@gmail.com', '', '', '', '', ''),
(14, 14, 'delivery', 'Moen', 'Secapuri', '', '', 'Cavite', 'City of Imus', 'Bucandala III', 'Blk 4 Lot 47 Phase A Laterraza Subdivision', '4103'),
(15, 15, 'delivery', 'Moen', 'Secapuri', '', '', 'Cavite', 'City of Imus', 'Bucandala III', 'Blk 4 Lot 47 Phase A Laterraza Subdivision', '4103'),
(16, 16, 'delivery', 'Moen', 'Secapuri', '', '', 'Cavite', 'City of Imus', 'Bucandala III', 'Blk 4 Lot 47 Phase A Laterraza Subdivision', '4103'),
(17, 17, 'delivery', 'Moen', 'Secapuri', '', '', 'Cavite', 'City of Imus', 'Bucandala III', 'Blk 4 Lot 47 Phase A Laterraza Subdivision', '4103'),
(18, 18, 'delivery', 'Moen', 'Secapuri', '', '', 'Cavite', 'City of Imus', 'Bucandala III', 'Blk 4 Lot 47 Phase A Laterraza Subdivision', '4103'),
(19, 19, 'delivery', 'Moen', 'Secapuri', '', '', 'Cavite', 'City of Imus', 'Bucandala III', 'Blk 4 Lot 47 Phase A Laterraza Subdivision', '4103'),
(20, 20, 'delivery', '', '', '', '', '', '', '', '', ''),
(21, 21, 'delivery', '', '', '', '', '', '', '', '', ''),
(22, 22, 'delivery', '', '', '', '', '', '', '', '', ''),
(23, 23, 'pickup', '', '', '', '', '', '', '', '', ''),
(24, 24, 'delivery', '', '', '', '', '', '', '', '', ''),
(25, 25, 'pickup', '', '', '', '', '', '', '', '', ''),
(26, 26, '', '', '', '', '', '', '', '', '', ''),
(27, 27, '', '', '', '', '', '', '', '', '', ''),
(28, 28, '', '', '', '', '', '', '', '', '', ''),
(29, 29, '', '', '', '', '', '', '', '', '', ''),
(30, 30, '', '', '', '', '', '', '', '', '', ''),
(31, 31, '', '', '', '', '', '', '', '', '', ''),
(32, 32, '', '', '', '', '', '', '', '', '', ''),
(33, 33, '', '', '', '', '', '', '', '', '', ''),
(34, 34, '', '', '', '', '', '', '', '', '', ''),
(35, 35, '', '', '', '', '', '', '', '', '', '');

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
  `line_total` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_type`, `product_id`, `cabinet_id`, `customization_id`, `quantity`, `unit_price`, `subtotal`, `name`, `qty`, `line_total`) VALUES
(1, 1, 'product', 12, NULL, NULL, 1, 5000.00, 0.00, 'dsdasdasdada', 1, 5000.00),
(2, 2, 'product', 12, NULL, NULL, 1, 5000.00, 0.00, 'dsdasdasdada', 1, 5000.00),
(3, 3, 'product', 11, NULL, NULL, 1, 20000000.00, 0.00, 'testsetsdsada', 1, 20000000.00),
(4, 4, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(5, 5, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(6, 6, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(7, 7, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(8, 8, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(9, 9, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(10, 10, 'product', 12, NULL, NULL, 1, 5000.00, 0.00, 'dsdasdasdada', 1, 5000.00),
(11, 11, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(12, 12, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(13, 13, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(14, 14, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(15, 15, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(16, 16, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(17, 17, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(18, 18, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(19, 19, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(20, 20, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(21, 21, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(22, 22, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(23, 23, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(24, 24, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(25, 25, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(26, 26, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(27, 27, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(28, 28, 'product', 14, NULL, NULL, 1, 123.00, 0.00, 'test', 1, 123.00),
(29, 29, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(30, 30, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(31, 31, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(32, 32, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(33, 33, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(34, 34, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00),
(35, 35, 'product', 13, NULL, NULL, 1, 100.00, 0.00, 'test1', 1, 100.00);

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
(1, 1, 123.00, 'GCash QR', NULL, '2025-10-19 02:54:47', 1, '2025-10-19 05:06:36', 'gcash', 50, 3050.00, 'VERIFIED', '2025-10-19 05:06:36'),
(3, 2, 123.00, 'GCash QR', NULL, '2025-10-19 02:55:40', NULL, NULL, 'bpi', 50, 3050.00, 'REJECTED', '2025-10-19 13:05:54'),
(5, 3, 0.00, 'GCash QR', NULL, '2025-10-19 02:58:49', NULL, NULL, 'bpi', 50, 11200250.00, 'PENDING_VERIFICATION', '2025-10-19 02:58:49'),
(6, 23, 112.00, 'GCash QR', NULL, '2025-10-19 15:58:34', 1, '2025-10-19 16:00:30', 'gcash', 100, 112.00, 'VERIFIED', '2025-10-19 16:00:30'),
(8, 24, 612.00, 'GCash QR', NULL, '2025-10-19 16:34:44', 1, '2025-10-19 17:46:15', 'gcash', 100, 612.00, 'VERIFIED', '2025-10-19 17:46:15'),
(11, 25, 56.00, 'GCash QR', NULL, '2025-10-19 17:46:39', 1, '2025-10-19 17:47:09', 'gcash', 50, 56.00, 'VERIFIED', '2025-10-19 17:47:09'),
(13, 22, 0.00, 'GCash QR', NULL, '2025-10-20 01:30:30', NULL, NULL, 'gcash', 100, 612.00, 'PENDING_VERIFICATION', '2025-10-20 01:30:30'),
(14, 26, 0.00, 'GCash QR', NULL, '2025-10-20 11:00:56', NULL, NULL, 'gcash', 50, 306.00, '', '2025-10-20 11:00:56'),
(15, 27, 0.00, 'GCash QR', NULL, '2025-10-20 22:29:55', NULL, NULL, 'gcash', 30, 183.60, '', '2025-10-21 10:01:17'),
(17, 28, 246.00, 'GCash QR', NULL, '2025-10-21 01:38:26', 1, '2025-10-26 03:57:09', 'gcash', 50, 318.88, 'VERIFIED', '2025-10-26 03:57:09'),
(19, 29, 12313.00, 'GCash QR', NULL, '2025-10-21 02:58:06', NULL, NULL, 'bpi', 50, 56.00, 'REJECTED', '2025-10-23 11:19:38'),
(22, 30, 123.00, 'GCash QR', NULL, '2025-10-21 03:02:07', NULL, NULL, 'bpi', 50, 56.00, 'REJECTED', '2025-10-23 11:19:18'),
(24, 31, 246.00, 'GCash QR', NULL, '2025-10-21 03:02:43', 1, '2025-10-22 12:50:59', 'gcash', 50, 56.00, 'VERIFIED', '2025-10-22 12:50:59'),
(26, 32, 246.00, 'GCash QR', NULL, '2025-10-21 03:03:46', 1, '2025-10-22 12:50:07', 'bpi', 50, 56.00, 'VERIFIED', '2025-10-22 12:50:07'),
(28, 33, 0.00, 'GCash QR', NULL, '2025-10-21 03:04:30', NULL, NULL, 'bpi', 50, 56.00, '', '2025-10-21 03:04:30'),
(29, 34, 246.00, 'GCash QR', NULL, '2025-10-21 03:09:04', 1, '2025-10-22 12:49:52', 'bpi', 50, 56.00, 'VERIFIED', '2025-10-22 12:49:52'),
(32, 35, 0.00, 'GCash QR', NULL, '2025-10-22 12:49:31', NULL, NULL, 'bpi', 100, 112.00, '', '2025-10-22 12:49:31');

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
(1, 1, 1, 3050.00, 3050.00, NULL, 'PAID', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-19 20:02:23', '2025-10-19 20:02:23'),
(2, 1, 2, 3050.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-19 20:02:23', '2025-10-19 20:02:23'),
(3, 26, 1, 306.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-20 11:00:56', '2025-10-20 11:00:56'),
(4, 26, 2, 306.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-20 11:00:56', '2025-10-20 11:00:56'),
(5, 27, 1, 306.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-20 22:29:55', '2025-10-20 22:29:55'),
(6, 27, 2, 306.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-20 22:29:55', '2025-10-20 22:29:55'),
(7, 28, 1, 318.88, 318.88, NULL, 'PAID', NULL, NULL, NULL, NULL, '2025-10-26 11:57:09', NULL, '2025-10-21 01:38:26', '2025-10-26 03:57:09'),
(8, 28, 2, 318.88, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 01:38:26', '2025-10-21 01:38:26'),
(9, 29, 1, 56.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 02:58:06', '2025-10-21 02:58:06'),
(10, 29, 2, 56.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 02:58:06', '2025-10-21 02:58:06'),
(11, 30, 1, 56.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:02:07', '2025-10-21 03:02:07'),
(12, 30, 2, 56.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:02:07', '2025-10-21 03:02:07'),
(13, 31, 1, 56.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:02:43', '2025-10-21 03:02:43'),
(14, 31, 2, 56.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:02:43', '2025-10-21 03:02:43'),
(15, 32, 1, 56.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:03:46', '2025-10-21 03:03:46'),
(16, 32, 2, 56.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:03:46', '2025-10-21 03:03:46'),
(17, 33, 1, 56.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:04:30', '2025-10-21 03:04:30'),
(18, 33, 2, 56.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:04:30', '2025-10-21 03:04:30'),
(19, 34, 1, 56.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:09:04', '2025-10-21 03:09:04'),
(20, 34, 2, 56.00, 0.00, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:09:04', '2025-10-21 03:09:04'),
(21, 35, 1, 112.00, 0.00, NULL, 'PENDING', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:49:31', '2025-10-22 12:49:31');

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
(1, 'gcash', 'uploads/qrs/gcash.png', 1),
(2, 'bpi', 'uploads/qrs/bpi.png', 1);

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_verifications`
--

INSERT INTO `payment_verifications` (`id`, `order_id`, `method`, `account_name`, `account_number`, `reference_number`, `amount_reported`, `screenshot_path`, `status`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `created_at`) VALUES
(1, 1, 'gcash', 'dascasdasd', '1231231231', 'd12231123', 123.00, 'uploads/payments/proof_1_1760842501.png', 'APPROVED', NULL, NULL, NULL, NULL, NULL, '2025-10-19 02:55:01'),
(2, 2, 'bpi', 'dascasdasd', '1231231231', 'd12231123', 123.00, 'uploads/payments/proof_2_1760842557.png', 'REJECTED', NULL, NULL, NULL, NULL, NULL, '2025-10-19 02:55:57'),
(3, 23, 'gcash', 'test1', 'test2', 'test123', 112.00, 'uploads/payments/proof_23_1760889566.jpg', 'APPROVED', NULL, NULL, NULL, NULL, NULL, '2025-10-19 15:59:26'),
(4, 24, 'gcash', 'teest', 'tset', 'testt', 612.00, 'uploads/payments/proof_24_1760891708.jpg', 'APPROVED', NULL, NULL, NULL, NULL, NULL, '2025-10-19 16:35:08'),
(5, 25, 'gcash', 'tset', 'tetset', 'teststs', 56.00, 'uploads/payments/proof_25_1760896011.png', 'APPROVED', NULL, NULL, NULL, NULL, NULL, '2025-10-19 17:46:51'),
(6, 28, 'gcash', 'sadas', 'dasdas', 'dasdas', 123.00, 'uploads/payments/proof_28_1761010719.png', 'APPROVED', 1, '2025-10-26 11:57:09', NULL, NULL, NULL, '2025-10-21 01:38:39'),
(7, 29, 'bpi', 'ds', 'das', 'da', 12313.00, 'uploads/payments/proof_29_1761015499.png', 'REJECTED', NULL, NULL, NULL, NULL, NULL, '2025-10-21 02:58:19'),
(8, 30, 'bpi', 'awit', '6969', '6969', 123.00, 'uploads/payments/proof_30_1761015751.jpeg', 'REJECTED', NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:02:31'),
(9, 31, 'gcash', 'dascasdasd', '1231231231', 'd12231123', 123.00, 'uploads/payments/proof_31_1761015774.png', 'APPROVED', NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:02:54'),
(10, 32, 'bpi', 'dascasdasd', '1231231231', '123', 123.00, 'uploads/payments/proof_32_1761015835.png', 'APPROVED', NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:03:55'),
(11, 34, 'bpi', 'dsad', 'asdas', 'ad123123', 123.00, 'uploads/payments/proof_34_1761016158.png', 'APPROVED', NULL, NULL, NULL, NULL, NULL, '2025-10-21 03:09:18');

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
  `image` varchar(255) DEFAULT NULL,
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
(10, 'test', 'Kitchen Cabinet', 'wawaawwa', 15000.00, 0, 'product_1760525903_68ef7e4f293a2.jpg', '', 'cm', 0, '2025-10-15 10:58:27', 1, 'released', '2025-10-17 01:59:00', 0, NULL),
(11, 'testsetsdsada', 'Office Cabinet', 'dadsdadadsad', 20000000.00, 0, 'product_1760525997_68ef7ead28c9f.jpg', '', 'cm', 0, '2025-10-15 11:00:01', 1, 'released', '2025-10-17 01:58:58', 0, NULL),
(12, 'dsdasdasdada', 'Kitchen Cabinet', 'dadsadawqdddada', 5000.00, 0, 'product_1760527224_68ef83788d9e3.png', '', 'cm', 0, '2025-10-15 11:20:28', 1, 'released', '2025-10-24 10:25:04', 0, NULL),
(13, 'test1', 'Office Cabinet', 'dsadasasdsa', 100.00, 0, 'product_1760531268_68ef93446993b.png', '', 'cm', 0, '2025-10-15 12:27:50', 1, 'released', '2025-10-16 19:08:24', 0, NULL),
(14, 'test', 'Wardrobe', 'testsss', 1000.00, 0, 'product_1761272746_68fae3aaaf08a.jpg', 'model_1761272983_68fae49775f0d.glb', 'cm', 1, '2025-10-20 11:45:28', 1, 'released', '2025-10-26 11:31:23', 0, NULL),
(15, 'tests', 'Wardrobe', 'test11', 10000.00, 0, 'product_1761469050_68fde27a80dc1.jpg', '', 'cm', 0, '2025-10-26 08:57:42', 1, 'released', '2025-10-26 17:05:54', 0, NULL);

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
(75, 14, 1, NULL, 0, 0, '2025-10-27 04:09:34');

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
(10, 14, 1, NULL, 0, 0, '2025-10-27 04:09:34');

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
(1, 12, 'width', 0.00, 300.00, 100.00, 1.00, 0.00, 'cm', '2025-10-15 11:27:37', 0.00, 0.00),
(2, 12, 'height', 0.00, 300.00, 100.00, 1.00, 0.00, 'cm', '2025-10-15 11:27:37', 0.00, 0.00),
(3, 12, 'depth', 0.00, 300.00, 100.00, 1.00, 0.00, 'cm', '2025-10-15 11:27:37', 0.00, 0.00),
(4, 13, 'width', 0.00, 300.00, 100.00, 1.00, 0.00, 'cm', '2025-10-15 12:28:09', 0.00, 0.00),
(5, 13, 'height', 0.00, 300.00, 100.00, 1.00, 0.00, 'cm', '2025-10-15 12:28:10', 0.00, 0.00),
(6, 13, 'depth', 0.00, 300.00, 100.00, 1.00, 0.00, 'cm', '2025-10-15 12:28:10', 0.00, 0.00),
(262, 14, 'width', 50.00, 300.00, 50.00, 1.00, 100.00, 'cm', '2025-10-27 04:09:33', 50.00, 1000.00),
(263, 14, 'height', 80.00, 300.00, 80.00, 1.00, 200.00, 'cm', '2025-10-27 04:09:33', 10.00, 250.00),
(264, 14, 'depth', 80.00, 300.00, 80.00, 1.00, 550.00, 'cm', '2025-10-27 04:09:33', 0.00, 0.00);

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
(62, 14, 1, NULL, 0, 0, '2025-10-27 04:09:33');

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
(34, 2, 'customer', 19, '[CLEARED BY CUSTOMER] hi', '2025-10-08 16:47:20'),
(35, 1, 'customer', 16, 'hi', '2025-10-08 17:03:12'),
(36, 1, 'customer', 16, 'hello', '2025-10-08 17:03:22'),
(37, 1, 'customer', 16, '??', '2025-10-08 17:03:24'),
(38, 2, 'customer', 19, '[CLEARED BY CUSTOMER] hi', '2025-10-08 17:06:15'),
(39, 3, 'customer', 17, 'hi!', '2025-10-08 17:08:17'),
(40, 3, 'customer', 17, 'eh>', '2025-10-08 17:08:21'),
(41, 1, 'customer', 16, 'hi', '2025-10-08 17:10:05'),
(42, 2, 'customer', 19, '[CLEARED BY CUSTOMER] hello', '2025-10-08 17:14:38'),
(43, 1, 'customer', 16, 'hi', '2025-10-08 21:24:43'),
(44, 1, 'customer', 16, 'heelo', '2025-10-08 21:24:55'),
(45, 1, 'customer', 16, 'lead times?', '2025-10-08 21:25:00'),
(46, 1, 'bot', NULL, 'Typical build time is 7–14 days depending on customization.', '2025-10-08 21:25:00'),
(47, 1, 'customer', 16, 'location?', '2025-10-08 21:25:04'),
(48, 1, 'customer', 16, 'located?', '2025-10-08 21:25:07'),
(49, 1, 'bot', NULL, 'We are located at Green Breeze, Piela, Dasmarinas Cavite.', '2025-10-08 21:25:08'),
(50, 1, 'customer', 16, 'custom', '2025-10-08 21:25:43'),
(51, 1, 'bot', NULL, 'Yes! Share your preferred dimensions and color; we will confirm feasibility and pricing.', '2025-10-08 21:25:43'),
(52, 2, 'customer', 19, 'hi', '2025-10-08 21:26:19'),
(53, 2, 'customer', 19, 'ok', '2025-10-08 21:26:23'),
(54, 2, 'customer', 19, 'lead tmes?', '2025-10-08 21:26:27'),
(55, 2, 'customer', 19, 'operating hours?', '2025-10-08 21:26:32'),
(56, 2, 'bot', NULL, 'We operate from 8am-5pm', '2025-10-08 21:26:33'),
(57, 3, 'customer', 17, 'hi', '2025-10-08 21:45:15'),
(58, 1, 'customer', 16, 'H', '2025-10-08 22:19:09'),
(59, 1, 'bot', NULL, 'Typical build time is 7–14 days depending on customization.', '2025-10-08 22:19:09'),
(60, 1, 'customer', 16, 'Hi', '2025-10-08 22:19:12'),
(61, 2, 'customer', 19, 'hey', '2025-10-08 22:23:17'),
(62, 2, 'customer', 19, 'located', '2025-10-08 22:23:28'),
(63, 2, 'bot', NULL, 'We are located at Green Breeze, Piela, Dasmarinas Cavite.', '2025-10-08 22:23:28'),
(64, 2, 'customer', 19, 'where', '2025-10-08 22:23:31'),
(65, 2, 'bot', NULL, 'We are located at Green Breeze, Piela, Dasmarinas Cavite.', '2025-10-08 22:23:31'),
(66, 2, 'customer', 19, 'what', '2025-10-08 22:23:33'),
(67, 2, 'bot', NULL, 'Typical build time is 7–14 days depending on customization.', '2025-10-08 22:23:33'),
(68, 2, 'customer', 19, 'when', '2025-10-08 22:23:35'),
(69, 2, 'customer', 19, 'how', '2025-10-08 22:23:38'),
(70, 2, 'customer', 19, 'custom', '2025-10-08 22:23:41'),
(71, 2, 'bot', NULL, 'Yes! Share your preferred dimensions and color; we will confirm feasibility and pricing.', '2025-10-08 22:23:41'),
(72, 2, 'customer', 19, 'what', '2025-10-08 22:23:49'),
(73, 2, 'bot', NULL, 'Typical build time is 7–14 days depending on customization.', '2025-10-08 22:23:49'),
(74, 2, 'customer', 19, 'operaeting hours', '2025-10-08 22:23:57'),
(75, 2, 'customer', 19, 'operating hours', '2025-10-08 22:24:02'),
(76, 2, 'bot', NULL, 'We operate from 8am-5pm', '2025-10-08 22:24:02'),
(77, 1, 'customer', 16, 'hi', '2025-10-09 09:36:10'),
(78, 2, 'customer', 19, 'location', '2025-10-09 09:37:43'),
(79, 2, 'customer', 19, 'location?', '2025-10-09 09:37:47'),
(80, 2, 'customer', 19, 'located?', '2025-10-09 09:37:50'),
(81, 2, 'bot', NULL, 'We are located at Green Breeze, Piela, Dasmarinas Cavite.', '2025-10-09 09:37:50'),
(82, 2, 'customer', 19, '1', '2025-10-09 09:38:10'),
(83, 1, 'admin', 1, 'hello', '2025-10-09 10:09:23'),
(84, 1, 'admin', 1, 'this is the admin', '2025-10-09 10:11:09'),
(85, 1, 'customer', 16, 'how are u admin', '2025-10-09 10:23:45'),
(86, 1, 'admin', 1, 'im fine', '2025-10-09 10:25:12'),
(87, 1, 'admin', 1, 'how about u how are u', '2025-10-09 10:26:23'),
(88, 1, 'customer', 16, 'delivery?', '2025-10-09 10:37:45'),
(89, 1, 'customer', 16, 'deliver', '2025-10-09 10:37:51'),
(90, 1, 'bot', NULL, 'Yes! We deliver within Cavite and nearby areas!', '2025-10-09 10:37:51'),
(91, 1, 'customer', 16, 'deliver?', '2025-10-09 10:37:56'),
(92, 1, 'bot', NULL, 'Yes! We deliver within Cavite and nearby areas!', '2025-10-09 10:37:57'),
(93, 2, 'admin', 1, 'hhi', '2025-10-09 10:39:28'),
(94, 3, 'customer', 17, 'hello??', '2025-10-09 10:43:48'),
(95, 3, 'customer', 17, 'deliver?', '2025-10-09 10:43:53'),
(96, 3, 'bot', NULL, 'Yes! We deliver within Cavite and nearby areas!', '2025-10-09 10:43:53'),
(97, 2, 'customer', 19, 'gege', '2025-10-09 10:44:26'),
(98, 2, 'customer', 19, 'deliver?', '2025-10-09 10:44:30'),
(99, 2, 'bot', NULL, 'Yes! We deliver within Cavite and nearby areas!', '2025-10-09 10:44:30'),
(100, 1, 'customer', 16, 'ey', '2025-10-09 10:45:03'),
(101, 2, 'customer', 19, 'lead times?', '2025-10-09 10:45:47'),
(102, 2, 'bot', NULL, 'Typical build time is 7–14 days depending on customization.', '2025-10-09 10:45:47'),
(103, 2, 'customer', 19, 'heyheyhey', '2025-10-09 10:45:50'),
(104, 1, 'customer', 16, 'OK', '2025-10-09 10:51:22'),
(105, 1, 'customer', 16, 'OK', '2025-10-09 10:52:40'),
(106, 1, 'customer', 16, 'deliver', '2025-10-09 11:05:40'),
(107, 1, 'customer', 16, 'deliver?', '2025-10-09 11:05:47'),
(108, 1, 'customer', 16, 'lead times?', '2025-10-09 11:05:52'),
(109, 3, 'customer', 17, 'deliver?', '2025-10-09 11:15:55'),
(110, 3, 'customer', 17, 'deliver', '2025-10-09 11:15:59'),
(111, 3, 'customer', 17, 'hi', '2025-10-09 11:26:54'),
(112, 2, 'customer', 19, 'hi', '2025-10-09 11:43:58'),
(113, 2, 'customer', 19, 'hello', '2025-10-09 11:44:04'),
(114, 1, 'customer', 16, 'lead times?', '2025-10-09 11:45:22'),
(115, 1, 'customer', 16, 'location?', '2025-10-09 11:45:28'),
(116, 1, 'customer', 16, 'deliver?', '2025-10-09 11:45:31'),
(117, 1, 'customer', 16, 'customize?', '2025-10-09 11:45:34'),
(118, 2, 'customer', 19, 'hello', '2025-10-09 11:46:09'),
(119, 3, 'customer', 17, 'hey', '2025-10-09 11:46:26'),
(120, 3, 'admin', 1, 'hi!', '2025-10-09 11:46:52'),
(121, 2, 'admin', 1, 'hello', '2025-10-09 11:46:55'),
(122, 1, 'admin', 1, 'hhi', '2025-10-09 11:46:58'),
(123, 1, 'customer', 16, 'hello', '2025-10-09 11:47:18'),
(124, 3, 'customer', 17, 'hello', '2025-10-09 11:47:31'),
(125, 2, 'customer', 19, 'deliver', '2025-10-09 11:47:44'),
(126, 2, 'customer', 19, 'lead times', '2025-10-09 11:47:48'),
(127, 3, 'admin', 1, 'hey', '2025-10-09 14:39:58'),
(128, 1, 'customer', 16, 'hi', '2025-10-10 13:08:18'),
(129, 1, 'customer', 16, 'ok', '2025-10-10 13:08:21'),
(130, 1, 'admin', 1, 'hello how can i help u', '2025-10-10 13:08:45'),
(131, 4, 'customer', 23, 'hi', '2025-10-10 14:07:18'),
(132, 4, 'admin', 3, 'hey', '2025-10-10 15:27:07'),
(133, 5, 'customer', 24, 'hehehe', '2025-10-10 15:28:54'),
(134, 5, 'admin', 1, 'ooooo', '2025-10-10 15:29:09'),
(135, 1, 'admin', 1, 'haahaaa', '2025-10-12 17:54:50'),
(136, 1, 'customer', 16, 'hoy', '2025-10-12 17:55:07'),
(137, 1, 'admin', 1, '?', '2025-10-12 17:58:36'),
(138, 1, 'customer', 16, 'gege', '2025-10-27 03:16:35');

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
(1, 'E12BBF23C765B137ECC6DFDDA9B6E419', 16, 'Moen Secapuri', NULL, NULL, 'open', '2025-10-27 11:16:36', '2025-10-08 16:33:13', '2025-10-27 11:18:52', '2025-10-13 01:56:35'),
(2, '57F84B08CC89FE1733CF3FA628487512', 19, 'vien santos', NULL, NULL, 'open', '2025-10-09 19:47:48', '2025-10-08 16:36:33', '2025-10-25 08:38:15', '2025-10-09 19:48:14'),
(3, '8A4C83FDA3C3E38DD3548FF63EC0EF06', 17, 'Moen Secapuri', NULL, NULL, 'open', '2025-10-09 22:39:58', '2025-10-08 17:08:12', '2025-10-25 08:38:14', '2025-10-09 19:16:21'),
(4, 'EFEE22638F01990688999B16CA09EEB8', 23, 'Vien Santos', NULL, NULL, 'open', '2025-10-10 23:27:07', '2025-10-10 14:07:16', '2025-10-25 08:38:16', NULL),
(5, '1D71C7F2C061F237DD7B2EDCF001569B', 24, 'Vien Santos', NULL, NULL, 'open', '2025-10-10 23:29:09', '2025-10-10 15:28:51', '2025-10-25 08:38:18', '2025-10-10 23:29:23'),
(6, '0FC92274A86149522300CAEC5BF3D4BF', 32, 'Vien Santos', NULL, NULL, 'open', '2025-10-18 21:08:53', '2025-10-18 13:08:53', '2025-10-25 08:38:19', NULL),
(7, '97369A6D963C38E5CAEBF7B90A1D7DC5', 1, 'System Owner44', NULL, NULL, 'open', '2025-10-23 19:24:22', '2025-10-23 11:24:22', '2025-10-25 08:38:35', NULL);

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
(223, 'about', 'About Us', '{\"about_hero_image\":\"/RADS-TOOLING/assets/images/store.jpg\",\"about_headline\":\"About Rads Tooling\",\"about_subheadline\":\"<p>Your trusted partner in precision tooling and industrial solutions</p>\",\"about_mission\":\"<p>To provide high-quality custom cabinets and tooling solutions that exceed customer expectations through superior craftsmanship, innovative design, and exceptional service.</p>\",\"about_vision\":\"<p>To be the leading cabinet manufacturer in Cavite, recognized for quality, reliability, and customer satisfaction.</p>\",\"about_story\":\"<p>Established in 2007, RADS Tooling has been serving customers for over 17 years. We started as a small workshop and have grown into a trusted name in custom cabinet manufacturing. Our commitment to quality and customer satisfaction has made us the preferred choice for homeowners and businesses alike.</p><p>Every cabinet we create is handcrafted by skilled artisans using premium materials and modern techniques. We combine traditional craftsmanship with innovative design to deliver products that stand the test of time.</p>\",\"about_address\":\"Green Breeze, Piela, Dasmariñas, Cavite\",\"about_phone\":\"+63 976 228 4270\",\"about_email\":\"radstooling@gmail.com\",\"about_hours_weekday\":\"Mon-Sat: 8:00 AM - 5:00 PM\",\"about_hours_sunday\":\"Sunday: Closed\",\"about_hero_path\":\"/RADS-TOOLING/assets/images/store.jpg\"}', 'draft', 9, 'System Owner', '2025-10-15 21:38:07', '2025-10-15 13:38:07'),
(224, 'about', 'About Us', '{\"about_hero_image\":\"/RADS-TOOLING/assets/images/store.jpg\",\"about_headline\":\"About Rads Tooling\",\"about_subheadline\":\"<p>Your trusted partner in precision tooling and industrial solutions</p>\",\"about_mission\":\"<p>To provide high-quality custom cabinets and tooling solutions that exceed customer expectations through superior craftsmanship, innovative design, and exceptional service.</p>\",\"about_vision\":\"<p>To be the leading cabinet manufacturer in Cavite, recognized for quality, reliability, and customer satisfaction.</p>\",\"about_story\":\"<p>Established in 2007, RADS Tooling has been serving customers for over 17 years. We started as a small workshop and have grown into a trusted name in custom cabinet manufacturing. Our commitment to quality and customer satisfaction has made us the preferred choice for homeowners and businesses alike.</p><p>Every cabinet we create is handcrafted by skilled artisans using premium materials and modern techniques. We combine traditional craftsmanship with innovative design to deliver products that stand the test of time.</p>\",\"about_address\":\"Green Breeze, Piela, Dasmariñas, Cavite\",\"about_phone\":\"+63 976 228 4270\",\"about_email\":\"radstooling@gmail.com\",\"about_hours_weekday\":\"Mon-Sat: 8:00 AM - 5:00 PM\",\"about_hours_sunday\":\"Sunday: Closed\",\"about_hero_path\":\"/RADS-TOOLING/assets/images/store.jpg\"}', 'published', 9, 'System Owner', '2025-10-15 21:38:09', '2025-10-15 13:38:09'),
(228, 'home_customer', 'Customer Homepage', '{\"welcome_message\":\"<h1>Welcome back, <span style=\\\"color: #2f5b88;\\\">{{customer_name}}</span>!</h1>\",\"intro_text\":\"<p>Explore our latest cabinet designs and continue your projectss</p>\",\"footer_email\":\"RadsTooling@gmail.com\",\"footer_phone\":\"+63 976 228 4270\",\"footer_address\":\"Green Breeze, Piela, Dasmariñas, Cavite\",\"footer_hours\":\"Mon-Sat: 8:00 AM - 5:00 PM\",\"welcome\":\"<h1>Welcome backs, <span style=\\\"color: #2f5b88;\\\">{{customer_name}}</span>!</h1>\",\"intro\":\"<p>Explore our latest cabinet designs and continue your projects</p>\",\"hero_image\":\"/RADS-TOOLING/assets/images/cabinet-hero.jpg\",\"cta_primary_text\":\"Start Designing\",\"cta_secondary_text\":\"Browse Products\",\"footer_description\":\"Premium custom cabinet manufacturer serving clients since 2007. Quality craftsmanship, affordable prices, and exceptional service.\",\"customer_hero_image\":\"/RADS-TOOLING/uploads/cms/customer/hero-cabinet_1760909601_d391178d23bb8eef.glb\"}', 'published', 7, 'System Owner', '2025-10-20 05:33:39', '2025-10-19 21:33:39'),
(234, 'privacy', 'Privacy Policy', '{\"content\":\"<h1>Privacy Policy</h1><p><em>Last updated: January 2024</em></p><p><br></p><h2>1. Introduction</h2><p>Welcome to RADS Tooling. We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains what information we collect, how we use it, and what rights you have in relation to it.</p><h2>2. Information We Collect</h2><h3>Personal Information</h3><p>We collect personal information that you voluntarily provide to us when you:</p><ul><li>Register for an account on our website</li><li>Place an order for our products</li><li>Request custom tooling solutions</li><li>Subscribe to our newsletter</li><li>Contact us for support or inquiries</li></ul><p>This information may include:</p><ul><li>Name and contact information (email, phone number, address)</li><li>Account credentials (username and password)</li><li>Payment information (credit card details, billing address)</li><li>Order history and preferences</li><li>Custom design specifications and requirements</li></ul><h3>Automatically Collected Information</h3><p>When you visit our website, we automatically collect certain information about your device, including:</p><ul><li>IP address and location data</li><li>Browser type and version</li><li>Device type and operating system</li><li>Pages visited and time spent on our site</li><li>Referring website or source</li></ul><h2>3. How We Use Your Information</h2><p>We use the information we collect to:</p><ul><li>Process and fulfill your orders</li><li>Provide customer support and respond to inquiries</li><li>Send order confirmations and shipping updates</li><li>Create and manage your account</li><li>Customize your experience on our website</li><li>Send marketing communications (with your consent)</li><li>Improve our products and services</li><li>Prevent fraud and enhance security</li><li>Comply with legal obligations</li></ul><h2>4. Cookies and Tracking Technologies</h2><p>We use cookies and similar tracking technologies to:</p><ul><li>Keep you logged in to your account</li><li>Remember your preferences and settings</li><li>Analyze website traffic and usage patterns</li><li>Improve website functionality and user experience</li><li>Deliver targeted advertisements (if applicable)</li></ul><p>You can control cookies through your browser settings. However, disabling cookies may limit some features of our website.</p><h2>5. Data Sharing and Disclosure</h2><p>We do not sell, trade, or rent your personal information to third parties. We may share your information with:</p><ul><li><strong>Service Providers:</strong>&nbsp;Third-party vendors who help us operate our business (e.g., payment processors, shipping companies, email service providers)</li><li><strong>Business Partners:</strong>&nbsp;Trusted partners for custom tooling projects (with your consent)</li><li><strong>Legal Requirements:</strong>&nbsp;When required by law or to protect our rights and safety</li><li><strong>Business Transfers:</strong>&nbsp;In connection with a merger, acquisition, or sale of assets</li></ul><h2>6. Data Security</h2><p>We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p><ul><li>Encryption of sensitive data in transit and at rest</li><li>Regular security assessments and updates</li><li>Limited access to personal information on a need-to-know basis</li><li>Employee training on data protection and privacy</li></ul><p>However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.</p><h2>7. Your Rights and Choices</h2><p>You have the right to:</p><ul><li><strong>Access:</strong>&nbsp;Request a copy of your personal information</li><li><strong>Correction:</strong>&nbsp;Update or correct inaccurate information</li><li><strong>Deletion:</strong>&nbsp;Request deletion of your personal information</li><li><strong>Opt-out:</strong>&nbsp;Unsubscribe from marketing communications</li><li><strong>Data Portability:</strong>&nbsp;Request your data in a portable format</li><li><strong>Withdraw Consent:</strong>&nbsp;Withdraw consent for data processing where applicable</li></ul><p>To exercise these rights, please contact us using the information provided below.</p><h2>8. Children\'s Privacy</h2><p>Our services are not intended for individuals under the age of 18. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.</p><h2>9. International Data Transfers</h2><p>Your information may be transferred to and processed in countries other than your country of residence. These countries may have different data protection laws. We ensure appropriate safeguards are in place to protect your information in accordance with this Privacy Policy.</p><h2>10. Changes to This Policy</h2><p>We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements. We will notify you of any material changes by posting the new Privacy Policy on this page and updating the \\\"Last updated\\\" date.</p><h2>11. Contact Us</h2><p>If you have questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us at:</p><ul><li><strong>Email:</strong>&nbsp;RadsTooling@gmail.com</li><li><strong>Phone:</strong>&nbsp;+63 (976) 228-4270</li><li><strong>Address:</strong>&nbsp;Green Breeze, Piela, Dasmariñas, Cavite, Philippines</li></ul><h2>12. Consent</h2><p>By using our website and services, you consent to the collection and use of your information as described in this Privacy Policy.</p>\"}', 'draft', 13, 'System Owner', '2025-10-20 08:32:00', '2025-10-20 00:32:00'),
(235, 'privacy', 'Privacy Policy', '{\"content\":\"<h1>Privacy Policy</h1><p><em>Last updated: January 2024</em></p><p><br></p><h2>1. Introduction</h2><p>Welcome to RADS Tooling. We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains what information we collect, how we use it, and what rights you have in relation to it.</p><h2>2. Information We Collect</h2><h3>Personal Information</h3><p>We collect personal information that you voluntarily provide to us when you:</p><ul><li>Register for an account on our website</li><li>Place an order for our products</li><li>Request custom tooling solutions</li><li>Subscribe to our newsletter</li><li>Contact us for support or inquiries</li></ul><p>This information may include:</p><ul><li>Name and contact information (email, phone number, address)</li><li>Account credentials (username and password)</li><li>Payment information (credit card details, billing address)</li><li>Order history and preferences</li><li>Custom design specifications and requirements</li></ul><h3>Automatically Collected Information</h3><p>When you visit our website, we automatically collect certain information about your device, including:</p><ul><li>IP address and location data</li><li>Browser type and version</li><li>Device type and operating system</li><li>Pages visited and time spent on our site</li><li>Referring website or source</li></ul><h2>3. How We Use Your Information</h2><p>We use the information we collect to:</p><ul><li>Process and fulfill your orders</li><li>Provide customer support and respond to inquiries</li><li>Send order confirmations and shipping updates</li><li>Create and manage your account</li><li>Customize your experience on our website</li><li>Send marketing communications (with your consent)</li><li>Improve our products and services</li><li>Prevent fraud and enhance security</li><li>Comply with legal obligations</li></ul><h2>4. Cookies and Tracking Technologies</h2><p>We use cookies and similar tracking technologies to:</p><ul><li>Keep you logged in to your account</li><li>Remember your preferences and settings</li><li>Analyze website traffic and usage patterns</li><li>Improve website functionality and user experience</li><li>Deliver targeted advertisements (if applicable)</li></ul><p>You can control cookies through your browser settings. However, disabling cookies may limit some features of our website.</p><h2>5. Data Sharing and Disclosure</h2><p>We do not sell, trade, or rent your personal information to third parties. We may share your information with:</p><ul><li><strong>Service Providers:</strong>&nbsp;Third-party vendors who help us operate our business (e.g., payment processors, shipping companies, email service providers)</li><li><strong>Business Partners:</strong>&nbsp;Trusted partners for custom tooling projects (with your consent)</li><li><strong>Legal Requirements:</strong>&nbsp;When required by law or to protect our rights and safety</li><li><strong>Business Transfers:</strong>&nbsp;In connection with a merger, acquisition, or sale of assets</li></ul><h2>6. Data Security</h2><p>We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p><ul><li>Encryption of sensitive data in transit and at rest</li><li>Regular security assessments and updates</li><li>Limited access to personal information on a need-to-know basis</li><li>Employee training on data protection and privacy</li></ul><p>However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.</p><h2>7. Your Rights and Choices</h2><p>You have the right to:</p><ul><li><strong>Access:</strong>&nbsp;Request a copy of your personal information</li><li><strong>Correction:</strong>&nbsp;Update or correct inaccurate information</li><li><strong>Deletion:</strong>&nbsp;Request deletion of your personal information</li><li><strong>Opt-out:</strong>&nbsp;Unsubscribe from marketing communications</li><li><strong>Data Portability:</strong>&nbsp;Request your data in a portable format</li><li><strong>Withdraw Consent:</strong>&nbsp;Withdraw consent for data processing where applicable</li></ul><p>To exercise these rights, please contact us using the information provided below.</p><h2>8. Children\'s Privacy</h2><p>Our services are not intended for individuals under the age of 18. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.</p><h2>9. International Data Transfers</h2><p>Your information may be transferred to and processed in countries other than your country of residence. These countries may have different data protection laws. We ensure appropriate safeguards are in place to protect your information in accordance with this Privacy Policy.</p><h2>10. Changes to This Policy</h2><p>We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements. We will notify you of any material changes by posting the new Privacy Policy on this page and updating the \\\"Last updated\\\" date.</p><h2>11. Contact Us</h2><p>If you have questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us at:</p><ul><li><strong>Email:</strong>&nbsp;RadsTooling@gmail.com</li><li><strong>Phone:</strong>&nbsp;+63 (976) 228-4270</li><li><strong>Address:</strong>&nbsp;Green Breeze, Piela, Dasmariñas, Cavite, Philippines</li></ul><h2>12. Consent</h2><p>By using our website and services, you consent to the collection and use of your information as described in this Privacy Policy.</p>\"}', 'published', 13, 'System Owner', '2025-10-20 08:32:03', '2025-10-20 00:32:03'),
(241, 'home_public', 'Public Homepage', '{\"hero_headline\":\"<h1><span style=\\\"color: rgb(47, 91, 136);\\\">C</span>ustomize Your Dream Cabinets</h1>\",\"hero_subtitle\":\"<p>Design, visualize, and order premium custom cabinets online. Choose your style, materials, and finishes with our 360° preview tool.</p>\",\"hero_image\":\"/RADS-TOOLING/uploads/cms/hero/hero-cabinet_1761047475_8c0c71_1761535587_dfa336560e8a3ef0.glb\",\"features_title\":\"<h2>Why Choose <span style=\\\"color: #667eea;\\\">RADS TOOLING</span>?</h2>\",\"features_subtitle\":\"<p>Everything you need to create your perfect cabinet</p>\",\"carousel_images\":[{\"image\":\"/RADS-TOOLING/assets/images/cab4.jpg\",\"title\":\"Bathroom Vanity\",\"description\":\"Water-resistant premium materials\"},{\"image\":\"/RADS-TOOLING/assets/images/cab3.jpg\",\"title\":\"Living Room Display\",\"description\":\"Showcase your style with custom shelving\"},{\"image\":\"/RADS-TOOLING/assets/images/cab1.jpg\",\"title\":\"Modern Kitchen\",\"description\":\"Contemporary design with premium finishes\"},{\"image\":\"/RADS-TOOLING/assets/images/cab5.jpg\",\"title\":\"Office Storage\",\"description\":\"Professional workspace solutions\"},{\"image\":\"/RADS-TOOLING/assets/images/cab2.jpg\",\"title\":\"Bedroom Wardrobe\",\"description\":\"Spacious storage with elegant styling\"}],\"video_title\":\"<h2><span style=\\\"color: #2f5b88;\\\">C</span>rafted with Passion &amp; Precision</h2>\",\"video_subtitle\":\"<p>Every cabinet is handcrafted by skilled artisans using premium materials. Watch our craftsmen bring your vision to life.</p>\",\"video_url\":\"/RADS-TOOLING/assets/videos/crafting.mp4\",\"video_poster\":\"/RADS-TOOLING/assets/images/video-poster.jpg\",\"cta_headline\":\"<h2>Ready to Design Your Dream Cabinet?</h2>\",\"cta_text\":\"<p>Join hundreds of satisfied customers who transformed their spaces</p>\",\"footer_company\":\"RADS TOOLING\",\"footer_description\":\"Premium custom cabinet manufacturer serving clients since 2007. Quality craftsmanship, affordable prices, and exceptional service.\",\"footer_address\":\"Green Breeze, Piela, Dasmariñas, Cavite\",\"footer_email\":\"RadsTooling@gmail.com\",\"footer_phone\":\"+63 976 228 4270\",\"footer_hours\":\"Mon-Sat: 8:00 AM - 5:00 PM\",\"footer_copyright\":\"© 2025 RADS TOOLING INC. All rights reserved.\"}', 'draft', 23, 'System Owner44', '2025-10-27 11:26:29', '2025-10-27 03:26:29'),
(242, 'home_public', 'Public Homepage', '{\"hero_headline\":\"<h1><span style=\\\"color: rgb(47, 91, 136);\\\">C</span>ustomize Your Dream Cabinets</h1>\",\"hero_subtitle\":\"<p>Design, visualize, and order premium custom cabinets online. Choose your style, materials, and finishes with our 360° preview tool.</p>\",\"hero_image\":\"/RADS-TOOLING/uploads/cms/hero/hero-cabinet_1761047475_8c0c71_1761535587_dfa336560e8a3ef0.glb\",\"features_title\":\"<h2>Why Choose <span style=\\\"color: #667eea;\\\">RADS TOOLING</span>?</h2>\",\"features_subtitle\":\"<p>Everything you need to create your perfect cabinet</p>\",\"carousel_images\":[{\"image\":\"/RADS-TOOLING/assets/images/cab4.jpg\",\"title\":\"Bathroom Vanity\",\"description\":\"Water-resistant premium materials\"},{\"image\":\"/RADS-TOOLING/assets/images/cab3.jpg\",\"title\":\"Living Room Display\",\"description\":\"Showcase your style with custom shelving\"},{\"image\":\"/RADS-TOOLING/assets/images/cab1.jpg\",\"title\":\"Modern Kitchen\",\"description\":\"Contemporary design with premium finishes\"},{\"image\":\"/RADS-TOOLING/assets/images/cab5.jpg\",\"title\":\"Office Storage\",\"description\":\"Professional workspace solutions\"},{\"image\":\"/RADS-TOOLING/assets/images/cab2.jpg\",\"title\":\"Bedroom Wardrobe\",\"description\":\"Spacious storage with elegant styling\"}],\"video_title\":\"<h2><span style=\\\"color: #2f5b88;\\\">C</span>rafted with Passion &amp; Precision</h2>\",\"video_subtitle\":\"<p>Every cabinet is handcrafted by skilled artisans using premium materials. Watch our craftsmen bring your vision to life.</p>\",\"video_url\":\"/RADS-TOOLING/assets/videos/crafting.mp4\",\"video_poster\":\"/RADS-TOOLING/assets/images/video-poster.jpg\",\"cta_headline\":\"<h2>Ready to Design Your Dream Cabinet?</h2>\",\"cta_text\":\"<p>Join hundreds of satisfied customers who transformed their spaces</p>\",\"footer_company\":\"RADS TOOLING\",\"footer_description\":\"Premium custom cabinet manufacturer serving clients since 2007. Quality craftsmanship, affordable prices, and exceptional service.\",\"footer_address\":\"Green Breeze, Piela, Dasmariñas, Cavite\",\"footer_email\":\"RadsTooling@gmail.com\",\"footer_phone\":\"+63 976 228 4270\",\"footer_hours\":\"Mon-Sat: 8:00 AM - 5:00 PM\",\"footer_copyright\":\"© 2025 RADS TOOLING INC. All rights reserved.\"}', 'published', 23, 'System Owner44', '2025-10-27 11:26:35', '2025-10-27 03:26:35');

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
(12, 'What are your operating hours?', 'We operate from 8am-5pm', 1, '2025-10-07 14:24:18', '2025-10-07 17:16:12'),
(14, 'Do you deliver?', 'Yes! We deliver within Cavite and nearby areas!', 1, '2025-10-09 10:27:33', '2025-10-25 00:38:27');

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
(1, 'Oak Wood', 'WOOD_OAK', 'texture_1761470168_68fde6d8425c1.jpg', '[]', 500.00, 'wood', 1, '2025-10-26 09:16:11'),
(2, 'Oak Wood', 'WOOD_OAK', 'texture_1761525638_68febf86dcd07.jpg', '[]', 500.00, 'test', 1, '2025-10-27 00:40:41');

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
(1, 1, 'door'),
(2, 2, 'body');

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
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD KEY `idx_customer_id` (`customer_id`);

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
  ADD KEY `idx_item_type` (`item_type`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `handle_types`
--
ALTER TABLE `handle_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `order_addresses`
--
ALTER TABLE `order_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `order_completions`
--
ALTER TABLE `order_completions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `payment_installments`
--
ALTER TABLE `payment_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payment_qr`
--
ALTER TABLE `payment_qr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_verifications`
--
ALTER TABLE `payment_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `product_handles`
--
ALTER TABLE `product_handles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_size_config`
--
ALTER TABLE `product_size_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=265;

--
-- AUTO_INCREMENT for table `product_textures`
--
ALTER TABLE `product_textures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `product_texture_parts`
--
ALTER TABLE `product_texture_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rt_chat_messages`
--
ALTER TABLE `rt_chat_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `rt_chat_threads`
--
ALTER TABLE `rt_chat_threads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `rt_cms_pages`
--
ALTER TABLE `rt_cms_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT for table `rt_faqs`
--
ALTER TABLE `rt_faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `textures`
--
ALTER TABLE `textures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `texture_allowed_parts`
--
ALTER TABLE `texture_allowed_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
