<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /auth/login.php"); exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings'])) {
    $data = [
        'site_name' => $_POST['site_name'],
        'sms_api_key' => $_POST['sms_api_key'],
        'zarinpal_merchant' => $_POST['zarinpal_merchant'],
        'zarinpal_status' => $_POST['zarinpal_status'],
        'require_admin_approval' => $_POST['require_admin_approval'],
        'user_registration' => $_POST['user_registration'],
        'pattern_otp' => $_POST['pattern_otp'],
        'pattern_admin_ticket' => $_POST['pattern_admin_ticket'],
        'pattern_agent_ticket' => $_POST['pattern_agent_ticket'],
        'pattern_expiry_warning' => $_POST['pattern_expiry_warning'],
        'days_before_expiry' => intval($_POST['days_before_expiry']),
        'pattern_volume_warning' => $_POST['pattern_volume_warning'],
        'megabytes_before_finish' => intval($_POST['megabytes_before_finish'])
    ];

    $sql = "UPDATE system_settings SET site_name=?, sms_api_key=?, zarinpal_merchant=?, zarinpal_status=?, 
            require_admin_approval=?, user_registration=?, pattern_otp=?, pattern_admin_ticket=?, 
            pattern_agent_ticket=?, pattern_expiry_warning=?, days_before_expiry=?, 
            pattern_volume_warning=?, megabytes_before_finish=? WHERE id=1";
    
    $stmt = $db->prepare($sql);
    if($stmt->execute(array_values($data))) {
        $message = "<div class='alert alert-success py-2 border-0 shadow-sm text-end' dir='rtl'>تنظیمات با موفقیت بروزرسانی شد.</div>";
    }
}

$settings = $db->query("SELECT * FROM system_settings WHERE id=1")->fetch();
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include __DIR__ . '/../includes/header.php';
?>

<style>
    .text-end { text-align: right !important; }
    .form-label { display: block; width: 100%; text-align: right; font-weight: bold; margin-bottom: 8px; font-size: 0.85rem; color: #555; }
    .form-control, .form-select { text-align: right; direction: rtl; border-radius: 10px; }
    .text-ltr-center { direction: ltr !important; text-align: center !important; }
</style>

<div id="content-area" class="container-fluid" dir="rtl">
    <h4 class="fw-bold text-persian-blue mb-4 text-end"><i class="bi bi-gear-fill me-2"></i> تنظیمات پیکربندی سیستم</h4>
    <?= $message ?>

    <form method="post">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-end">
                    <h6 class="fw-bold mb-4 border-bottom pb-2 text-primary"><i class="bi bi-credit-card-2-front me-2"></i> تنظیمات درگاه پرداخت</h6>
                    <div class="mb-3">
                        <label class="form-label">Merchant ID زرین‌پال:</label>
                        <input type="text" name="zarinpal_merchant" value="<?= $settings['zarinpal_merchant'] ?>" class="form-control text-ltr-center shadow-none">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">وضعیت درگاه:</label>
                        <select name="zarinpal_status" class="form-select shadow-none">
                            <option value="1" <?= $settings['zarinpal_status']==1?'selected':'' ?>>فعال</option>
                            <option value="0" <?= $settings['zarinpal_status']==0?'selected':'' ?>>غیرفعال</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-end">
                    <h6 class="fw-bold mb-4 border-bottom pb-2 text-secondary"><i class="bi bi-shield-lock me-2"></i> مدیریت دسترسی</h6>
                    <div class="mb-3">
                        <label class="form-label">تایید مدیر برای ثبت‌نام:</label>
                        <select name="require_admin_approval" class="form-select shadow-none">
                            <option value="1" <?= $settings['require_admin_approval']==1?'selected':'' ?>>اجباری</option>
                            <option value="0" <?= $settings['require_admin_approval']==0?'selected':'' ?>>خودکار</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">وضعیت ثبت‌نام عمومی:</label>
                        <select name="user_registration" class="form-select shadow-none">
                            <option value="1" <?= $settings['user_registration']==1?'selected':'' ?>>باز</option>
                            <option value="0" <?= $settings['user_registration']==0?'selected':'' ?>>بسته</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-end">
                    <h6 class="fw-bold mb-4 border-bottom pb-2 text-dark"><i class="bi bi-chat-left-dots me-2"></i> سامانه پیامک و اعلان‌های هوشمند (IPPanel)</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">نام سامانه:</label>
                            <input type="text" name="site_name" value="<?= $settings['site_name'] ?>" class="form-control shadow-none">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">API Key پنل پیامک (IPPanel):</label>
                            <input type="text" name="sms_api_key" value="<?= $settings['sms_api_key'] ?>" class="form-control text-ltr-center shadow-none">
                        </div>
                        
                        <div class="col-md-4"><label class="form-label">الگوی OTP:</label><input type="text" name="pattern_otp" value="<?= $settings['pattern_otp'] ?>" class="form-control text-ltr-center"></div>
                        <div class="col-md-4"><label class="form-label">تیکت (مدیر):</label><input type="text" name="pattern_admin_ticket" value="<?= $settings['pattern_admin_ticket'] ?>" class="form-control text-ltr-center"></div>
                        <div class="col-md-4"><label class="form-label">تیکت (کاربر):</label><input type="text" name="pattern_agent_ticket" value="<?= $settings['pattern_agent_ticket'] ?>" class="form-control text-ltr-center"></div>

                        <div class="col-md-3">
                            <label class="form-label text-success">الگوی هشدار زمان:</label>
                            <input type="text" name="pattern_expiry_warning" value="<?= $settings['pattern_expiry_warning'] ?>" class="form-control text-ltr-center">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-success">روز مانده:</label>
                            <input type="number" name="days_before_expiry" value="<?= $settings['days_before_expiry'] ?>" class="form-control text-center">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-info">الگوی هشدار حجم:</label>
                            <input type="text" name="pattern_volume_warning" value="<?= $settings['pattern_volume_warning'] ?>" class="form-control text-ltr-center">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-info">مگابایت مانده:</label>
                            <input type="number" name="megabytes_before_finish" value="<?= $settings['megabytes_before_finish'] ?>" class="form-control text-center">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" name="save_settings" class="btn btn-persian w-100 py-3 fw-bold rounded-4 shadow">ذخیره نهایی تمامی تنظیمات</button>
            </div>
        </div>
    </form>
</div>

<?php if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include __DIR__ . '/../includes/footer.php'; ?>