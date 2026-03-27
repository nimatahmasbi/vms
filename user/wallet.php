<?php
// Wallet and Transactions View | مشاهده کیف پول و تراکنش‌ها
session_start();
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$transactions = $db->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$transactions->execute([$user_id]);

$user_data = $db->prepare("SELECT wallet FROM users WHERE id = ?");
$user_data->execute([$user_id]);
$current_balance = $user_data->fetchColumn();
?>
<div class="container py-4" dir="rtl">
    <div class="alert alert-info shadow-sm text-center">
        <h5>موجودی فعلی شما: <?= number_format($current_balance) ?> تومان</h5>
        <button class="btn btn-outline-primary btn-sm mt-2">شارژ آنلاین کیف پول</button>
    </div>
    
    <h6>تاریخچه تراکنش‌ها</h6>
    <div class="list-group">
        <?php foreach($transactions as $trx): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <span><?= $trx['description'] ?> (<?= $trx['type'] ?>)</span>
            <span class="badge bg-<?= ($trx['amount']>0)?'success':'danger' ?> rounded-pill">
                <?= number_format($trx['amount']) ?>
            </span>
            <small class="text-muted"><?= $trx['created_at'] ?></small>
        </div>
        <?php endforeach; ?>
    </div>
</div>