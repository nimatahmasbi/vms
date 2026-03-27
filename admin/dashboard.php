<?php
// Admin Overview Dashboard | پیش‌خوان مدیریت
include 'includes/header.php';
session_start();
require_once '../config/db.php';
if ($_SESSION['role'] !== 'admin') die("Access Denied");

// دریافت آمارها
$total_users = $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$total_resellers = $db->query("SELECT COUNT(*) FROM users WHERE role='reseller'")->fetchColumn();
$active_services = $db->query("SELECT COUNT(*) FROM services WHERE status='active'")->fetchColumn();
$open_tickets = $db->query("SELECT COUNT(*) FROM tickets WHERE status='open'")->fetchColumn();
$total_wallet = $db->query("SELECT SUM(wallet) FROM users")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="manifest" href="../manifest.json">
    <title>پنل مدیریت VMS</title>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand">مدیریت هوشمند VMS</span>
            <a href="../user/auth/logout.php" class="btn btn-outline-danger btn-sm">خروج</a>
        </div>
    </nav>

    <div class="container">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-primary">
                    <h6>کاربران عادی</h6>
                    <h3><?= $total_users ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-success">
                    <h6>نمایندگان</h6>
                    <h3><?= $total_resellers ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-info">
                    <h6>سرویس‌های فعال</h6>
                    <h3><?= $active_services ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-danger">
                    <h6>تیکت‌های باز</h6>
                    <h3><?= $open_tickets ?></h3>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">دسترسی سریع</div>
                    <div class="card-body">
                        <a href="users_manage.php" class="btn btn-outline-primary m-1">مدیریت کاربران</a>
                        <a href="locations_setup.php" class="btn btn-outline-secondary m-1">تنظیمات لوکیشن</a>
                        <a href="plans_manage.php" class="btn btn-outline-success m-1">تعریف پلن فروش</a>
                        <a href="settings.php" class="btn btn-outline-dark m-1">تنظیمات سیستم</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white text-primary">وضعیت مالی</div>
                    <div class="card-body text-center">
                        <small class="text-muted">مجموع موجودی کیف پول‌ها:</small>
                        <h4><?= number_format($total_wallet) ?> تومان</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<?php include 'includes/footer.php'; ?>
</html>