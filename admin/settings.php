<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /auth/login.php"); exit();
}

$message = "";

// بروزرسانی تنظیمات | Update Settings
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings'])) {
    $s_name = htmlspecialchars($_POST['site_name']);
    $sms_key = htmlspecialchars($_POST['sms_api_key']);
    
    // الگوهای پیامک
    $p_otp = htmlspecialchars($_POST['pattern_otp']);
    $p_admin = htmlspecialchars($_POST['pattern_admin_ticket']);
    $p_agent = htmlspecialchars($_POST['pattern_agent_ticket']);
    $p_expiry = htmlspecialchars($_POST['pattern_expiry_warning']);
    $p_volume = htmlspecialchars($_POST['pattern_volume_warning']);
    
    // مقادیر متغیر (آستانه هشدار)
    $d_before = intval($_POST['days_before_expiry']);
    $m_before = intval($_POST['megabytes_before_finish']);

    // کوئری بروزرسانی
    $stmt = $db->prepare("UPDATE system_settings SET 
        site_name=?, sms_api_key=?, pattern_otp=?, 
        pattern_admin_ticket=?, pattern_agent_ticket=?, 
        pattern_expiry_warning=?, pattern_volume_warning=?,
        days_before_expiry=?, megabytes_before_finish=? 
        WHERE id=1");
        
    if($stmt->execute([$s_name, $sms_key, $p_otp, $p_admin, $p_agent, $p_expiry, $p_volume, $d_before, $m_before])) {
        $message = "<div class='alert alert-success py-2 border-0 shadow-sm'>تمامی تنظیمات، الگوها و مقادیر هشدار با موفقیت ذخیره شدند.</div>";
    } else {
        $message = "<div class='alert alert-danger py-2 border-0 shadow-sm'>خطا در ذخیره‌سازی اطلاعات!</div>";
    }
}

// واکشی اطلاعات فعلی
$settings = $db->query("SELECT * FROM system_settings WHERE id=1")->fetch();

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include __DIR__ . '/../includes/header.php';
?>

<div id="content-area" class="text-end">
    <h4 class="fw-bold text-persian-blue mb-4"><i class="bi bi-gear-fill me-2"></i> تنظیمات عمومی و اطلاع‌رسانی</h4>
    <?= $message ?>

    <form method="post">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <h6 class="fw-bold mb-4 border-bottom pb-2 text-secondary">تنظیمات اصلی پنل</h6>
                    <div class="mb-3">
                        <label class="small fw-bold">نام سایت / برند:</label>
                        <input type="text" name="site_name" value="<?= $settings['site_name'] ?>" class="form-control shadow-none">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-primary">API Key پنل پیامک (Kavenegar):</label>
                        <input type="text" name="sms_api_key" value="<?= $settings['sms_api_key'] ?>" class="form-control text-ltr text-center shadow-none">
                    </div>
                    <div class="alert alert-warning small py-2 mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i> <b>نکته:</b> اگر فیلد الگو را خالی بگذارید، ارسال آن پیامک غیرفعال می‌شود.
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <h6 class="fw-bold mb-4 border-bottom pb-2 text-secondary">کد الگوها و آستانه هشدار</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="small fw-bold">الگوی OTP:</label>
                            <input type="text" name="pattern_otp" value="<?= $settings['pattern_otp'] ?>" class="form-control text-center shadow-none" placeholder="otp-verify">
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold text-danger">تیکت جدید (مدیر):</label>
                            <input type="text" name="pattern_admin_ticket" value="<?= $settings['pattern_admin_ticket'] ?>" class="form-control text-center shadow-none">
                        </div>
                        
                        <hr class="my-2">

                        <div class="col-8">
                            <label class="small fw-bold text-success">الگوی هشدار اتمام زمان:</label>
                            <input type="text" name="pattern_expiry_warning" value="<?= $settings['pattern_expiry_warning'] ?>" class="form-control text-center shadow-none">
                        </div>
                        <div class="col-4">
                            <label class="small fw-bold text-success">روز مانده:</label>
                            <input type="number" name="days_before_expiry" value="<?= $settings['days_before_expiry'] ?? 3 ?>" class="form-control text-center shadow-none">
                        </div>

                        <div class="col-8">
                            <label class="small fw-bold text-info">الگوی هشدار اتمام حجم:</label>
                            <input type="text" name="pattern_volume_warning" value="<?= $settings['pattern_volume_warning'] ?>" class="form-control text-center shadow-none">
                        </div>
                        <div class="col-4">
                            <label class="small fw-bold text-info">مگابایت مانده:</label>
                            <input type="number" name="megabytes_before_finish" value="<?= $settings['megabytes_before_finish'] ?? 100 ?>" class="form-control text-center shadow-none">
                        </div>

                        <div class="col-12 mt-2">
                             <label class="small fw-bold text-warning">الگوی تیکت جدید (کاربر):</label>
                            <input type="text" name="pattern_agent_ticket" value="<?= $settings['pattern_agent_ticket'] ?>" class="form-control text-center shadow-none">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12">
                <button type="submit" name="save_settings" class="btn btn-persian w-100 py-3 fw-bold rounded-4 shadow">ذخیره نهایی تمامی پیکربندی‌ها</button>
            </div>
        </div>
    </form>
</div>

<?php if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include __DIR__ . '/../includes/footer.php'; ?>