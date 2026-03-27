<?php
// Scrape Usage Data from MikroTik | استخراج دیتای مصرف از میکروتیک
require_once 'MikroTikEngine.php';

class UsageMonitor {
    public function syncUserUsage($userId) {
        global $db;
        $stmt = $db->prepare("SELECT s.*, l.server_ip FROM services s JOIN locations l ON s.location_id = l.id WHERE s.user_id = ?");
        $stmt->execute([$userId]);
        $service = $stmt->fetch();

        if ($service) {
            $mk = new MikroTikEngine();
            // فرض: یوزرنیم و پسورد میکروتیک در تنظیمات است
            if ($mk->connectRouter($service['server_ip'], 'admin', 'pass')) {
                $stats = $mk->getApi()->comm("/queue/simple/print", [
                    "?target" => $service['remote_ip'] . "/32"
                ]);
                
                if (!empty($stats)) {
                    // فیلد total-bytes مجموع آپلود و دانلود است
                    $bytes = explode("/", $stats[0]['total-bytes'])[1] ?? 0; 
                    
                    // بروزرسانی در دیتابیس
                    $update = $db->prepare("UPDATE services SET used_volume = ? WHERE user_id = ?");
                    $update->execute([$bytes, $userId]);
                    return $bytes;
                }
            }
        }
        return 0;
    }
}