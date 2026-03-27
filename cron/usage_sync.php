<?php
// Real-time Traffic Sync | همگام‌سازی ترافیک مصرفی
require_once '../config/db.php';
require_once '../core/MikroTikEngine.php';

$mk = new MikroTikEngine();
// دریافت لیست سرویس‌های فعال
$services = $db->query("SELECT * FROM services WHERE status = 'active'")->fetchAll();

foreach ($services as $srv) {
    // اتصال به روتر مربوطه (از جدول locations)
    // $mk->connectRouter(...)
    
    // دریافت آمار از Simple Queue
    $stats = $mk->getQueueStats($srv['remote_ip']); // متدی برای خواندن total-bytes
    
    if ($stats) {
        $db->prepare("UPDATE services SET used_volume = ? WHERE id = ?")
           ->execute([$stats, $srv['id']]);
        
        // چک کردن اتمام حجم
        if ($stats >= $srv['total_volume']) {
            $mk->disableService($srv['username']);
            $db->prepare("UPDATE services SET status = 'expired' WHERE id = ?")->execute([$srv['id']]);
        }
    }
}