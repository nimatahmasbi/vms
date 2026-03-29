<?php
// ۱. فعال کردن نمایش خطا برای پیدا کردن علت دقیق خطای ۵۰۰
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

// ۲. بررسی دسترسی
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// ۳. اتصال به دیتابیس با مدیریت خطا
try {
    require_once '../config/db.php';
} catch (Exception $e) {
    die("خطا در بارگذاری تنظیمات دیتابیس: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// ۴. واکشی اطلاعات کاربر
$user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}

// ۵. واکشی سرویس فعال با مدیریت خطای عدم وجود جدول
$active_srv = false;
try {
    $stmt_srv = $db->prepare("
        SELECT s.*, p.title as plan_title, p.volume_bytes 
        FROM user_subscriptions s 
        LEFT JOIN plans p ON s.plan_id = p.id 
        WHERE s.user_id = ? AND s.status = 'active' 
        LIMIT 1
    ");
    $stmt_srv->execute([$user_id]);
    $active_srv = $stmt_srv->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // اگر جدول وجود نداشت، متغیر را خالی می‌گذارد تا صفحه کرش نکند
    $active_srv = false;
}

// محاسبه درصد مصرف
$usage_percent = 0;
if ($active_srv && $active_srv['volume_bytes'] > 0) {
    $usage_percent = round(($active_srv['used_traffic'] / $active_srv['volume_bytes']) * 100, 1);
}

// ۶. فراخوانی هدر (با فرض اینکه پوشه includes در ریشه است)
if (file_exists('../includes/header.php')) {
    include '../includes/header.php';
} else {
    die("فایل هدر پیدا نشد. مسیر را چک کنید.");
}
?>

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-12">
            <div class="card card-persian shadow-sm p-4 bg-white border-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="fw-bold text-persian-blue">سلام، <?= htmlspecialchars($user['first_name'] ?? 'کاربر') ?> عزیز!</h4>
                        <p class="text-muted mb-0 small">به پنل مدیریت سرویس‌های خود خوش آمدید.</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <div class="bg-light p-3 rounded-4 border d-flex align-items-center">
                            <i class="bi bi-wallet2 fs-4 text-persian-turquoise me-3"></i>
                            <div>
                                <small class="text-muted d-block">موجودی کیف پول</small>
                                <span class="fw-bold"><?= number_format($user['wallet'] ?? 0) ?> تومان</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($active_srv): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="d-flex align-items-center">
                        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4 me-3">
                            <i class="bi bi-cpu fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">سرویس فعال</small>
                            <span class="fw-bold"><?= htmlspecialchars($active_srv['plan_title']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h6 class="fw-bold mb-3 small">جزئیات اتصال</h6>
                    <div class="alert bg-light border-0 text-break text-ltr mb-3" style="font-size: 0.85rem;">
                        <?= htmlspecialchars($active_srv['config_link'] ?? 'کانفیگ یافت نشد') ?>
                    </div>
                    <button class="btn btn-persian btn-sm px-4" onclick="navigator.clipboard.writeText('<?= $active_srv['config_link'] ?>').alert('کپی شد!')">
                        کپی لینک اتصال
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="card card-persian p-5 border-0 shadow-sm">
                    <i class="bi bi-cart-x fs-1 text-muted mb-3"></i>
                    <h5>هنوز هیچ سرویسی خریداری نکرده‌اید!</h5>
                    <a href="services/purchase.php" class="btn btn-persian mt-3 px-5">خرید سرویس</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>