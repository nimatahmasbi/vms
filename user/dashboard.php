<?php
// User Service Overview | پیش‌نمایش سرویس کاربر
session_start();
require_once '../config/db.php';
require_once '../core/UsageMonitor.php';

$user_id = $_SESSION['user_id'];
$monitor = new UsageMonitor();
$used_bytes = $monitor->syncUserUsage($user_id); // دریافت حجم لحظه‌ای از میکروتیک

// دریافت اطلاعات سرویس و لوکیشن
$stmt = $db->prepare("SELECT s.*, l.name as loc_name, p.title as plan_title 
                      FROM services s 
                      JOIN locations l ON s.location_id = l.id 
                      JOIN plans p ON s.plan_id = p.id 
                      WHERE s.user_id = ?");
$stmt->execute([$user_id]);
$service = $stmt->fetch();

$percent = ($used_bytes / $service['total_volume']) * 100;
$remaining_days = (strtotime($service['expire_date']) - time()) / (60 * 60 * 24);
?>
<div class="container py-3" dir="rtl">
    <div class="card mb-3 border-0 shadow-sm bg-primary text-white">
        <div class="card-body">
            <h6>سرویس فعلی: <?= $service['plan_title'] ?></h6>
            <small>لوکیشن: <?= $service['loc_name'] ?> | آی‌پی ریموت: <?= $service['remote_ip'] ?></small>
        </div>
    </div>

    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <label class="d-flex justify-content-between mb-2">
                <span>مصرف حجم</span>
                <span><?= round($used_bytes / (1024**3), 2) ?> / <?= $service['total_volume'] / (1024**3) ?> GB</span>
            </label>
            <div class="progress">
                <div class="progress-bar bg-info" style="width: <?= $percent ?>%"></div>
            </div>
            
            <label class="d-flex justify-content-between mt-3 mb-2">
                <span>اعتبار زمانی</span>
                <span><?= round($remaining_days) ?> روز باقی‌مانده</span>
            </label>
            <div class="progress">
                <div class="progress-bar bg-warning" style="width: <?= ($remaining_days/30)*100 ?>%"></div>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-6">
            <a href="vpn_config.php" class="btn btn-dark w-100 py-3">دریافت کانفیگ</a>
        </div>
        <div class="col-6">
            <a href="switch_location.php" class="btn btn-outline-primary w-100 py-3">تغییر لوکیشن</a>
        </div>
    </div>
</div>