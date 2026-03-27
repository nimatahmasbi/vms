<?php
// Global Application Settings | تنظیمات سراسری اپلیکیشن
return [
    'app_name' => 'VMS Smart Panel',
    'sms_auth_enabled' => true,        // فعال بودن تایید پیامکی
    'admin_approval_required' => true, // نیاز به تایید مدیریت برای ثبت‌نام
    'referral_system' => true,         // فعال بودن سیستم معرف
    'registration_open' => true,       // باز بودن ثبت‌نام عمومی
    'zarinpal_merchant' => 'XXXX-XXXX-XXXX-XXXX',
    'ippanel_api_key' => 'YOUR_API_KEY',
    'ippanel_pattern_code' => '123456', // کد الگوی پیامک تایید
];