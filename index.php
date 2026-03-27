<?php
// Application Entry Point | نقطه ورود اپلیکیشن
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
} else {
    header("Location: user/auth/login.php");
}
exit;