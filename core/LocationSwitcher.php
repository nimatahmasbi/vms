<?php
// Switch Location Logic | منطق جابجایی لوکیشن
require_once 'IPPoolManager.php';
require_once 'MikroTikEngine.php';

class LocationSwitcher {
    public function switch($db, $userId, $newLocId) {
        $userSrv = $db->prepare("SELECT * FROM services WHERE user_id = ?");
        $userSrv->execute([$userId]);
        $srv = $userSrv->fetch();

        $mk = new MikroTikEngine();
        // اتصال به روتر و حذف پیر قبلی
        // $mk->connectRouter(...)
        $mk->removeService($srv['username'], $srv['remote_ip']);
        
        // آزادسازی آی‌پی قدیم و گرفتن آی‌پی جدید
        IPPoolManager::release($db, $userId);
        $newIp = IPPoolManager::allocate($db, $newLocId, $userId);

        if ($newIp) {
            // آپدیت دیتابیس و ساخت در میکروتیک جدید
            $db->prepare("UPDATE services SET location_id = ?, remote_ip = ? WHERE user_id = ?")
               ->execute([$newLocId, $newIp, $userId]);
            // $mk->createService(...)
            return true;
        }
        return false;
    }
}