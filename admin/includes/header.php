<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php"); exit; }
require_once '../config/db.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/bootstrap-icons.css">
    <title>پنل مدیریت VMS</title>
</head>
<body class="bg-light">
<div class="main-wrapper">
    <div id="sidebar">
        <div class="p-4 text-center border-bottom border-secondary">
            <h5 class="fw-bold mb-0 text-persian-turquoise">VMS ULTIMATE</h5>
            <small class="text-muted">نسخه هوشمند ۲۰۲۶</small>
        </div>
        <div class="py-3">
            <?php include 'sidebar.php'; ?>
        </div>
    </div>

    <div id="main-content">
        <div class="top-nav justify-content-between">
            <div class="fw-bold text-secondary"><i class="bi bi-shield-lock-fill me-2 text-persian-blue"></i> پنل نظارت و مدیریت سیستم</div>
            <div class="d-flex align-items-center">
                <span class="me-3 small text-muted"><?= $_SESSION['mobile'] ?? 'Admin' ?></span>
                <a href="../../auth/logout.php" class="btn btn-sm btn-outline-danger px-3"><i class="bi bi-power"></i> خروج</a>
            </div>
        </div>
        <div class="content-body">