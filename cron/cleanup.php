<?php
// Auto-release Expired IPs | آزادسازی خودکار آی‌پی‌های منقضی
require_once '../config/db.php';
require_once '../core/IPPoolManager.php';

$now = date('Y-m-d H:i:s');
// پیدا کردن سرویس‌هایی که تاریخ انقضایشان گذشته است
$expired = $db->prepare("SELECT user_id, remote_ip FROM services WHERE expire_date < ? AND status = 'active'");
$expired->execute([$now]);

foreach ($expired as $srv) {
    // ۱. آزاد سازی در دیتابیس
    IPPoolManager::release($db, $srv['user_id']);
    
    // ۲. تغییر وضعیت در دیتابیس
    $db->prepare("UPDATE services SET status = 'expired' WHERE user_id = ?")
       ->execute([$srv['user_id']]);
       
    // ۳. دستور حذف از میکروتیک (اختیاری در اینجا یا توسط MikroTikEngine)
    echo "Released IP: " . $srv['remote_ip'] . " for User ID: " . $srv['user_id'] . "\n";
}