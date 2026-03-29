<?php
// ۱. شروع سشن و بافر خروجی
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

// ۲. امنیت: فقط ادمین
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /auth/login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

// ۳. دریافت ID کاربر از URL
$u_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($u_id <= 0) {
    header("Location: /admin/users_manage.php");
    exit();
}

// ۴. واکشی اطلاعات کلی کاربر
$stmt_user = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$u_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("کاربر یافت نشد.");
}

// ۵. واکشی لیست سرویس‌های این کاربر (اشتراک‌ها)
$stmt_subs = $db->prepare("
    SELECT s.*, p.title as plan_title 
    FROM user_subscriptions s 
    LEFT JOIN plans p ON s.plan_id = p.id 
    WHERE s.user_id = ? 
    ORDER BY s.id DESC
");
$stmt_subs->execute([$u_id]);
$subscriptions = $stmt_subs->fetchAll(PDO::FETCH_ASSOC);

// ۶. فراخوانی هدر هوشمند واحد
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-persian-blue">
            <i class="bi bi-person-badge me-2"></i> جزئیات حساب: <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
        </h4>
        <a href="/admin/users_manage.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i> بازگشت به لیست
        </a>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white text-center">
                <div class="mb-3">
                    <i class="bi bi-person-circle display-1 text-persian-blue opacity-25"></i>
                </div>
                <h5 class="fw-bold"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                <p class="text-muted small mb-3"><?= $user['mobile'] ?></p>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">موجودی فعلی:</span>
                    <span class="fw-bold text-success"><?= number_format($user['wallet']) ?> تومان</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">وضعیت حساب:</span>
                    <span class="badge rounded-pill bg-<?= $user['status'] == 'active' ? 'success' : 'danger' ?>">
                        <?= $user['status'] == 'active' ? 'فعال' : 'مسدود' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">تاریخ ثبت‌نام:</span>
                    <span class="small"><?= $user['created_at'] ?? 'نامشخص' ?></span>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-persian-blue"><i class="bi bi-cpu me-2"></i> سرویس‌ها و اشتراک‌ها</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="p-3">نام پلن</th>
                                <th>ترافیک مصرفی</th>
                                <th>تاریخ انقضا</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($subscriptions) > 0): ?>
                                <?php foreach($subscriptions as $sub): 
                                    $usage_percent = ($sub['volume_bytes'] > 0) ? round(($sub['used_traffic'] / $sub['volume_bytes']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td class="p-3 fw-bold"><?= htmlspecialchars($sub['plan_title']) ?></td>
                                    <td>
                                        <div class="progress" style="height: 6px; width: 100px;">
                                            <div class="progress-bar bg-persian-blue" role="progressbar" style="width: <?= $usage_percent ?>%"></div>
                                        </div>
                                        <small class="text-muted" style="font-size: 10px;"><?= $usage_percent ?>% مصرف شده</small>
                                    </td>
                                    <td class="text-ltr small"><?= $sub['expire_date'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $sub['status'] == 'active' ? 'success' : 'secondary' ?> py-1 px-2">
                                            <?= $sub['status'] == 'active' ? 'فعال' : 'منقضی' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" title="قطع دسترسی اجباری">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted small">کاربر هیچ سرویس فعالی ندارد.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// ۷. فراخوانی فوتر هوشمند واحد
include __DIR__ . '/../includes/footer.php'; 
?>