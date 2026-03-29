<?php
// ۱. شروع سشن و بافر خروجی
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

// ۲. امنیت: فقط ادمین اجازه ورود دارد
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /auth/login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

$message = "";

// ۳. عملیات مدیریت (تغییر موجودی، وضعیت و حذف)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // الف) تغییر موجودی کیف پول
    if (isset($_POST['update_wallet'])) {
        $u_id = intval($_POST['user_id']);
        $new_wallet = intval($_POST['wallet_amount']);
        $update = $db->prepare("UPDATE users SET wallet = ? WHERE id = ?");
        $update->execute([$new_wallet, $u_id]);
        $message = "<div class='alert alert-success py-2 small'>موجودی کاربر با موفقیت بروزرسانی شد.</div>";
    }
    
    // ب) تغییر وضعیت کاربر (فعال/غیرفعال)
    if (isset($_POST['toggle_status'])) {
        $u_id = intval($_POST['user_id']);
        $current_status = $_POST['current_status'];
        $new_status = ($current_status == 'active') ? 'inactive' : 'active';
        $update = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
        $update->execute([$new_status, $u_id]);
    }

    // ج) حذف کاربر
    if (isset($_POST['delete_user'])) {
        $u_id = intval($_POST['user_id']);
        $delete = $db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $delete->execute([$u_id]);
        $message = "<div class='alert alert-warning py-2 small'>کاربر مورد نظر از سیستم حذف شد.</div>";
    }
}

// ۴. واکشی لیست کاربران (به جز ادمین فعلی یا همه کاربران)
$stmt = $db->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();

// ۵. فراخوانی هدر هوشمند واحد
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-persian-blue"><i class="bi bi-people-fill me-2"></i> مدیریت کاربران سامانه</h4>
        <span class="badge bg-persian-blue px-3 py-2">تعداد کل: <?= count($users) ?> نفر</span>
    </div>

    <?= $message ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="p-3">نام و نام خانوادگی</th>
                        <th>شماره موبایل</th>
                        <th>موجودی کیف پول</th>
                        <th>وضعیت</th>
                        <th>نقش</th>
                        <th class="text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td class="p-3">
                            <div class="fw-bold"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                            <small class="text-muted" style="font-size: 10px;">عضویت: <?= $u['created_at'] ?? '---' ?></small>
                        </td>
                        <td><code class="text-primary"><?= $u['mobile'] ?></code></td>
                        <td>
                            <form method="post" class="d-flex align-items-center" style="max-width: 180px;">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="number" name="wallet_amount" class="form-control form-control-sm text-center me-1 border-light-subtle" value="<?= $u['wallet'] ?>">
                                <button type="submit" name="update_wallet" class="btn btn-sm btn-success" title="بروزرسانی مبلغ">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $u['status'] ?>">
                                <button type="submit" name="toggle_status" class="btn btn-sm rounded-pill px-3 bg-<?= $u['status']=='active'?'success':'danger' ?> text-white border-0" style="font-size: 11px;">
                                    <?= $u['status'] == 'active' ? 'فعال' : 'غیرفعال' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <span class="badge <?= $u['role']=='admin'?'bg-warning text-dark':'bg-light text-dark' ?> border">
                                <?= $u['role'] == 'admin' ? 'مدیر' : 'کاربر عادی' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="/admin/user_details.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" title="مشاهده سرویس‌ها">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if($u['role'] != 'admin'): ?>
                                <form method="post" onsubmit="return confirm('آیا از حذف این کاربر مطمئن هستید؟ این عمل غیرقابل بازگشت است.');" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger border-start-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* استایل اختصاصی برای دکمه‌های این صفحه */
.btn-persian-blue {
    background-color: #1C39BB;
    color: white;
}
.bg-persian-blue {
    background-color: #1C39BB !important;
}
.table-hover tbody tr:hover {
    background-color: rgba(28, 57, 187, 0.03);
}
</style>

<?php 
// ۶. فراخوانی فوتر هوشمند واحد
include __DIR__ . '/../includes/footer.php'; 
?>