SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- ۱. جدول کاربران (Users)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `mobile` varchar(15) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `wallet` decimal(12,0) DEFAULT 0,
  `role` enum('user','reseller','admin') DEFAULT 'user',
  `referrer_id` int(11) DEFAULT NULL,
  `status` enum('pending','active','blocked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- ۲. جدول تنظیمات سرور میکروتیک (Server Configs)
-- --------------------------------------------------------
CREATE TABLE `server_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_name` varchar(100) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `api_port` int(11) DEFAULT 8728,
  `api_user` varchar(100) NOT NULL,
  `api_pass` varchar(255) NOT NULL,
  `userman_profile` varchar(100) DEFAULT 'default',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- ۳. جدول روترهای فرعی (NAS Routers)
-- --------------------------------------------------------
CREATE TABLE `nas_routers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nas_name` varchar(100) NOT NULL,
  `nas_ip` varchar(255) NOT NULL,
  `nas_secret` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- ۴. جدول لوکیشن‌ها (Locations)
-- --------------------------------------------------------
CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_name` varchar(100) NOT NULL,
  `country_code` varchar(10) DEFAULT NULL,
  `server_address` varchar(255) DEFAULT NULL,
  `local_ip_range` varchar(100) DEFAULT '10.10.10.1',
  `remote_ip_range` varchar(100) DEFAULT '10.10.10.2-10.10.10.254',
  `last_assigned_ip` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- ۵. جدول پلن‌های فروش (Plans)
-- --------------------------------------------------------
CREATE TABLE `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `volume_bytes` bigint(20) NOT NULL,
  `price` decimal(12,0) NOT NULL,
  `duration_days` int(11) DEFAULT 30,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- ۶. جدول رابطه پلن و لوکیشن (Plan Locations)
-- --------------------------------------------------------
CREATE TABLE `plan_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- ۷. جدول اشتراک کاربران (User Subscriptions)
-- --------------------------------------------------------
CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `mikrotik_username` varchar(100) DEFAULT NULL,
  `assigned_local_ip` varchar(50) DEFAULT NULL,
  `assigned_remote_ip` varchar(50) DEFAULT NULL,
  `used_traffic` bigint(20) DEFAULT 0,
  `expire_date` datetime NOT NULL,
  `expiry_sms_sent` tinyint(1) DEFAULT 0,
  `volume_sms_sent` tinyint(1) DEFAULT 0,
  `status` enum('active','expired','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- ۸. جدول تنظیمات سیستم (System Settings)
-- --------------------------------------------------------
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) DEFAULT 'VMS Panel',
  `sms_api_key` varchar(255) DEFAULT NULL,
  `pattern_otp` varchar(50) DEFAULT NULL,
  `pattern_admin_ticket` varchar(50) DEFAULT NULL,
  `pattern_agent_ticket` varchar(50) DEFAULT NULL,
  `pattern_expiry_warning` varchar(50) DEFAULT NULL,
  `pattern_volume_warning` varchar(50) DEFAULT NULL,
  `days_before_expiry` int(11) DEFAULT 3,
  `megabytes_before_finish` int(11) DEFAULT 100,
  `referral_profit_percent` int(11) DEFAULT 10,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- ۹. جدول تیکت‌ها و تراکنش‌ها (Tickets & Transactions)
-- --------------------------------------------------------
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('open','replied','closed') DEFAULT 'open',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ticket_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,0) NOT NULL,
  `type` enum('deposit','purchase','commission','withdrawal') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- درج مقادیر اولیه
INSERT INTO `system_settings` (`id`, `site_name`) VALUES (1, 'VMS Panel');

COMMIT;