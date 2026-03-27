<?php
// IP Pool Generator | تولید کننده مخزن آی‌پی
require_once '../config/db.php';

function generateRange($db, $locId, $startIp, $endIp) {
    $prefix = substr($startIp, 0, strrpos($startIp, '.') + 1);
    $start = (int)substr($startIp, strrpos($startIp, '.') + 1);
    $end = (int)substr($endIp, strrpos($endIp, '.') + 1);

    for ($i = $start; $i <= $end; $i++) {
        $ip = $prefix . $i;
        $db->prepare("INSERT IGNORE INTO ip_pool (location_id, ip_address) VALUES (?, ?)")
           ->execute([$locId, $ip]);
    }
}

// مثال برای لوکیشن ۱ (آمریکا) و لوکیشن ۲ (آلمان)
generateRange($db, 1, '192.168.11.2', '192.168.11.254');
generateRange($db, 2, '192.168.12.2', '192.168.12.254');
generateRange($db, 2, '192.168.13.2', '192.168.13.254');
generateRange($db, 2, '192.168.14.2', '192.168.14.254');
generateRange($db, 2, '192.168.15.2', '192.168.15.254');

echo "IP Pool Generated Successfully | مخزن آی‌پی با موفقیت ساخته شد";