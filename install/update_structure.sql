-- افزودن فیلدهای پیامک و کرون‌جاب اگر وجود ندارند
ALTER TABLE `system_settings` ADD COLUMN IF NOT EXISTS `pattern_otp` varchar(50) DEFAULT NULL;
ALTER TABLE `system_settings` ADD COLUMN IF NOT EXISTS `days_before_expiry` int(11) DEFAULT 3;
ALTER TABLE `system_settings` ADD COLUMN IF NOT EXISTS `megabytes_before_finish` int(11) DEFAULT 100;

-- افزودن فیلدهای وضعیت پیامک به اشتراک‌ها
ALTER TABLE `user_subscriptions` ADD COLUMN IF NOT EXISTS `expiry_sms_sent` tinyint(1) DEFAULT 0;
ALTER TABLE `user_subscriptions` ADD COLUMN IF NOT EXISTS `volume_sms_sent` tinyint(1) DEFAULT 0;

-- ساخت جدول NAS Routers اگر وجود ندارد
CREATE TABLE IF NOT EXISTS `nas_routers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nas_name` varchar(100) NOT NULL,
  `nas_ip` varchar(255) NOT NULL,
  `nas_secret` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;