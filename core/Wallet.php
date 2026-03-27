<?php
// Wallet & Commission Logic | منطق کیف پول و پورسانت
require_once __DIR__ . '/../config/db.php';

class Wallet {
    // افزودن سود تراکنش به معرف یا نماینده
    public static function addTransaction($userId, $amount, $type, $description) {
        global $db;
        try {
            $db->beginTransaction();
            
            // ۱. بروزرسانی موجودی کاربر
            $stmt = $db->prepare("UPDATE users SET wallet = wallet + ? WHERE id = ?");
            $stmt->execute([$amount, $userId]);

            // ۲. ثبت در تاریخچه تراکنش‌ها
            $stmt = $db->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $amount, $type, $description]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    // توزیع پورسانت بازاریابی (Affiliate)
    public static function distributeAffiliateProfit($subUserId, $purchaseAmount) {
        global $db;
        $stmt = $db->prepare("SELECT referrer_id FROM users WHERE id = ?");
        $stmt->execute([$subUserId]);
        $referrerId = $stmt->fetchColumn();

        if ($referrerId) {
            $commission = $purchaseAmount * 0.10; // ۱۰ درصد سود معرف
            self::addTransaction($referrerId, $commission, 'commission', "سود خرید زیرمجموعه");
        }
    }
}