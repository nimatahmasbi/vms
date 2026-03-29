<?php
/**
 * تابع تخصیص خودکار آی‌پی از رنج تعریف شده در لوکیشن
 * Get Next Free IP from Range
 */
function getNextFreeIP($db, $location_id) {
    // ۱. دریافت اطلاعات رنج از دیتابیس
    $stmt = $db->prepare("SELECT remote_ip_range, last_assigned_ip FROM locations WHERE id = ?");
    $stmt->execute([$location_id]);
    $loc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$loc) return null;

    $range = $loc['remote_ip_range'];
    $last_ip = $loc['last_assigned_ip'];

    // ۲. اگر رنج نیست و فقط یک آی‌پی تک است
    if (strpos($range, '-') === false) {
        return $range; 
    }

    // ۳. تحلیل رنج (شروع و پایان)
    list($start_ip, $end_ip) = explode('-', $range);
    $start_long = ip2long(trim($start_ip));
    $end_long   = ip2long(trim($end_ip));

    // ۴. محاسبه آی‌پی بعدی
    if (!$last_ip) {
        $next_long = $start_long;
    } else {
        $next_long = ip2long($last_ip) + 1;
    }

    // ۵. بررسی سقف رنج
    if ($next_long > $end_long) {
        return "FULL"; // تمام آی‌پی‌ها مصرف شده‌اند
    }

    $next_ip = long2ip($next_long);

    // ۶. به‌روزرسانی دیتابیس برای استفاده‌های بعدی
    $update = $db->prepare("UPDATE locations SET last_assigned_ip = ? WHERE id = ?");
    $update->execute([$next_ip, $location_id]);

    return $next_ip;
}

/**
 * تبدیل بایت به واحدهای قابل فهم (GB/MB)
 * Format Bytes to Human Readable
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>