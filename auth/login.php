<?php
// Secure User Login | ورود ایمن کاربر
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE mobile = ?");
    $stmt->execute([$mobile]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] == 'active') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'];
            
            // هدایت بر اساس نقش کاربری
            $path = ($user['role'] == 'admin') ? '../../admin/dashboard.php' : '../dashboard.php';
            header("Location: $path");
        } else {
            $error = "Account is pending or blocked | حساب شما در انتظار تایید یا مسدود است";
        }
    } else {
        $error = "Invalid Mobile or Password | موبایل یا رمز عبور اشتباه است";
    }
}
?>
<div class="container mt-5" dir="rtl">
    <div class="card p-4 shadow-sm mx-auto" style="max-width: 400px;">
        <h4 class="text-center">ورود به پنل هوشمند</h4>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="post">
            <input type="text" name="mobile" class="form-control mb-3" placeholder="شماره موبایل" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="رمز عبور" required>
            <button class="btn btn-primary w-100">ورود به حساب</button>
        </form>
    </div>
</div>