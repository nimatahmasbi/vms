<?php
// Prevent Header issues | جلوگیری از اختلال در هدایت صفحه
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Database Connection | اتصال به دیتابیس
require_once __DIR__ . '/../config/db.php';

// 2. Check Login | بررسی لاگین کاربر
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$is_admin = ($role === 'admin');
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 3. Fetch Ticket Info | واکشی اطلاعات تیکت
$stmt = $db->prepare("SELECT t.*, u.first_name, u.last_name, u.mobile FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

// امنیت: بررسی دسترسی (کاربر فقط تیکت خودش را ببیند، ادمین همه را)
if (!$ticket || (!$is_admin && $ticket['user_id'] != $user_id)) {
    die("دسترسی غیرمجاز یا تیکت یافت نشد.");
}

// 4. Action: Send Message | عملیات ارسال پیام
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $message = htmlspecialchars($_POST['message']);
    
    if (!empty($message)) {
        // ثبت پیام در دیتابیس
        $msg_stmt = $db->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $msg_stmt->execute([$ticket_id, $user_id, $message]);

        // تغییر وضعیت هوشمند
        // Intelligent status update: if admin replies, set to 'replied'
        $new_status = $is_admin ? 'replied' : 'open';
        $update_stmt = $db->prepare("UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?");
        $update_stmt->execute([$new_status, $ticket_id]);

        header("Location: chat.php?id=" . $ticket_id);
        exit();
    }
}

// 5. Action: Close Ticket | عملیات بستن تیکت
if (isset($_POST['close_ticket'])) {
    $update_stmt = $db->prepare("UPDATE tickets SET status = 'closed', updated_at = NOW() WHERE id = ?");
    $update_stmt->execute([$ticket_id]);
    header("Location: chat.php?id=" . $ticket_id);
    exit();
}

// 6. Fetch Messages | واکشی تمام پیام‌های این تیکت
$msg_stmt = $db->prepare("SELECT tm.*, u.first_name, u.last_name, u.role FROM ticket_messages tm JOIN users u ON tm.user_id = u.id WHERE tm.ticket_id = ? ORDER BY tm.created_at ASC");
$msg_stmt->execute([$ticket_id]);
$messages = $msg_stmt->fetchAll();

// 7. Load Smart Header | فراخوانی هدر هوشمند
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="fw-bold text-persian-blue mb-1">موضوع: <?= htmlspecialchars($ticket['title']) ?></h5>
                <small class="text-muted">
                    <i class="bi bi-person me-1"></i> <?= $ticket['first_name'] . ' ' . $ticket['last_name'] ?> 
                    (<?= $ticket['mobile'] ?>)
                </small>
            </div>
            <div class="text-end">
                <span class="badge rounded-pill bg-<?= $ticket['status'] == 'open' ? 'success' : ($ticket['status'] == 'replied' ? 'info' : 'secondary') ?> px-3 py-2 mb-2">
                    <?php 
                        if($ticket['status'] == 'open') echo 'در انتظار بررسی';
                        elseif($ticket['status'] == 'replied') echo 'پاسخ داده شده';
                        else echo 'بسته شده';
                    ?>
                </span>
                <div class="d-flex gap-2">
                    <a href="list.php" class="btn btn-light btn-sm border">بازگشت <i class="bi bi-arrow-left"></i></a>
                    <?php if($ticket['status'] != 'closed'): ?>
                        <form method="post" onsubmit="return confirm('آیا از بستن این تیکت اطمینان دارید؟');">
                            <button type="submit" name="close_ticket" class="btn btn-outline-danger btn-sm">بستن تیکت</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-light p-3 mb-4" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
        <?php foreach($messages as $m): 
            $is_my_msg = ($m['user_id'] == $user_id);
            $is_msg_admin = ($m['role'] == 'admin');
        ?>
            <div class="d-flex mb-4 <?= $is_my_msg ? 'justify-content-start' : 'justify-content-end' ?>">
                <div class="message-bubble shadow-sm p-3 rounded-4 <?= $is_msg_admin ? 'bg-persian-blue text-white' : 'bg-white' ?>" style="max-width: 80%;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-bold"><?= $is_msg_admin ? 'پشتیبانی (ادمین)' : $m['first_name'] ?></small>
                        <small class="<?= $is_msg_admin ? 'text-white-50' : 'text-muted' ?> ms-3" style="font-size: 10px;"><?= $m['created_at'] ?></small>
                    </div>
                    <div class="message-text">
                        <?= nl2br(htmlspecialchars($m['message'])) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if($ticket['status'] != 'closed'): ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">متن پاسخ شما:</label>
                    <textarea name="message" class="form-control shadow-none border-light-subtle" rows="4" placeholder="اینجا بنویسید..." required></textarea>
                </div>
                <div class="text-start">
                    <button type="submit" name="send_message" class="btn btn-persian px-5 py-2 fw-bold">
                        ارسال پاسخ <i class="bi bi-send-fill ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary text-center border-0 rounded-4 shadow-sm">
            این تیکت بسته شده است و امکان ارسال پیام جدید وجود ندارد.
        </div>
    <?php endif; ?>
</div>

<style>
/* Chat Bubbles Custom Styles */
/* استایل سفارشی حباب‌های چت */
.bg-persian-blue {
    background-color: #008080 !important; /* Persian Turquoise / Blue */
}
.message-bubble {
    position: relative;
    line-height: 1.6;
}
.justify-content-start .message-bubble {
    border-bottom-right-radius: 2px !important;
}
.justify-content-end .message-bubble {
    border-bottom-left-radius: 2px !important;
    background-color: #e9ecef; /* Light gray for others */
}
</style>

<?php 
// 8. Load Smart Footer | فراخوانی فوتر هوشمند
include __DIR__ . '/../includes/footer.php'; 
?>