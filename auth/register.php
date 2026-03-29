<?php include '../includes/header.php'; ?>

<div class="auth-bg d-flex align-items-center justify-content-center py-5">
    <div class="card card-persian p-4 shadow-lg" style="max-width: 450px; width: 90%;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-persian-blue">عضویت سریع</h3>
            <p class="text-muted small">اطلاعات خود را دقیق وارد کنید</p>
        </div>
        
        <form method="post">
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <input type="text" name="f_name" class="form-control shadow-none" placeholder="نام" required>
                </div>
                <div class="col-6">
                    <input type="text" name="l_name" class="form-control shadow-none" placeholder="نام خانوادگی" required>
                </div>
            </div>
            <div class="mb-3">
                <input type="text" name="mobile" class="form-control shadow-none text-center" placeholder="شماره موبایل (نام کاربری)" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control shadow-none text-center" placeholder="رمز عبور (حداقل ۸ کاراکتر)" required>
            </div>
            <button type="submit" class="btn btn-persian w-100 py-2 shadow"><i class="bi bi-person-plus me-2"></i> ثبت‌نام و ایجاد حساب</button>
        </form>
        
        <div class="text-center mt-4 border-top pt-3">
            <p class="small text-muted">قبلاً ثبت‌نام کرده‌اید؟ <a href="login.php" class="text-decoration-none fw-bold text-persian-blue">وارد شوید</a></p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>