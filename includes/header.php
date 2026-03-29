<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current_uri = $_SERVER['REQUEST_URI'];
$is_auth_page = (strpos($current_uri, '/auth/') !== false);
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

if (!isset($_SESSION['user_id']) && !$is_auth_page && $current_uri != '/' && strpos($current_uri, 'index.php') === false) {
    header("Location: /auth/login.php"); exit;
}
require_once __DIR__ . '/../config/db.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <title>VMS PANEL</title>
</head>
<body>
<div class="main-wrapper">
    <?php if (!$is_auth_page): ?>
    <div id="sidebar">
        <div class="p-4 text-center border-bottom border-secondary">
            <h5 class="fw-bold mb-0 text-persian-turquoise">VMS PANEL</h5>
            <small class="text-muted"><?= $is_admin ? 'مدیریت کل' : 'پنل کاربری' ?></small>
        </div>
        <div class="sidebar-nav py-3">
            <?php include __DIR__ . '/sidebar.php'; ?>
        </div>
    </div>
    <?php endif; ?>

    <div id="main-content">
        <?php if (!$is_auth_page): ?>
        <nav class="top-nav justify-content-between px-4">
            <div class="fw-bold text-secondary"><i class="bi bi-list me-2"></i> سامانه هوشمند VMS</div>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle shadow-sm border" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle text-persian-blue"></i> 
                    <span class="ms-1"><?= $_SESSION['name'] ?? 'کاربر' ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 text-end">
                    <li><a class="dropdown-item py-2 ajax-link" data-url="/includes/profile.php" href="#"><i class="bi bi-person me-2"></i> پروفایل</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger" href="/auth/logout.php"><i class="bi bi-power me-2"></i> خروج</a></li>
                </ul>
            </div>
        </nav>
        <?php endif; ?>
        
        <div class="content-body" id="content-area">