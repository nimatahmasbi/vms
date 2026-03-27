<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }

$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

// فراخوانی هدر بر اساس نقش
if ($is_admin) {
    include '../admin/includes/header.php';
} else {
    echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><link rel="stylesheet" href="../assets/css/bootstrap.rtl.min.css"><link rel="stylesheet" href="../assets/css/style.css"></head><body class="bg-light p-4"><div class="container shadow-sm p-4 bg-white rounded-4">';
}

$sql = $is_admin ? "SELECT t.*, u.mobile FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.updated_at DESC" 
                  : "SELECT * FROM tickets WHERE user_id = ? ORDER BY updated_at DESC";
$stmt = $db->prepare($sql);
$is_admin ? $stmt->execute() : $stmt->execute([$_SESSION['user_id']]);
$tickets = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-persian-blue"><i class="bi bi-chat-square-dots-fill me-2"></i> مرکز تیکت‌های پشتیبانی</h4>
    <?php if(!$is_admin): ?>
        <button class="btn btn-persian btn-sm" data-bs-toggle="modal" data-bs-target="#newTicket">ثبت تیکت جدید</button>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th class="p-3">موضوع تیکت</th>
                <?= $is_admin ? '<th>شماره کاربر</th>' : '' ?>
                <th>وضعیت</th>
                <th>آخرین فعالیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($tickets as $t): ?>
            <tr>
                <td class="fw-bold p-3"><?= htmlspecialchars($t['title']) ?></td>
                <?= $is_admin ? '<td><span class="badge bg-light text-dark">'.$t['mobile'].'</span></td>' : '' ?>
                <td>
                    <span class="badge rounded-pill bg-<?= $t['status']=='open'?'success':'secondary' ?>">
                        <?= $t['status'] == 'open' ? 'درحال پیگیری' : 'بسته شده' ?>
                    </span>
                </td>
                <td class="small text-muted"><?= $t['updated_at'] ?></td>
                <td><a href="chat.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary px-3">نمایش گفتگو</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php 
if ($is_admin) {
    include '../admin/includes/footer.php';
} else {
    echo '</div></body></html>';
}
?>