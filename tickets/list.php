<?php
// Prevent Header issues | جلوگیری از اختلال در هدایت صفحه
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Database Connection | اتصال به دیتابیس با مسیر اصلاح شده
require_once __DIR__ . '/../config/db.php';

// 2. Check Login | بررسی لاگین کاربر
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

// 3. New Ticket Logic | منطق ثبت تیکت جدید (حل مشکل دکمه)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_ticket'])) {
    $title = htmlspecialchars($_POST['title']);
    $message = htmlspecialchars($_POST['message']);

    try {
        $db->beginTransaction();
        
        // Insert into tickets table | ثبت تیکت
        $stmt = $db->prepare("INSERT INTO tickets (user_id, title, status) VALUES (?, ?, 'open')");
        $stmt->execute([$user_id, $title]);
        $ticket_id = $db->lastInsertId();
        
        // Insert first message | ثبت اولین پیام تیکت
        $msg_stmt = $db->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $msg_stmt->execute([$ticket_id, $user_id, $message]);
        
        $db->commit();
        header("Location: list.php?success=1");
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $error = "خطا در ثبت تیکت: " . $e->getMessage();
    }
}

// 4. Fetch Data | واکشی تیکت‌ها
$sql = $is_admin ? "SELECT t.*, u.mobile FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.updated_at DESC" 
                  : "SELECT * FROM tickets WHERE user_id = ? ORDER BY updated_at DESC";
$stmt = $db->prepare($sql);
$is_admin ? $stmt->execute() : $stmt->execute([$user_id]);
$tickets = $stmt->fetchAll();

// 5. Load Smart Header | فراخوانی هدر هوشمند واحد
include __DIR__ . '/../includes/header.php'; 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-persian-blue"><i class="bi bi-chat-square-dots-fill me-2"></i> مرکز پشتیبانی و تیکت</h4>
        <?php if(!$is_admin): ?>
            <button class="btn btn-persian btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                <i class="bi bi-plus-lg me-1"></i> ارسال تیکت جدید
            </button>
        <?php endif; ?>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 shadow-sm py-2">تیکت شما با موفقیت ثبت شد.</div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm py-2"><?= $error ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="p-3">موضوع</th>
                        <?= $is_admin ? '<th>کاربر</th>' : '' ?>
                        <th>وضعیت</th>
                        <th>آخرین بروزرسانی</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($tickets) > 0): ?>
                        <?php foreach($tickets as $t): ?>
                        <tr>
                            <td class="fw-bold p-3"><?= htmlspecialchars($t['title']) ?></td>
                            <?= $is_admin ? "<td><span class='badge bg-light text-dark border'>{$t['mobile']}</span></td>" : "" ?>
                            <td>
                                <span class="badge rounded-pill bg-<?= $t['status']=='open'?'success':'secondary' ?> px-3">
                                    <?= $t['status'] == 'open' ? 'درحال بررسی' : 'بسته شده' ?>
                                </span>
                            </td>
                            <td class="small text-muted text-ltr"><?= $t['updated_at'] ?></td>
                            <td>
                                <a href="chat.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary rounded-3 px-3">
                                    مشاهده <i class="bi bi-chevron-left ms-1"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">تیکتی جهت نمایش وجود ندارد.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="newTicketModal" tabindex="-1" aria-labelledby="newTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content border-0 shadow-lg rounded-4 text-end">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold text-persian-blue" id="newTicketModalLabel">ثبت تیکت پشتیبانی</h5>
                <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">موضوع تیکت:</label>
                    <input type="text" name="title" class="form-control shadow-none" placeholder="موضوع را کوتاه بنویسید" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">متن پیام:</label>
                    <textarea name="message" class="form-control shadow-none" rows="5" placeholder="جزئیات مشکل خود را شرح دهید..." required></textarea>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">انصراف</button>
                <button type="submit" name="create_ticket" class="btn btn-persian btn-sm px-4">ارسال پیام</button>
            </div>
        </form>
    </div>
</div>

<?php 
// 6. Load Smart Footer | فراخوانی فوتر هوشمند واحد
include __DIR__ . '/../includes/footer.php'; 
?>