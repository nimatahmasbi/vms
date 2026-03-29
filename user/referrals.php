<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
$user_id = $_SESSION['user_id'];

// واکشی لیست زیرمجموعه‌ها و جمع سود تراکنشی از نوع commission
$refs = $db->prepare("SELECT first_name, last_name, mobile, created_at FROM users WHERE referrer_id = ?");
$refs->execute([$user_id]);
$referrals = $refs->fetchAll();

$profit = $db->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'commission'");
$profit->execute([$user_id]);
$total_profit = $profit->fetchColumn() ?: 0;

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include __DIR__ . '/../../includes/header.php';
?>

<div id="content-area">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-persian-blue">کسب درآمد و زیرمجموعه‌ها</h4>
        <div class="bg-success text-white px-3 py-2 rounded-3 shadow-sm">
            <small>سود کل شما:</small> <b class="ms-1"><?= number_format($total_profit) ?> تومان</b>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <table class="table table-hover align-middle mb-0 text-center">
            <thead class="table-dark">
                <tr>
                    <th class="p-3">نام کاربر</th>
                    <th>شماره موبایل</th>
                    <th>تاریخ عضویت</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($referrals) > 0): ?>
                    <?php foreach($referrals as $r): ?>
                    <tr>
                        <td class="p-3 fw-bold"><?= $r['first_name'].' '.$r['last_name'] ?></td>
                        <td><?= $r['mobile'] ?></td>
                        <td class="small text-muted"><?= $r['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="py-5 text-muted">هنوز کسی با شماره شما معرفی نشده است.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include __DIR__ . '/../../includes/footer.php'; ?>