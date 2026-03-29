-- VMS Full Database Structure (All 14 Tables)
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `user_subscriptions`, `transactions`, `ticket_messages`, `tickets`, 
`server_configs`, `server_assets`, `system_settings`, `plan_locations`, `plans`, 
`nas_routers`, `locations`, `external_routers`, `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ۱. کاربران
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `wallet` decimal(12,0) DEFAULT 0,
  `role` enum('admin','user','reseller') DEFAULT 'user',
  `status` enum('pending','active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ۲. تنظیمات سیستم
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) DEFAULT 'VMS Panel',
  `sms_api_key` varchar(255) DEFAULT NULL,
  `zarinpal_merchant` varchar(100) DEFAULT NULL,
  `zarinpal_status` tinyint(1) DEFAULT 0,
  `require_admin_approval` tinyint(1) DEFAULT 1,
  `user_registration` tinyint(1) DEFAULT 1,
  `pattern_otp` varchar(50) DEFAULT NULL,
  `pattern_admin_ticket` varchar(50) DEFAULT NULL,
  `pattern_agent_ticket` varchar(50) DEFAULT NULL,
  `pattern_expiry_warning` varchar(50) DEFAULT NULL,
  `days_before_expiry` int(11) DEFAULT 3,
  `pattern_volume_warning` varchar(50) DEFAULT NULL,
  `megabytes_before_finish` int(11) DEFAULT 100,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`id`, `site_name`) VALUES (1, 'VMS Smart Panel');

-- ۳. لوکیشن‌ها
CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `country_code` varchar(10) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ۴. روترهای اصلی (NAS)
CREATE TABLE `nas_routers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `secret` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ۵. روترهای خارجی
CREATE TABLE `external_routers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `router_name` varchar(100) DEFAULT NULL,
  `auth_key` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ۶. پلن‌ها
CREATE TABLE `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `price` decimal(12,0) NOT NULL,
  `duration_days` int(11) DEFAULT 30,
  `volume_gb` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ۷. رابطه پلن و لوکیشن
CREATE TABLE `plan_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ۸. دارایی‌های سرور
CREATE TABLE `server_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_name` varchar(100) DEFAULT NULL,
  `asset_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ۹. کانفیگ سرورها
CREATE TABLE `server_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_name` varchar(100) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `api_user` varchar(100) DEFAULT NULL,
  `api_pass` varchar(100) DEFAULT NULL,
  `api_port` int(5) DEFAULT 8728,
  `version` varchar(10) DEFAULT 'v7',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ۱۰. تیکت‌ها و پیام‌ها
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('open','closed','pending') DEFAULT 'open',
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ticket_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ۱۱. تراکنش‌ها و اشتراک‌ها
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,0) NOT NULL,
  `authority` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `mikrotik_username` varchar(100) DEFAULT NULL,
  `expire_date` datetime DEFAULT NULL,
  `status` enum('active','expired','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;