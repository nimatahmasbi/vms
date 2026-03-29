<?php include 'includes/header.php'; ?>

<div class="auth-bg text-white py-5 d-flex align-items-center" style="min-height: 80vh;">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">سامانه هوشمند VMS</h1>
        <p class="lead mb-5 text-persian-turquoise">مدیریت یکپارچه، سریع و امن سرورهای میکروتیک</p>
        
        <div class="d-flex gap-3 justify-content-center">
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="auth/login.php" class="btn btn-persian btn-lg px-5 shadow"><i class="bi bi-box-arrow-in-right me-2"></i> ورود به پنل</a>
                <a href="auth/register.php" class="btn btn-outline-light btn-lg px-5"><i class="bi bi-person-plus me-2"></i> ثبت‌نام کاربر جدید</a>
            <?php else: ?>
                <a href="user/dashboard.php" class="btn btn-persian btn-lg px-5">ورود به داشبورد کاربری</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row text-center g-4">
        <div class="col-md-4">
            <div class="card card-persian p-4 h-100">
                <i class="bi bi-speedometer2 display-4 text-persian-turquoise mb-3"></i>
                <h5>سرعت بالا</h5>
                <p class="text-muted small">اتصال آنی و مدیریت پهنای باند سرورها</p>
            </div>
        </div>
        </div>
</div>

<?php include 'includes/footer.php'; ?>