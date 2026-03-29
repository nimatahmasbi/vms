<?php
/**
 * VMS Project - Subscription & Traffic Checker (Cron Job)
 * مسیر پیشنهادی: /public_html/cron/check_subscriptions.php
 */

// ۱. تنظیمات اولیه و اتصال به دیتابیس
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sms_helper.php'; // شامل تابع ارسال پیامک
require_once __DIR__ . '/../includes/routeros_api.class.php'; // کلاس API میکروتیک

// ۲. واکشی تنظیمات عمومی و الگوها
$settings = $db->query("SELECT * FROM system_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    die("Settings not found! | تنظیمات سیستم یافت نشد.");
}

$today = date('Y-m-d H:i:s');
echo "--- شروع پردازش در تاریخ: $today ---<br>";

// ---------------------------------------------------------
// بخش اول: بررسی اتمام زمان (Expiry Warning)
// ---------------------------------------------------------
$expiry_pattern = $settings['pattern_expiry_warning'];
$days_to_check = intval($settings['days_before_expiry']);

if (!empty($expiry_pattern) && $days_to_check > 0) {
    echo "بررسی کاربران برای $days_to_check روز مانده به انقضا...<br>";
    
    // واکشی اشتراک‌هایی که دقیقا N روز به پایانشان مانده و پیامک نگرفته‌اند
    $expiry_stmt = $db->prepare("
        SELECT s.id, u.mobile, u.first_name, s.expire_date 
        FROM user_subscriptions s
        JOIN users u ON s.user_id = u.id
        WHERE s.status = 'active' 
        AND DATEDIFF(s.expire_date, NOW()) = ?
        AND s.expiry_sms_sent = 0
    ");
    $expiry_stmt->execute([$days_to_check]);
    $expiry_users = $expiry_stmt->fetchAll();

    foreach ($expiry_users as $row) {
        // ارسال پیامک | Send SMS
        // پارامترها: نام کاربر (token)، تعداد روز (token2)
        $res = sendSMS($row['mobile'], $expiry_pattern, [
            'token' => $row['first_name'],
            'token2' => $days_to_check
        ]);

        if ($res) {
            // علامت‌گذاری برای عدم ارسال تکراری
            $db->prepare("UPDATE user_subscriptions SET expiry_sms_sent = 1 WHERE id = ?")->execute([$row['id']]);
            echo "پیامک انقضا برای {$row['mobile']} ارسال شد.<br>";
        }
    }
} else {
    echo "اطلاع‌رسانی انقضا غیرفعال است (الگو خالی یا روز صفر است).<br>";
}


// ---------------------------------------------------------
// بخش دوم: بررسی اتمام حجم (Volume Warning)
// ---------------------------------------------------------
$volume_pattern = $settings['pattern_volume_warning'];
$mb_threshold = intval($settings['megabytes_before_finish']);

if (!empty($volume_pattern) && $mb_threshold > 0) {
    echo "بررسی ترافیک باقی‌مانده (آستانه: $mb_threshold MB)...<br>";
    
    // واکشی تنظیمات اتصال به میکروتیک
    $srv = $db->query("SELECT * FROM server_configs WHERE id = 1")->fetch();

    if ($srv) {
        $api = new RouterosAPI();
        if ($api->connect($srv['ip_address'], $srv['api_user'], $srv['api_pass'], $srv['api_port'])) {
            
            // دریافت وضعیت تمام کاربران از یوزرمنجر ۷
            $api->write('/user-manager/user/print');
            $m_users = $api->read();

            foreach ($m_users as $mu) {
                // محاسبه حجم باقی‌مانده | Calculate Remaining Traffic
                $total_limit = isset($mu['total-limit-bytes']) ? (float)$mu['total-limit-bytes'] : 0;
                $used_bytes = isset($mu['used-bytes']) ? (float)$mu['used-bytes'] : 0;
                
                if ($total_limit > 0) {
                    $rem_bytes = $total_limit - $used_bytes;
                    $rem_mb = $rem_bytes / (1024 * 1024);

                    // اگر حجم باقی‌مانده کمتر از آستانه بود
                    if ($rem_mb > 0 && $rem_mb <= $mb_threshold) {
                        
                        // بررسی در دیتابیس محلی (که قبلاً پیامک نگرفته باشد)
                        $local_stmt = $db->prepare("
                            SELECT s.id, u.mobile, u.first_name 
                            FROM user_subscriptions s
                            JOIN users u ON s.user_id = u.id
                            WHERE s.mikrotik_username = ? AND s.volume_sms_sent = 0 AND s.status = 'active'
                        ");
                        $local_stmt->execute([$mu['name']]);
                        $local_user = $local_stmt->fetch();

                        if ($local_user) {
                            // ارسال پیامک اتمام حجم | Send Volume SMS
                            // پارامترها: نام کاربر (token)، حجم باقی‌مانده (token2)
                            sendSMS($local_user['mobile'], $volume_pattern, [
                                'token' => $local_user['first_name'],
                                'token2' => round($rem_mb)
                            ]);
                            
                            // علامت‌گذاری برای عدم ارسال تکراری
                            $db->prepare("UPDATE user_subscriptions SET volume_sms_sent = 1 WHERE id = ?")->execute([$local_user['id']]);
                            echo "پیامک حجم برای کاربر {$mu['name']} ارسال شد.<br>";
                        }
                    }
                }
            }
            $api->disconnect();
        } else {
            echo "خطا در اتصال به API میکروتیک برای بررسی حجم!<br>";
        }
    }
} else {
    echo "اطلاع‌رسانی اتمام حجم غیرفعال است (الگو خالی یا مقدار صفر است).<br>";
}

echo "--- پایان عملیات ---";
?>