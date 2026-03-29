<?php include '../includes/header.php'; ?>

<div class="auth-bg d-flex align-items-center justify-content-center">
    <div class="card card-persian p-4 shadow-lg" style="max-width: 400px; width: 90%;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-persian-blue">بازیابی رمز</h3>
            <p class="text-muted small">شماره موبایل خود را وارد کنید</p>
        </div>
        
        <form method="post">
            <div class="mb-4">
                <input type="text" name="mobile" class="form-control text-center shadow-none" placeholder="09XXXXXXXXX" required>
            </div>
            <button type="submit" class="btn btn-persian w-100 py-2 shadow">ارسال کد تایید پیامکی</button>
        </form>
        
        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none text-muted small">بازگشت به ورود</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>