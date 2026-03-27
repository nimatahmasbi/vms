<?php include 'includes/header.php'; ?>

<?php
$config_file = '../config/app_settings.php';
$settings = file_exists($config_file) ? include $config_file : [
    'merchant' => '',
    'sms_key' => '',
    'sms_pattern_admin_notify' => '',
    'sms_pattern_reseller_notify' => '',
    'referral_percent' => 5,
    'admin_approve' => false,
    'sms_auth' => false
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings'])) {
    $data = [
        'merchant' => $_POST['merchant'],
        'sms_key' => $_POST['sms_key'],
        'sms_pattern_admin_notify' => $_POST['sms_admin'],
        'sms_pattern_reseller_notify' => $_POST['sms_res'],
        'referral_percent' => (int)$_POST['ref_p'],
        'admin_approve' => isset($_POST['approve']),
        'sms_auth' => isset($_POST['sms_on'])
    ];
    file_put_contents($config_file, "<?php\nreturn " . var_export($data, true) . ";");
    echo "<div class='alert alert-success shadow-sm border-0 mb-4'>✅ تنظیمات با موفقیت در فایل پیکربندی ذخیره شد.</div>";
    $settings = $data;
}
?>

<div class="row g-4">
    <div class="col-12 mb-2">
        <h3 class="text-persian-blue fw-bold"><i class="bi bi-gear-wide-connected me-2"></i> تنظیمات پیکربندی سامانه</h3>
    </div>

    <form method="post" class="row g-4">
        <div class="col-md-6">
            <div class="card card-persian shadow-sm p-4 h-100" style="border-top: 5px solid #1C39BB;">
                <h5 class="text-persian-blue mb-4 fw-bold border-bottom pb-2">درگاه پرداخت و سود</h5>
                <label class="form-label small">مرچنت زرین‌پال:</label>
                <input type="text" name="merchant" value="<?= $settings['merchant'] ?>" class="form-control mb-3" placeholder="Merchant ID">
                
                <label class="form-label small">درصد سود معرف (بازاریابی):</label>
                <div class="input-group">
                    <input type="number" name="ref_p" value="<?= $settings['referral_percent'] ?>" class="form-control">
                    <span class="input-group-text">%</span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm p-4 h-100" style="border-top: 5px solid #00A693;">
                <h5 class="text-persian-turquoise mb-4 fw-bold border-bottom pb-2">پیامک (IPPanel)</h5>
                <label class="form-label small">کلید API پنل پیامک:</label>
                <input type="text" name="sms_key" value="<?= $settings['sms_key'] ?>" class="form-control mb-3" placeholder="API Key">
                
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small">کد الگوی مدیر:</label>
                        <input type="text" name="sms_admin" value="<?= $settings['sms_pattern_admin_notify'] ?>" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">کد الگوی نماینده:</label>
                        <input type="text" name="sms_res" value="<?= $settings['sms_pattern_reseller_notify'] ?>" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-3 shadow-sm border-0 bg-white rounded-4">
                <div class="d-flex justify-content-around align-items-center flex-wrap gap-4">
                    <div class="form-check form-switch custom-switch">
                        <input class="form-check-input" type="checkbox" name="sms_on" <?= $settings['sms_auth'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold ms-2">احراز هویت پیامکی (OTP)</label>
                    </div>
                    <div class="form-check form-switch custom-switch">
                        <input class="form-check-input" type="checkbox" name="approve" <?= $settings['admin_approve'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold ms-2">تأیید دستی کاربران توسط مدیر</label>
                    </div>
                    <button type="submit" name="save_settings" class="btn btn-persian px-5 py-2 shadow-sm">
                        <i class="bi bi-save me-2"></i> ذخیره تغییرات
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
