-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 20, 2026 at 06:44 AM
-- Server version: 8.4.7
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `support_ticket`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
CREATE TABLE IF NOT EXISTS `branches` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `branch_code` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `branch_code`, `created_at`, `updated_at`) VALUES
(1, 'Kuti', 1, NULL, NULL),
(2, 'Dharkhar', 2, NULL, NULL),
(3, 'Chargas', 3, NULL, NULL),
(4, 'Head Office', 9999, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `assign_role_ids` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `assign_role_ids`, `created_at`, `updated_at`) VALUES
(1, 'Software Support', '1', NULL, NULL),
(2, 'Hardware Support', '2', NULL, NULL),
(3, 'Email or Outlook Support', '1', NULL, NULL),
(4, 'General Inquiry', '2', NULL, NULL),
(6, 'test1', '3', '2026-02-23 22:47:00', '2026-02-23 22:47:00'),
(7, 'test3', '1', '2026-03-10 22:39:25', '2026-03-10 22:39:25'),
(8, 'jjk', '1', '2026-04-13 03:36:26', '2026-04-13 03:36:26');

-- --------------------------------------------------------

--
-- Table structure for table `category_engineer_map`
--

DROP TABLE IF EXISTS `category_engineer_map`;
CREATE TABLE IF NOT EXISTS `category_engineer_map` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_engineer_map_category_id_user_id_unique` (`category_id`,`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category_engineer_map`
--

INSERT INTO `category_engineer_map` (`id`, `category_id`, `user_id`, `created_at`, `updated_at`) VALUES
(38, 4, 6, '2026-04-13 07:07:53', '2026-04-13 07:07:53'),
(36, 3, 1, '2026-04-13 07:07:36', '2026-04-13 07:07:36'),
(21, 2, 15, '2026-03-29 22:07:53', '2026-03-29 22:07:53'),
(22, 2, 16, '2026-03-29 22:07:58', '2026-03-29 22:07:58'),
(33, 1, 4, '2026-04-13 05:53:58', '2026-04-13 05:53:58'),
(37, 4, 4, '2026-04-13 07:07:47', '2026-04-13 07:07:47'),
(35, 3, 4, '2026-04-13 07:07:33', '2026-04-13 07:07:33'),
(31, 1, 1, '2026-04-13 05:53:43', '2026-04-13 05:53:43'),
(32, 1, 6, '2026-04-13 05:53:51', '2026-04-13 05:53:51'),
(41, 2, 1, '2026-04-16 03:31:53', '2026-04-16 03:31:53'),
(34, 3, 6, '2026-04-13 07:07:29', '2026-04-13 07:07:29'),
(39, 4, 1, '2026-04-13 07:07:58', '2026-04-13 07:07:58');

-- --------------------------------------------------------

--
-- Table structure for table `engineer_category_mappings`
--

DROP TABLE IF EXISTS `engineer_category_mappings`;
CREATE TABLE IF NOT EXISTS `engineer_category_mappings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_02_16_000000_create_category_engineer_hierarchy_table', 1),
(2, '2026_02_15_120000_create_category_engineer_hierarchy_table', 2),
(3, '2026_02_15_120100_add_assigned_hierarchy_to_tickets_table', 3),
(4, '2026_02_15_120200_create_ticket_forward_logs_table', 4),
(5, '2026_03_11_000000_create_category_engineer_map_table', 5),
(6, '2026_03_11_000100_update_category_engineer_map_allow_multiple_categories', 6),
(7, '2026_02_16_000000_add_solved_message_to_tickets_table', 7),
(8, '2026_02_17_000000_drop_unused_tables', 8),
(9, '2026_03_30_000000_create_ticket_attachments_table', 9),
(10, '2014_10_12_100000_create_password_resets_table', 10),
(11, '2019_08_19_000000_create_failed_jobs_table', 10),
(12, '2026_03_30_100000_add_handoff_note_to_tickets_table', 11);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priorities`
--

DROP TABLE IF EXISTS `priorities`;
CREATE TABLE IF NOT EXISTS `priorities` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `priorities_name_unique` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `priorities`
--

INSERT INTO `priorities` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Low', NULL, NULL),
(2, 'Medium', NULL, NULL),
(3, 'High', NULL, NULL),
(4, 'Urgent', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Admin', NULL, NULL),
(2, 'Engineer', NULL, NULL),
(3, 'Branch', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sub_categories`
--

DROP TABLE IF EXISTS `sub_categories`;
CREATE TABLE IF NOT EXISTS `sub_categories` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sub_categories_category_id_index` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_categories`
--

INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Installation/Setup', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(2, 1, 'Mobile Apps Related Problem', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(3, 1, 'CDIP EYE Issues', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(4, 1, 'Smart Move Issues', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(5, 1, 'Planning Software Issues', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(6, 1, 'HRM Leave/ Visit Issues', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(7, 2, 'Laptop/Desktop Issues', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(8, 2, 'Printer Problems', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(9, 2, 'Network Device Issues', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(10, 2, 'Peripheral Issues', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(11, 2, 'Hardware Replacement', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(12, 2, 'Power/Battery Issues', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(13, 3, 'Login Issues', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(14, 3, 'Send/Receive Errors', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(15, 3, 'Mailbox Full', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(16, 3, 'Outlook Setup', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(17, 3, 'Sync Problems', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(18, 3, 'Rules/Filters Issues', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(19, 4, 'Access Requests', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(20, 4, 'Password Reset Guidance', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(21, 4, 'Network Connectivity Questions', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(22, 4, 'VPN/Remote Access Questions', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(23, 4, 'System/Service Availability', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(24, 4, 'IT Policy/Security Questions', '2025-11-16 16:13:00', '2025-11-16 16:13:00'),
(25, 8, 'test category', '2026-02-16 00:24:54', '2026-04-13 03:52:24'),
(26, 4, 'test 000 sub', '2026-02-16 00:29:04', '2026-02-16 00:29:04');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` int DEFAULT '0',
  `priority_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `sub_category_id` int DEFAULT NULL,
  `assigned_to` int DEFAULT NULL,
  `handoff_note` text COLLATE utf8mb4_general_ci,
  `assigned_hierarchy` int UNSIGNED DEFAULT NULL,
  `solved_by` varchar(333) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `solved_message` text COLLATE utf8mb4_general_ci,
  `contact_person` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attachment` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `subject`, `description`, `status`, `priority_id`, `category_id`, `sub_category_id`, `assigned_to`, `handoff_note`, `assigned_hierarchy`, `solved_by`, `solved_message`, `contact_person`, `attachment`, `created_at`, `updated_at`) VALUES
(1, 1, 'test subj 1', 'test desc 1', 0, 2, 2, 7, 11, NULL, NULL, NULL, NULL, 'test contact 207', NULL, '2025-11-21 13:24:36', '2025-11-22 11:37:34'),
(2, 1, 'test subj 2', 'test desc 2', 0, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 'test contact 453', NULL, '2025-11-21 13:24:36', '2025-11-21 13:24:36'),
(3, 1, 'not printing', 'page stuck', 0, NULL, 2, 8, NULL, NULL, NULL, NULL, NULL, '461', NULL, '2025-11-28 12:50:10', '2025-11-28 12:50:10'),
(4, 1, 'achv issues', 'issues new entry', 0, NULL, 1, 5, 15, NULL, NULL, NULL, NULL, 'p mondal 455', NULL, '2025-12-12 06:31:44', '2025-12-12 14:42:02'),
(5, 1, 'test role', 'test role id', 0, NULL, 1, 3, 14, NULL, NULL, NULL, NULL, '1111', NULL, '2025-12-12 07:30:26', '2025-12-12 14:41:00'),
(6, 1, 'test hrd assign to', 'test hrd assign totest hrd assign to', 0, NULL, 2, 11, 15, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 07:34:13', '2025-12-12 07:34:13'),
(7, 1, 'app related issue', 'software issue', 0, NULL, 1, 2, 12, NULL, NULL, NULL, NULL, '1111', NULL, '2025-12-12 07:34:45', '2025-12-12 14:41:25'),
(8, 1, 'test innn', 'nafis', 2, NULL, 1, 3, 12, NULL, NULL, '12', NULL, '01313030453', NULL, '2026-02-09 03:31:04', '2026-02-09 03:34:39'),
(9, 1, 'mb damage', 'pc not opening', 0, NULL, 2, 10, 15, NULL, NULL, NULL, NULL, '01313030461', NULL, '2026-02-09 03:31:49', '2026-02-09 03:31:49'),
(10, 1, 'not printing', 'not printing', 0, NULL, 2, 8, 15, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-09 03:45:50', '2026-02-09 03:45:50'),
(11, 4, 'as', 'as', 2, NULL, 1, 2, 11, NULL, NULL, '11', NULL, '098', NULL, '2026-02-16 03:40:41', '2026-02-16 03:41:55'),
(12, 4, '12', '12', 0, NULL, 2, 11, 15, NULL, NULL, NULL, NULL, '12', NULL, '2026-02-16 04:04:17', '2026-02-16 04:04:17'),
(13, 1, 'ads', 'asdf', 0, NULL, 2, 7, 15, NULL, NULL, NULL, NULL, 'bl', NULL, '2026-02-22 22:29:16', '2026-02-22 22:29:16'),
(14, 1, 'asd', 'asd', 0, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, 'bf', NULL, '2026-02-22 22:29:53', '2026-02-22 22:29:53'),
(15, 1, '12', '12', 0, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, '12', NULL, '2026-02-22 22:38:44', '2026-02-22 22:38:44'),
(16, 1, 'dfd', 'dfd', 0, NULL, 1, 6, 10, NULL, NULL, NULL, NULL, '45', NULL, '2026-02-22 23:01:14', '2026-02-22 23:01:14'),
(17, 1, 'iii', 'iiii', 0, NULL, 2, 9, 15, NULL, NULL, NULL, NULL, '78', NULL, '2026-02-22 23:02:10', '2026-02-22 23:02:10'),
(18, 1, 'sdf', 'dsf', 0, NULL, 3, 13, NULL, NULL, NULL, NULL, NULL, 'df', NULL, '2026-02-22 23:13:49', '2026-02-22 23:13:49'),
(19, 1, 'gg', 'gg', 0, NULL, 3, 16, 10, NULL, NULL, NULL, NULL, 'gg', NULL, '2026-02-22 23:40:23', '2026-02-22 23:40:23'),
(20, 1, 'df', 'df', 0, NULL, 4, 23, 15, NULL, NULL, NULL, NULL, 'df', NULL, '2026-02-22 23:41:00', '2026-02-22 23:41:00'),
(21, 1, '23', '23', 0, NULL, 1, 1, 10, NULL, NULL, NULL, NULL, '23', NULL, '2026-02-23 01:14:06', '2026-02-23 01:14:06'),
(22, 2, '444', '444', 0, NULL, 1, 3, 10, NULL, NULL, NULL, NULL, '444', NULL, '2026-02-23 21:19:03', '2026-02-23 21:19:03'),
(23, 2, '11', '11', 0, NULL, 1, 3, 14, NULL, NULL, NULL, NULL, '11', NULL, '2026-02-23 22:39:23', '2026-02-23 22:39:23'),
(24, 2, 'adsf', 'adsf', 0, NULL, 2, 11, 15, NULL, NULL, NULL, NULL, 'asdf', NULL, '2026-02-23 23:36:36', '2026-02-23 23:36:36'),
(25, 2, 'asdf', 'adsf', 0, NULL, 1, 3, 10, NULL, NULL, NULL, NULL, 'asdf', NULL, '2026-02-23 23:37:39', '2026-02-23 23:37:39'),
(26, 2, '12', '12', 0, NULL, 1, 6, 10, NULL, NULL, NULL, NULL, '12', NULL, '2026-02-24 00:11:48', '2026-02-24 00:11:48'),
(27, 2, '444', 'uyhgvbhil', 0, NULL, 2, 7, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 01:47:34', '2026-02-24 01:47:34'),
(28, 2, 'mnnjn', 'lkkjmooo', 0, NULL, 1, 6, 10, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 01:47:50', '2026-02-25 21:31:09'),
(29, 3, '12', '12', 0, NULL, 2, 7, 14, NULL, NULL, NULL, NULL, '12', NULL, '2026-02-25 21:40:17', '2026-02-25 21:45:03'),
(30, 3, '13', '13', 1, NULL, 1, 3, 12, NULL, NULL, NULL, NULL, '13', NULL, '2026-02-25 21:46:17', '2026-03-01 22:58:09'),
(31, 2, 'nm', 'nm', 0, NULL, 1, 3, 4, NULL, 1, NULL, NULL, 'nm', NULL, '2026-03-09 22:11:39', '2026-03-09 22:11:39'),
(32, 2, 'gg', 'gg', 0, NULL, 1, 6, 14, NULL, 1, NULL, NULL, 'gg', NULL, '2026-03-09 22:12:56', '2026-03-09 22:12:56'),
(33, 2, 'tt', 'tt', 0, NULL, 1, 3, 4, NULL, 1, NULL, NULL, 'tt', NULL, '2026-03-09 22:25:22', '2026-03-09 22:25:22'),
(34, 2, '66', '66', 1, NULL, 1, 6, 11, NULL, 2, NULL, NULL, '66', NULL, '2026-03-09 22:30:56', '2026-03-09 22:45:52'),
(35, 2, 'hh', 'hh', 0, NULL, 1, 1, 14, NULL, 2, NULL, NULL, 'hh', NULL, '2026-03-09 22:46:11', '2026-03-09 22:48:55'),
(36, 2, 'jj', 'jj', 2, NULL, 2, 7, 15, NULL, 2, '15', NULL, 'jj', NULL, '2026-03-09 22:50:27', '2026-03-09 22:51:27'),
(37, 2, '12', '12', 0, NULL, 1, 3, 4, NULL, 1, NULL, NULL, '12', NULL, '2026-03-10 01:08:05', '2026-03-10 01:08:05'),
(38, 2, '44', '44', 2, NULL, 1, 3, 14, NULL, 2, '14', NULL, '44', NULL, '2026-03-10 01:10:49', '2026-03-10 01:12:15'),
(39, 2, '33', '33', 2, NULL, 1, 3, 14, NULL, 2, '14', NULL, '33', NULL, '2026-03-10 02:26:34', '2026-03-10 02:27:52'),
(40, 2, 'gg', 'gg', 2, NULL, 1, 3, 4, NULL, 1, '4', NULL, 'gg', NULL, '2026-03-10 02:32:43', '2026-03-10 02:40:30'),
(41, 2, 'ty', 'ty', 0, NULL, 1, 5, 4, NULL, 1, NULL, NULL, 'ty', NULL, '2026-03-10 02:40:54', '2026-03-10 02:40:54'),
(42, 3, 'vv', 'vv', 2, NULL, 1, 2, 10, NULL, 3, '10', NULL, 'vv', NULL, '2026-03-10 22:11:41', '2026-03-10 22:24:44'),
(43, 3, '11', '11', 2, NULL, 1, 3, 1, NULL, 1, '1', NULL, '11', NULL, '2026-03-10 22:47:53', '2026-03-10 22:55:38'),
(44, 2, 'tt', 'tt', 0, NULL, 1, 3, 10, NULL, NULL, NULL, NULL, 'tt', NULL, '2026-03-11 02:07:09', '2026-03-11 02:07:09'),
(45, 2, '12', '12', 0, NULL, 1, 6, 12, NULL, NULL, NULL, NULL, '12', NULL, '2026-03-11 02:25:58', '2026-03-11 21:40:39'),
(46, 2, 'zz', 'zz', 0, NULL, 1, 1, 14, NULL, NULL, NULL, NULL, 'zz', NULL, '2026-03-11 21:43:40', '2026-03-11 23:14:12'),
(47, 2, ',,', ',,', 2, NULL, 1, 5, 4, NULL, NULL, '4', NULL, ',,', NULL, '2026-03-11 23:16:10', '2026-03-11 23:18:03'),
(48, 2, '66', '66', 1, 1, 2, 11, 4, NULL, NULL, NULL, NULL, '66', NULL, '2026-03-11 23:23:02', '2026-03-29 21:19:30'),
(49, 2, 'zx', 'zx', 2, NULL, 1, 3, 14, NULL, NULL, '14', 'Done', 'zx', NULL, '2026-03-12 00:24:31', '2026-03-12 01:04:40'),
(50, 4, '77', '77', 2, NULL, 2, 9, 16, NULL, NULL, '16', 'uuuuuuu', '77', NULL, '2026-03-12 01:15:33', '2026-03-12 03:03:20'),
(51, 2, '45', '45', 2, NULL, 2, 11, 12, NULL, NULL, '12', 'problem solved', '45', NULL, '2026-03-15 00:02:24', '2026-03-16 00:39:27'),
(52, 1, 'ff', 'ff', 2, 3, 1, 6, 14, NULL, NULL, '14', 'asd', 'ff', NULL, '2026-03-16 00:42:38', '2026-03-27 06:23:23'),
(53, 1, '123', '123', 0, 4, 1, 6, NULL, NULL, NULL, NULL, NULL, '123', NULL, '2026-03-16 01:55:20', '2026-03-16 01:55:20'),
(54, 1, 'bb', 'bb', 2, NULL, 1, 1, 4, NULL, NULL, '4', 'done .', 'bb', NULL, '2026-03-27 05:19:36', '2026-03-27 05:34:08'),
(55, 2, 'ff', 'ff', 0, NULL, 2, 11, NULL, NULL, NULL, NULL, NULL, 'ff', 'tickets/nqBS5ICdE6tBbSWk7dAPnUlvr24izekL1XHugQt7.jpg', '2026-03-29 21:22:52', '2026-03-29 21:22:52'),
(56, 2, 'bn', 'bn', 1, NULL, 2, 7, 15, 'tk', NULL, NULL, NULL, 'bn', NULL, '2026-03-29 21:32:52', '2026-03-30 00:56:18'),
(57, 2, 'sd', 'sd', 1, NULL, 1, 1, 1, 'asdf', NULL, NULL, NULL, 'sd', NULL, '2026-03-29 21:40:47', '2026-03-29 22:15:03'),
(58, 2, 'dd', 'dd', 1, NULL, 3, 15, 12, 'aDfdasfgfdgsdf', NULL, NULL, NULL, 'dd', NULL, '2026-03-29 21:44:37', '2026-04-09 06:09:10'),
(59, 2, 'tt', 'tt', 2, NULL, 1, 3, 12, 'hh', NULL, '12', 'hh', 'tt', NULL, '2026-03-29 21:51:55', '2026-03-29 22:05:55'),
(60, 2, 'jj', 'jj', 2, NULL, 2, 11, 16, '1098', NULL, '16', 'done 55', 'jj', NULL, '2026-03-30 00:20:17', '2026-03-30 01:48:08'),
(61, 2, '56', '56', 2, 4, 1, 5, 12, 'hjjjjj', NULL, '12', 'saldkfjalsdfkj', '56', NULL, '2026-03-30 01:53:04', '2026-03-30 01:56:59'),
(62, 2, '12', '12', 2, NULL, 1, 3, 14, 'koro', NULL, '14', 'Done 101', '12', NULL, '2026-03-30 02:00:44', '2026-03-30 02:02:00'),
(63, 2, 'as', 'as', 1, NULL, 1, 4, 12, NULL, NULL, NULL, NULL, 'as', NULL, '2026-03-30 02:17:32', '2026-03-30 02:27:14'),
(64, 2, 'vv', 'vv', 2, NULL, 3, 15, 12, 'yyyyyyyyyyyyyy', NULL, '12', 'fine', 'vv', NULL, '2026-03-30 03:08:11', '2026-03-30 03:10:56'),
(65, 2, 'ff', 'ff', 1, NULL, 1, 3, 12, 'uuuu', NULL, NULL, NULL, 'ff', NULL, '2026-03-30 03:23:09', '2026-04-09 06:08:12'),
(66, 2, 'dd', 'dd', 2, NULL, 1, 6, 12, NULL, NULL, '12', 'hh', 'dd', NULL, '2026-03-30 09:41:11', '2026-03-30 09:42:35'),
(67, 1, '12', '12', 2, NULL, 1, 4, 12, '1/2% done', NULL, '12', 'Done', '12', NULL, '2026-04-01 03:28:20', '2026-04-01 03:41:03'),
(68, 1, '33', '33', 2, 4, 1, 6, 14, 'problem', NULL, '14', '8888', '333', NULL, '2026-04-01 03:42:09', '2026-04-01 03:51:53'),
(69, 1, '22', '22', 2, NULL, 1, 1, 14, 'fg', NULL, '14', '666666666666666666666', '22', NULL, '2026-04-01 04:17:19', '2026-04-01 04:20:52'),
(70, 1, '555', '55', 2, NULL, 1, 6, 14, 'hhhhhhh', NULL, '14', 'done', '55', NULL, '2026-04-09 06:16:56', '2026-04-09 06:21:56'),
(71, 1, '12', '12', 2, NULL, 1, 3, 12, NULL, NULL, '12', 'done', '12', NULL, '2026-04-13 04:08:47', '2026-04-13 04:18:07'),
(72, 2, '11', '11', 1, NULL, 1, 3, 4, 'tttttttttttt', NULL, NULL, NULL, '11', NULL, '2026-04-13 05:22:09', '2026-04-13 06:53:38'),
(73, 2, 'gg', 'gg', 1, NULL, 1, 6, 12, 'gh', NULL, NULL, NULL, 'gg', NULL, '2026-04-13 07:00:07', '2026-04-13 07:09:28'),
(74, 2, '22', '22', 2, 1, 1, 3, 12, 'ty', NULL, '12', 'done', '22', NULL, '2026-04-15 04:03:34', '2026-04-15 05:01:59'),
(75, 2, 'ss', 'ss', 2, 2, 1, 1, 4, 'jjk part done', NULL, '4', 'done 12', 'ss', NULL, '2026-04-15 04:31:56', '2026-04-15 05:25:37'),
(76, 2, 'ff', 'ff', 0, NULL, 2, 11, NULL, NULL, NULL, NULL, NULL, 'ff', NULL, '2026-04-15 06:19:04', '2026-04-15 06:19:04'),
(77, 1, 'eye', 'eye problem', 2, NULL, 1, 3, 4, NULL, NULL, '4', 'donee', 'rahim', NULL, '2026-04-16 03:19:34', '2026-04-16 03:34:13'),
(78, 1, 'dd', 'dd', 2, NULL, 1, 3, 14, 'eye problem solve it', NULL, '14', 'complete', 'dd', NULL, '2026-04-16 03:37:27', '2026-04-16 03:38:26'),
(79, 1, 'eye', 'eye problem', 2, NULL, 1, 3, 1, NULL, NULL, '1', 'Done', 'Karim', NULL, '2026-04-16 04:35:00', '2026-04-16 04:38:05'),
(80, 1, 'ss', 'ss', 2, NULL, 1, 3, 1, NULL, NULL, '1', 'ss', 'ss', NULL, '2026-04-16 05:36:19', '2026-04-16 05:38:02'),
(81, 1, 'tt', 'tt', 1, NULL, 1, 3, 1, NULL, NULL, NULL, NULL, 'tt', NULL, '2026-04-16 05:46:14', '2026-04-16 05:59:34'),
(82, 1, 'test1', 'test description', 2, NULL, 1, 3, 4, NULL, NULL, '4', 'done', 'kasem', NULL, '2026-04-19 08:51:39', '2026-04-19 08:55:08'),
(83, 1, 'Issue', 'issue description', 0, NULL, 1, 5, NULL, NULL, NULL, NULL, NULL, 'kasem', NULL, '2026-04-20 03:18:47', '2026-04-20 03:18:47');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_attachments`
--

DROP TABLE IF EXISTS `ticket_attachments`;
CREATE TABLE IF NOT EXISTS `ticket_attachments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_attachments_ticket_id_foreign` (`ticket_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_attachments`
--

INSERT INTO `ticket_attachments` (`id`, `ticket_id`, `file_path`, `original_name`, `created_at`, `updated_at`) VALUES
(1, 56, 'tickets/Bfy8l8YKBojOQ0AmGSrbBChzCoS4ojciJYwlhXoM.jpg', '10.jpg', '2026-03-29 21:32:52', '2026-03-29 21:32:52'),
(2, 57, 'tickets/l8kCIGctTx27eQTW2DrUkdPvbsInQ5B3kbH5ZNNz.jpg', 'wallpaperflare.com_wallpaper (4).jpg', '2026-03-29 21:40:47', '2026-03-29 21:40:47'),
(3, 58, 'tickets/O8YcUsjSgYR3v75EXVbqCKRlcsOqEuCI8EiaJVNN.jpg', '10.jpg', '2026-03-29 21:44:37', '2026-03-29 21:44:37'),
(4, 58, 'tickets/jH5XyStPJ5M1glWlwxysh8EQ77iQEnSX9Uh3adJr.jpg', 'wallpaperflare.com_wallpaper (4).jpg', '2026-03-29 21:44:37', '2026-03-29 21:44:37'),
(5, 59, 'tickets/PeimG9svG7VUnY243E67MFdUfk1k9XEZ2PBZjwds.jpg', '10.jpg', '2026-03-29 21:51:55', '2026-03-29 21:51:55'),
(6, 59, 'tickets/6m3UqT9TX3YHJQxXINHPaTjIudGxnRxTcHRaIRO0.jpg', 'wallpaperflare.com_wallpaper (4).jpg', '2026-03-29 21:51:55', '2026-03-29 21:51:55'),
(7, 60, 'tickets/yF5B4452S6hpS5ogJsF0hpL63E2H987dMzjw4q7q.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-03-30 00:20:17', '2026-03-30 00:20:17'),
(8, 60, 'tickets/w7rho6ONfBceJzfBMAj4dBRFYNkHM1HGdzTDPJAA.jpg', '10.jpg', '2026-03-30 00:20:17', '2026-03-30 00:20:17'),
(9, 61, 'tickets/YVlEH3EAcm8WRu49zRJvQ9ulaxXvU8zcFjrlXBmA.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-03-30 01:53:04', '2026-03-30 01:53:04'),
(10, 61, 'tickets/pMV1IBWCqN4NTHfk832RkTWIAXRBF63SsMz2zzI1.jpg', '10.jpg', '2026-03-30 01:53:04', '2026-03-30 01:53:04'),
(11, 62, 'tickets/wAakHs8M4LIBy2T2RE7ySpZT40YBI3q8Hrya4cKn.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-03-30 02:00:44', '2026-03-30 02:00:44'),
(12, 62, 'tickets/Pfp1vZt7LY7KrPB7NLhUkWemMbMksfy1QN87805q.jpg', '10.jpg', '2026-03-30 02:00:44', '2026-03-30 02:00:44'),
(13, 63, 'tickets/UKKpNCrvJUIsheHJj6hp1Lj3D6dfZ8nkqN97rmrs.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-03-30 02:17:32', '2026-03-30 02:17:32'),
(14, 63, 'tickets/jVChXQ5YaUn8RM4u7KE825MsvPsu53aSvLdvjDLg.jpg', '10.jpg', '2026-03-30 02:17:32', '2026-03-30 02:17:32'),
(15, 65, 'tickets/EBWdcXKXXABP6pkzqUT4awyjuHh5yfsK2pfN217N.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-03-30 03:23:09', '2026-03-30 03:23:09'),
(16, 65, 'tickets/fUm7hxradiVlM94cmVdDxhk9t1xNkHM11pYXKG31.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-03-30 03:23:09', '2026-03-30 03:23:09'),
(17, 67, 'tickets/HEKKetWm1bPzUEaURyNAROypY8eZhkXJVBGLMl8h.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-04-01 03:28:20', '2026-04-01 03:28:20'),
(18, 67, 'tickets/FgMNaGOd8SleqRYL4queLp7UZ2NCRtUZs6V5ndbw.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-04-01 03:28:20', '2026-04-01 03:28:20'),
(19, 69, 'tickets/VS75DBGicOemXP9TCtTCgZr8hmioEKY2zOyFS1RG.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-04-01 04:17:19', '2026-04-01 04:17:19'),
(20, 69, 'tickets/jj8d9TtSrrflyKhIgDuM7VC03mZdOIKyCRVndnax.jpg', 'wallpaperflare.com_wallpaper (4).jpg', '2026-04-01 04:17:19', '2026-04-01 04:17:19'),
(21, 70, 'tickets/2XVhzC3EBZas6VCqOPp94Ofp8s3tdzHLyRoTMunM.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-04-09 06:16:56', '2026-04-09 06:16:56'),
(22, 70, 'tickets/4fyLGhQlzvFqdcUYx8OQnhER3kfm9aYo4KGzWht0.jpg', '10.jpg', '2026-04-09 06:16:56', '2026-04-09 06:16:56'),
(23, 71, 'tickets/5SqsZElpz3ALCco0of9sGDDPfat2oy5yVUfxIg2P.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-04-13 04:08:47', '2026-04-13 04:08:47'),
(24, 71, 'tickets/7N8NSbnZ5ln7vSM5e9mle55rrGxdBw472qfDQFCO.jpg', '10.jpg', '2026-04-13 04:08:47', '2026-04-13 04:08:47'),
(25, 77, 'tickets/EAcF7W65EySsUJ9BAa9pPsam4wLXGD3eOIr1gOuz.jpg', '10.jpg', '2026-04-16 03:19:34', '2026-04-16 03:19:34'),
(26, 82, 'tickets/lCYMwqHVJqHyqaneyX3Ym9bXU7SmnKszsmxGWMNo.jpg', '7d8385ae30bdbe7f883c12467f123ab1.jpg', '2026-04-19 08:51:39', '2026-04-19 08:51:39');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_replies`
--

DROP TABLE IF EXISTS `ticket_replies`;
CREATE TABLE IF NOT EXISTS `ticket_replies` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_replies_ticket_id_foreign` (`ticket_id`),
  KEY `ticket_replies_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_replies`
--

INSERT INTO `ticket_replies` (`id`, `ticket_id`, `user_id`, `message`, `note`, `created_at`, `updated_at`) VALUES
(1, 5, 4, NULL, 'test', '2025-12-12 14:41:00', '2025-12-12 14:41:00'),
(2, 7, 4, NULL, 't', '2025-12-12 14:41:25', '2025-12-12 14:41:25'),
(3, 8, 4, 'working on it', NULL, '2026-02-09 03:32:47', '2026-02-09 03:32:47'),
(4, 8, 4, NULL, 'test- please see the issue', '2026-02-09 03:33:23', '2026-02-09 03:33:23'),
(5, 34, 4, NULL, 'do it', '2026-03-09 22:38:15', '2026-03-09 22:38:15'),
(6, 34, 4, NULL, 'ui', '2026-03-09 22:39:14', '2026-03-09 22:39:14'),
(7, 45, 14, 'Forwarded to Bulon: problem on X', NULL, '2026-03-11 21:40:39', '2026-03-11 21:40:39'),
(8, 47, 1, 'Forwarded to Rezwan: do it', NULL, '2026-03-11 23:17:02', '2026-03-11 23:17:02'),
(9, 49, 4, 'Forwarded to Joyanta Kumer: jjjj', NULL, '2026-03-12 00:49:27', '2026-03-12 00:49:27'),
(10, 51, 16, 'Forwarded to Dibya: problem is x', NULL, '2026-03-15 00:13:53', '2026-03-15 00:13:53'),
(11, 51, 6, 'Forwarded to Bulon: kl', NULL, '2026-03-16 00:12:33', '2026-03-16 00:12:33'),
(12, 54, 14, 'Forwarded to Rezwan: 1 part complete', NULL, '2026-03-27 05:33:26', '2026-03-27 05:33:26'),
(13, 59, 1, NULL, 'hk', '2026-03-29 21:53:29', '2026-03-29 21:53:29'),
(14, 59, 12, 'Forwarded to Joyanta Kumer: yu', NULL, '2026-03-29 21:54:47', '2026-03-29 21:54:47'),
(15, 59, 14, 'Forwarded to Bulon: hd', NULL, '2026-03-29 21:57:15', '2026-03-29 21:57:15'),
(16, 59, 12, 'Forwarded to Joyanta Kumer: gg', NULL, '2026-03-29 22:04:48', '2026-03-29 22:04:48'),
(17, 59, 1, 'Manual assign to Bulon: hh', NULL, '2026-03-29 22:05:25', '2026-03-29 22:05:25'),
(18, 57, 12, 'Forwarded to F M Nafis: asdf', NULL, '2026-03-29 22:15:03', '2026-03-29 22:15:03'),
(19, 56, 15, 'Forwarded to Akash: ggggggggggggg', NULL, '2026-03-29 22:16:56', '2026-03-29 22:16:56'),
(20, 56, 16, 'Forwarded to Maznu: tk', NULL, '2026-03-30 00:56:18', '2026-03-30 00:56:18'),
(21, 60, 16, 'Forwarded to Maznu: gg', NULL, '2026-03-30 00:57:45', '2026-03-30 00:57:45'),
(22, 60, 15, 'Forwarded to Akash: hh', NULL, '2026-03-30 01:08:46', '2026-03-30 01:08:46'),
(23, 60, 16, 'Forwarded to Maznu: huh', NULL, '2026-03-30 01:39:06', '2026-03-30 01:39:06'),
(24, 60, 15, 'Forwarded to Akash: 1098', NULL, '2026-03-30 01:47:44', '2026-03-30 01:47:44'),
(25, 61, 14, 'Forwarded to Bulon: hjjjjj', NULL, '2026-03-30 01:56:32', '2026-03-30 01:56:32'),
(26, 62, 4, 'Manual assign to Joyanta Kumer: koro', NULL, '2026-03-30 02:01:24', '2026-03-30 02:01:24'),
(27, 64, 14, 'Forwarded to Bulon: yyyyyyyyyyyyyy', NULL, '2026-03-30 03:10:36', '2026-03-30 03:10:36'),
(28, 67, 14, 'Forwarded to Bulon: 1/2% done', NULL, '2026-04-01 03:40:00', '2026-04-01 03:40:00'),
(29, 68, 12, 'Forwarded to Joyanta Kumer: problem', NULL, '2026-04-01 03:49:48', '2026-04-01 03:49:48'),
(30, 69, 4, 'Manual assign to Joyanta Kumer: fg', NULL, '2026-04-01 04:19:07', '2026-04-01 04:19:07'),
(31, 65, 4, 'Manual assign to Bulon: uuuu', NULL, '2026-04-09 06:08:13', '2026-04-09 06:08:13'),
(32, 58, 4, 'Manual assign to Bulon: aDfdasfgfdgsdf', NULL, '2026-04-09 06:09:10', '2026-04-09 06:09:10'),
(33, 70, 12, 'Forwarded to Joyanta Kumer: hhhhhhh', NULL, '2026-04-09 06:21:18', '2026-04-09 06:21:18'),
(34, 72, 6, 'Manual assign to Dibya: tt', NULL, '2026-04-13 06:46:06', '2026-04-13 06:46:06'),
(35, 72, 6, 'Manual assign to Rezwan: tttttttttttt', NULL, '2026-04-13 06:53:38', '2026-04-13 06:53:38'),
(36, 73, 4, 'Manual assign to Bulon: gg', NULL, '2026-04-13 07:06:56', '2026-04-13 07:06:56'),
(37, 73, 4, 'Manual assign to Bulon: gh', NULL, '2026-04-13 07:09:28', '2026-04-13 07:09:28'),
(38, 74, 4, 'Manual assign to Dibya: do this', NULL, '2026-04-15 04:17:32', '2026-04-15 04:17:32'),
(39, 74, 14, 'Forwarded to Bulon: hhhh', NULL, '2026-04-15 04:58:36', '2026-04-15 04:58:36'),
(40, 74, 12, 'Forwarded to Joyanta Kumer: gh', NULL, '2026-04-15 05:00:01', '2026-04-15 05:00:01'),
(41, 74, 14, 'Forwarded to Bulon: ty', NULL, '2026-04-15 05:01:35', '2026-04-15 05:01:35'),
(42, 75, 4, 'Manual assign to Joyanta Kumer: fg', NULL, '2026-04-15 05:09:48', '2026-04-15 05:09:48'),
(43, 75, 4, 'Manual assign to Dibya: uuuuuuuuuuuuuuuuuuuu', NULL, '2026-04-15 05:16:38', '2026-04-15 05:16:38'),
(44, 75, 6, 'Manual assign to Rezwan: jjk part done', NULL, '2026-04-15 05:22:51', '2026-04-15 05:22:51'),
(45, 78, 4, 'Manual assign to Joyanta Kumer: eye problem solve it', NULL, '2026-04-16 03:38:00', '2026-04-16 03:38:00');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_status_logs`
--

DROP TABLE IF EXISTS `ticket_status_logs`;
CREATE TABLE IF NOT EXISTS `ticket_status_logs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` int UNSIGNED NOT NULL,
  `status` tinyint NOT NULL,
  `changed_by` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_status_logs`
--

INSERT INTO `ticket_status_logs` (`id`, `ticket_id`, `status`, `changed_by`, `created_at`) VALUES
(1, 38, 1, 4, '2025-11-16 11:04:38'),
(2, 39, 1, 4, '2025-11-19 11:42:36'),
(3, 42, 1, 4, '2025-11-19 11:44:14'),
(4, 43, 2, 4, '2025-11-19 11:44:40'),
(5, 8, 1, 12, '2026-02-09 03:34:19'),
(6, 8, 2, 12, '2026-02-09 03:34:39'),
(7, 11, 2, 4, '2026-02-16 03:41:55'),
(8, 30, 1, 4, '2026-03-01 22:56:59'),
(9, 30, 0, 4, '2026-03-01 22:57:40'),
(10, 30, 1, 4, '2026-03-01 22:58:09'),
(11, 34, 1, 4, '2026-03-09 22:39:02'),
(12, 36, 2, 15, '2026-03-09 22:51:27'),
(13, 38, 1, 14, '2026-03-10 01:12:10'),
(14, 38, 2, 14, '2026-03-10 01:12:15'),
(15, 39, 2, 14, '2026-03-10 02:27:52'),
(16, 40, 1, 4, '2026-03-10 02:38:12'),
(17, 40, 2, 4, '2026-03-10 02:40:30'),
(18, 42, 1, 14, '2026-03-10 22:13:36'),
(19, 42, 2, 10, '2026-03-10 22:24:44'),
(20, 43, 2, 4, '2026-03-10 22:55:38'),
(21, 47, 2, 4, '2026-03-11 23:18:03'),
(22, 49, 2, 14, '2026-03-12 01:04:40'),
(23, 50, 2, 4, '2026-03-12 03:03:20'),
(24, 51, 2, 12, '2026-03-16 00:39:27'),
(25, 54, 2, 4, '2026-03-27 05:34:08'),
(26, 52, 2, 14, '2026-03-27 06:23:23'),
(27, 59, 2, 12, '2026-03-29 22:05:55'),
(28, 60, 2, 16, '2026-03-30 01:48:08'),
(29, 61, 2, 12, '2026-03-30 01:56:59'),
(30, 62, 2, 14, '2026-03-30 02:02:00'),
(31, 64, 2, 12, '2026-03-30 03:10:56'),
(32, 66, 1, 4, '2026-03-30 09:41:34'),
(33, 66, 2, 12, '2026-03-30 09:42:35'),
(34, 67, 1, 4, '2026-04-01 03:39:15'),
(35, 67, 2, 12, '2026-04-01 03:41:03'),
(36, 68, 1, 12, '2026-04-01 03:47:16'),
(37, 68, 2, 14, '2026-04-01 03:51:53'),
(38, 69, 1, 4, '2026-04-01 04:19:07'),
(39, 69, 2, 14, '2026-04-01 04:20:52'),
(40, 65, 1, 4, '2026-04-09 06:04:18'),
(41, 58, 1, 4, '2026-04-09 06:09:10'),
(42, 70, 1, 12, '2026-04-09 06:20:41'),
(43, 70, 2, 14, '2026-04-09 06:21:56'),
(44, 71, 1, 12, '2026-04-13 04:16:12'),
(45, 71, 2, 12, '2026-04-13 04:18:07'),
(46, 72, 1, 4, '2026-04-13 06:09:52'),
(47, 73, 1, 4, '2026-04-13 07:00:29'),
(48, 74, 1, 4, '2026-04-15 04:04:54'),
(49, 75, 1, 4, '2026-04-15 04:33:37'),
(50, 74, 2, 12, '2026-04-15 05:01:59'),
(51, 75, 2, 4, '2026-04-15 05:25:37'),
(52, 77, 1, 4, '2026-04-16 03:32:48'),
(53, 77, 2, 4, '2026-04-16 03:34:13'),
(54, 78, 1, 4, '2026-04-16 03:38:00'),
(55, 78, 2, 14, '2026-04-16 03:38:26'),
(56, 79, 1, 1, '2026-04-16 04:36:24'),
(57, 79, 2, 1, '2026-04-16 04:38:05'),
(58, 80, 1, 1, '2026-04-16 05:37:57'),
(59, 80, 2, 1, '2026-04-16 05:38:02'),
(60, 81, 1, 1, '2026-04-16 05:59:34'),
(61, 82, 1, 4, '2026-04-19 08:54:08'),
(62, 82, 2, 4, '2026-04-19 08:55:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` int NOT NULL,
  `role_id` int DEFAULT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `role`, `role_id`, `branch_id`, `created_at`, `updated_at`) VALUES
(1, 'F M Nafis', '3628', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 1, NULL, 4, '2025-06-13 02:28:57', '2026-03-11 00:38:36'),
(10, 'Polash', '2397', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 2, 1, 4, '2025-06-13 02:28:57', '2026-03-11 00:30:58'),
(9, 'Dharkhar', 'bm0004', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 3, NULL, 2, '2025-06-13 02:28:57', '2026-02-23 05:00:45'),
(4, 'Rezwan', '4360', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 1, 1, 4, '2025-06-13 02:28:57', '2026-02-24 01:45:56'),
(6, 'Dibya', '5750', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 1, 1, 4, '2025-06-13 02:28:57', '2026-04-13 05:06:11'),
(7, 'Kuti', 'bm0001', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 3, NULL, 1, '2025-06-13 02:28:57', '2025-11-18 16:54:40'),
(8, 'Chargas', 'bm0003', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 3, NULL, 3, '2025-06-13 02:28:57', '2025-11-18 16:56:45'),
(14, 'Joyanta Kumer', '6246', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 2, NULL, 4, '2025-06-13 02:28:57', '2026-03-11 00:29:45'),
(11, 'Pronab Mondal', '2692', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 2, NULL, 4, '2025-06-13 02:28:57', '2026-03-11 00:41:36'),
(12, 'Bulon', '5696', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 2, NULL, 4, '2025-06-13 02:28:57', '2026-02-24 00:56:13'),
(15, 'Maznu', '1802', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 1, 2, 4, '2025-06-13 02:28:57', '2026-04-13 05:52:58'),
(16, 'Akash', '6623', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, 1, 2, 4, '2025-06-13 02:28:57', '2026-04-13 05:53:12');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
