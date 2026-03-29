<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection | اتصال به دیتابیس
require_once __DIR__ . '/../config/db.php';

$error = "";

// If already logged in, redirect away from login page | اگر قبلا وارد شده، به داشبورد منتقل شود
if (isset($_SESSION['user_id'])) {
    $redirect = ($_SESSION['role'] === 'admin') ? "../admin/dashboard.php" : "../user/dashboard.php";
    header("Location: $redirect");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE mobile = ?");
    $stmt->execute([$mobile]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'active') {
            // Set Session Data | تنظیم اطلاعات سشن
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['mobile'] = $user['mobile'];
            
            // Critical: Save and close session before redirect | ذخیره سشن قبل از انتقال
            session_write_close();

            if ($user['role'] === 'admin') {
                header("Location: /admin/dashboard.php");
            } else {
                header("Location: /user/dashboard.php");
            }
            exit;
        } else {
            $error = "حساب کاربری شما فعال نیست.";
        }
    } else {
        $error = "شماره موبایل یا رمز عبور اشتباه است.";
    }
}

// Load Header | لود کردن هدر
include __DIR__ . '/../includes/header.php';
?>

<div class="auth-bg d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card card-persian p-4 shadow-lg border-0" style="max-width: 400px; width: 90%;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-persian-blue">ورود به پنل</h3>
            <p class="text-muted small">خوش آمدید، لطفاً وارد شوید</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger py-2 small text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">شماره موبایل:</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-start-0"><i class="bi bi-phone text-muted"></i></span>
                    <input type="text" name="mobile" class="form-control text-center shadow-none border-end-0" placeholder="09XXXXXXXXX" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-secondary">رمز عبور:</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-start-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control text-center shadow-none border-end-0" required>
                </div>
            </div>
            <button type="submit" class="btn btn-persian w-100 py-2 shadow-sm fw-bold">ورود به حساب کاربری</button>
        </form>
        
        <div class="text-center mt-4 border-top pt-3">
            <p class="small text-muted">حساب ندارید؟ <a href="register.php" class="text-decoration-none fw-bold text-persian-blue">ثبت‌نام کنید</a></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>