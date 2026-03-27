<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }

$ticket_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin');

// Security Check | بررسی امنیت دسترسی
$stmt = $db->prepare($is_admin ? "SELECT * FROM tickets WHERE id = ?" : "SELECT * FROM tickets WHERE id = ? AND user_id = ?");
$is_admin ? $stmt->execute([$ticket_id]) : $stmt->execute([$ticket_id, $user_id]);
$ticket = $stmt->fetch();

if (!$ticket) die("Ticket Access Denied | دسترسی غیرمجاز");

// Send Reply | ارسال پاسخ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_reply'])) {
    $msg = htmlspecialchars($_POST['message']);
    $db->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message) VALUES (?, ?, ?)")->execute([$ticket_id, $user_id, $msg]);
    
    // ادمین پاسخ داد وضعیت باز بماند، کاربر پاسخ داد هم همینطور
    $db->prepare("UPDATE tickets SET updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$ticket_id]);
}

$messages = $db->prepare("SELECT m.*, u.role, u.first_name FROM ticket_messages m JOIN users u ON m.user_id = u.id WHERE m.ticket_id = ? ORDER BY m.created_at ASC");
$messages->execute([$ticket_id]);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/bootstrap.rtl.min.css">
    <title>تیکت #<?= $ticket_id ?></title>
    <style>
        .chat-container { max-width: 700px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; }
        .bubble { padding: 10px 15px; border-radius: 15px; margin-bottom: 10px; max-width: 85%; }
        .bubble-admin { background: #f1f1f1; margin-right: auto; }
        .bubble-user { background: #d1ecf1; margin-left: auto; }
    </style>
</head>
<body class="bg-light p-4">
    <div class="chat-container shadow-sm">
        <div class="border-bottom mb-3 pb-2 d-flex justify-content-between">
            <h5><?= htmlspecialchars($ticket['title']) ?></h5>
            <a href="list.php" class="btn btn-sm btn-outline-secondary">بازگشت</a>
        </div>
        
        <div class="d-flex flex-column">
            <?php foreach($messages as $m): ?>
                <div class="bubble <?= $m['role'] == 'admin' ? 'bubble-admin' : 'bubble-user' ?>">
                    <small class="d-block text-muted"><?= $m['role'] == 'admin' ? 'پشتیبانی' : 'کاربر' ?></small>
                    <?= nl2br(htmlspecialchars($m['message'])) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="post" class="mt-4 border-top pt-3">
            <textarea name="message" class="form-control mb-2" rows="3" placeholder="متن پاسخ..." required></textarea>
            <button name="send_reply" class="btn btn-primary w-100">ارسال پاسخ</button>
        </form>
    </div>
</body>
</html>