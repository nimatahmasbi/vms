<?php
// IP Pool Logic | منطق مدیریت مخزن آی‌پی
class IPPoolManager {
    public static function allocate($db, $locationId, $userId) {
        $stmt = $db->prepare("SELECT id, ip_address FROM ip_pool WHERE location_id = ? AND is_used = 0 LIMIT 1");
        $stmt->execute([$locationId]);
        $ipRow = $stmt->fetch();

        if ($ipRow) {
            $db->prepare("UPDATE ip_pool SET is_used = 1, user_id = ? WHERE id = ?")
               ->execute([$userId, $ipRow['id']]);
            return $ipRow['ip_address'];
        }
        return false;
    }

    public static function release($db, $userId) {
        $db->prepare("UPDATE ip_pool SET is_used = 0, user_id = NULL WHERE user_id = ?")
           ->execute([$userId]);
    }
}