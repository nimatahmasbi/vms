<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // بروزرسانی مشخصات
    if (isset($_POST['update_profile'])) {
        $f_name = htmlspecialchars($_POST['f_name']);
        $l_name = htmlspecialchars($_POST['l_name']);
        $db->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?")->execute([$f_name, $l_name, $user_id]);
        $_SESSION['name'] = $f_name . " " . $l_name;
        $message = "<div class='alert alert-success py-2'>مشخصات بروز شد.</div>";
    }
    // بروزرسانی رمز عبور
    if (isset($_POST['update_pass'])) {
        $new_pass = $_POST['new_pass'];
        $confirm_pass = $_POST['confirm_pass'];
        if($new_pass === $confirm_pass && strlen($new_pass) >= 6) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user_id]);
            $message = "<div class='alert alert-success py-2'>رمز عبور با موفقیت تغییر کرد.</div>";
        } else {
            $message = "<div class='alert alert-danger py-2'>رمزها مطابقت ندارند یا خیلی کوتاه‌اند.</div>";
        }
    }
}

$user = $db->query("SELECT * FROM users WHERE id = $user_id")->fetch();

// اگر درخواست Ajax نبود هدر را لود کن (برای لود مستقیم صفحه)
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include 'header.php';
?>

<div id="content-area">
    <h4 class="fw-bold text-persian-blue mb-4">تنظیمات حساب کاربری</h4>
    <?= $message ?>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h6 class="fw-bold mb-3">ویرایش اطلاعات شناسایی</h6>
                <form method="post" action="/includes/profile.php">
                    <div class="mb-3">
                        <label class="small fw-bold">نام:</label>
                        <input type="text" name="f_name" value="<?= $user['first_name'] ?>" class="form-control shadow-none">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">نام خانوادگی:</label>
                        <input type="text" name="l_name" value="<?= $user['last_name'] ?>" class="form-control shadow-none">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-persian w-100">ذخیره تغییرات</button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h6 class="fw-bold mb-3 text-danger">تغییر رمز عبور</h6>
                <form method="post" action="/includes/profile.php">
                    <div class="mb-3">
                        <label class="small fw-bold">رمز عبور جدید:</label>
                        <input type="password" name="new_pass" class="form-control shadow-none" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">تکرار رمز عبور:</label>
                        <input type="password" name="confirm_pass" class="form-control shadow-none" required>
                    </div>
                    <button type="submit" name="update_pass" class="btn btn-outline-danger w-100">بروزرسانی رمز</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include 'footer.php'; ?>