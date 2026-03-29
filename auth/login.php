<?php
/**
 * VMS Login Page - Fixed for New Database Structure
 * English|Persian format.
 */
session_start();
require_once __DIR__ . '/../config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];

    // واکشی اطلاعات کاربر بر اساس شماره موبایل
    $stmt = $db->prepare("SELECT * FROM users WHERE mobile = ?");
    $stmt->execute([$mobile]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // بررسی وضعیت فعال بودن حساب کاربری
        if ($user['status'] === 'active') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . " " . $user['last_name'];

            // هدایت بر اساس نقش کاربری
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../user/dashboard.php");
            }
            exit();
        } else {
            $error = "حساب کاربری شما فعال نیست. لطفاً با مدیریت تماس بگیرید.";
        }
    } else {
        $error = "شماره موبایل یا رمز عبور اشتباه است.";
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.css">
    <title>ورود به پنل VMS</title>
    <style>
        body { background: #f0f2f5; font-family: 'Tahoma', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-card { width: 100%; max-width: 400px; border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); background: #fff; padding: 40px; }
        .btn-persian { background: #1C39BB; color: #fff; border-radius: 12px; padding: 12px; border: none; width: 100%; }
        .btn-persian:hover { background: #152a8a; }
        .form-control { border-radius: 10px; padding: 12px; text-align: center; direction: ltr; margin-bottom: 20px; border: 1px solid #ddd; }
        .form-label { display: block; text-align: right; font-weight: bold; margin-bottom: 8px; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="login-card">
    <h4 class="text-center fw-bold text-primary mb-4">ورود به پنل کاربری</h4>
    
    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small text-center"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">شماره موبایل:</label>
            <input type="text" name="mobile" class="form-control shadow-none" placeholder="09120000000" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label">رمز عبور:</label>
            <input type="password" name="password" class="form-control shadow-none" placeholder="******" required>
        </div>
        <button type="submit" class="btn btn-persian shadow">ورود به سیستم</button>
        
        <div class="text-center mt-4">
            <a href="forgot-password.php" class="small text-decoration-none text-muted">رمز عبور را فراموش کرده‌اید؟</a>
        </div>
    </form>
</div>

</body>
</html>