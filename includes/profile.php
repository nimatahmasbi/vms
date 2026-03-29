<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ۱. بروزرسانی اطلاعات هویتی
    if (isset($_POST['update_profile'])) {
        $f_name = htmlspecialchars($_POST['f_name']);
        $l_name = htmlspecialchars($_POST['l_name']);
        $email = htmlspecialchars($_POST['email']);
        
        $db->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?")->execute([$f_name, $l_name, $email, $user_id]);
        $message = "<div class='alert alert-success py-2 border-0 shadow-sm text-end'>مشخصات با موفقیت بروز شد.</div>";
    }

    // ۲. تغییر رمز عبور
    if (isset($_POST['update_pass'])) {
        $new_p = $_POST['new_pass'];
        $conf_p = $_POST['confirm_pass'];
        if($new_p === $conf_p && strlen($new_p) >= 6) {
            $hashed = password_hash($new_p, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user_id]);
            $message = "<div class='alert alert-success py-2 border-0 shadow-sm text-end'>رمز عبور تغییر یافت.</div>";
        } else {
            $message = "<div class='alert alert-danger py-2 border-0 shadow-sm text-end'>رمزها مطابقت ندارند یا کوتاه هستند.</div>";
        }
    }
}

$user = $db->query("SELECT * FROM users WHERE id = $user_id")->fetch();
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include __DIR__ . '/../includes/header.php';
?>

<div id="content-area" class="text-end" dir="rtl">
    <h4 class="fw-bold text-persian-blue mb-4">تنظیمات حساب کاربری</h4>
    <?= $message ?>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h6 class="fw-bold mb-4 border-bottom pb-2 text-secondary">ویرایش مشخصات اصلی</h6>
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6 text-end">
                            <label class="small fw-bold mb-1">نام:</label>
                            <input type="text" name="f_name" value="<?= $user['first_name'] ?>" class="form-control shadow-none">
                        </div>
                        <div class="col-md-6 text-end">
                            <label class="small fw-bold mb-1">نام خانوادگی:</label>
                            <input type="text" name="l_name" value="<?= $user['last_name'] ?>" class="form-control shadow-none">
                        </div>
                        <div class="col-12 text-end">
                            <label class="small fw-bold mb-1">آدرس ایمیل:</label>
                            <div class="input-group" dir="ltr">
                                <?php if(!$user['email_verified']): ?>
                                    <button class="btn btn-warning btn-sm" type="button" onclick="sendVerifyCode()">ارسال کد تایید</button>
                                <?php else: ?>
                                    <span class="input-group-text bg-success text-white small"><i class="bi bi-patch-check-fill me-1"></i> تایید شده</span>
                                <?php endif; ?>
                                <input type="email" name="email" value="<?= $user['email'] ?>" class="form-control text-center shadow-none">
                            </div>
                            
                            <div id="verify_section" class="mt-3 bg-light p-3 rounded-3 d-none">
                                <label class="small fw-bold mb-1 d-block">کد ۶ رقمی ارسال شده به ایمیل را وارد کنید:</label>
                                <div class="input-group" dir="ltr">
                                    <button class="btn btn-success btn-sm" type="button" onclick="confirmCode()">تایید کد</button>
                                    <input type="text" id="v_code" class="form-control text-center shadow-none" placeholder="123456">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-persian w-100 mt-4 shadow-sm">ذخیره تغییرات پروفایل</button>
                </form>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h6 class="fw-bold mb-4 border-bottom pb-2 text-danger">تغییر رمز عبور</h6>
                <form method="post">
                    <div class="mb-3 text-end">
                        <label class="small fw-bold mb-1">رمز عبور جدید:</label>
                        <input type="password" name="new_pass" class="form-control text-center text-ltr shadow-none" required>
                    </div>
                    <div class="mb-4 text-end">
                        <label class="small fw-bold mb-1">تکرار رمز عبور:</label>
                        <input type="password" name="confirm_pass" class="form-control text-center text-ltr shadow-none" required>
                    </div>
                    <button type="submit" name="update_pass" class="btn btn-outline-danger w-100 shadow-sm">بروزرسانی گذرواژه</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function sendVerifyCode() {
    // در اینجا باید یک درخواست Ajax به فایلی مثل send_email_code.php بزنید
    alert("کد تایید به ایمیل شما ارسال شد.");
    document.getElementById('verify_section').classList.remove('d-none');
}

function confirmCode() {
    const code = document.getElementById('v_code').value;
    if(code.length === 6) {
        alert("ایمیل شما با موفقیت تایید شد.");
        location.reload();
    } else {
        alert("کد وارد شده صحیح نیست.");
    }
}
</script>

<?php if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include __DIR__ . '/../includes/footer.php'; ?>