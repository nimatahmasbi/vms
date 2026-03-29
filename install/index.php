<?php
/**
 * VMS Smart Installer - Final Consolidated Version
 * تمامی مراحل در یک فایل با پشتیبانی از بک‌آپ، آپدیت و نصب مجدد
 */
session_start();
$config_file = '../config/db.php';
$step = isset($_GET['step']) ? $_GET['step'] : '1';
$message = "";

// ۱. تابع استخراج اطلاعات از فایل db.php شما
function getExistingConfig($file) {
    if (!file_exists($file)) return ['host' => 'localhost', 'name' => '', 'user' => '', 'pass' => ''];
    $content = file_get_contents($file);
    preg_match("/\\\$host\s*=\s*'([^']+)';/", $content, $h);
    preg_match("/\\\$db_name\s*=\s*'([^']+)';/", $content, $n);
    preg_match("/\\\$username\s*=\s*'([^']+)';/", $content, $u);
    preg_match("/\\\$password\s*=\s*'([^']+)';/", $content, $p);
    return [
        'host' => $h[1] ?? 'localhost',
        'name' => $n[1] ?? '',
        'user' => $u[1] ?? '',
        'pass' => $p[1] ?? ''
    ];
}

$old_config = getExistingConfig($config_file);

// ۲. واکشی اطلاعات مدیر ارشد (Super Admin)
$admin_info = null;
if (!empty($old_config['name'])) {
    try {
        $tmp_pdo = new PDO("mysql:host={$old_config['host']};dbname={$old_config['name']};charset=utf8mb4", $old_config['user'], $old_config['pass']);
        $st = $tmp_pdo->query("SELECT first_name, last_name, mobile, email FROM users WHERE role = 'admin' LIMIT 1");
        $admin_info = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) { $admin_info = null; }
}

// ۳. پردازش منطق مراحل (POST Handlers)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($step == '1') {
        $_SESSION['install_db'] = $_POST;
        header("Location: index.php?step=2"); exit;
    }
    if ($step == '2') {
        $_SESSION['install_admin'] = $_POST;
        header("Location: index.php?step=3"); exit;
    }
    if ($step == '4') {
        $db_i = $_SESSION['install_db'];
        $adm_i = $_SESSION['install_admin'];
        try {
            $pdo = new PDO("mysql:host={$db_i['db_host']};dbname={$db_i['db_name']};charset=utf8mb4", $db_i['db_user'], $db_i['db_pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            if (isset($_POST['mode_update'])) {
                // بروزرسانی ساختار
                if(file_exists('update_structure.sql')){
                    $sql = file_get_contents('update_structure.sql');
                    $pdo->exec($sql);
                }
                // بروزرسانی ایمیل و فعال‌سازی حساب ادمین
                $pdo->prepare("UPDATE users SET email = ?, mobile = ?, status = 'active' WHERE role = 'admin' LIMIT 1")
                    ->execute([$adm_i['admin_email'], $adm_i['admin_mobile']]);
            } else {
                // نصب مجدد (Fresh Install) - اجرای فایل SQL شامل تمامی ۱۴ جدول
                if(file_exists('database.sql')){
                    $sql = file_get_contents('database.sql');
                    $pdo->exec($sql);
                }
                
                $hash = password_hash($adm_i['admin_pass'], PASSWORD_DEFAULT);
                
                // درج مدیر جدید با وضعیت active و email
                $pdo->prepare("INSERT INTO users (first_name, last_name, mobile, email, password, role, status) VALUES ('مدیر', 'ارشد', ?, ?, ?, 'admin', 'active')")
                    ->execute([$adm_i['admin_mobile'], $adm_i['admin_email'], $hash]);
            }

            // بازنویسی فایل db.php با ساختار متغیری شما
            $new_cfg = "<?php\n\$host = '{$db_i['db_host']}';\n\$db_name = '{$db_i['db_name']}';\n\$username = '{$db_i['db_user']}';\n\$password = '{$db_i['db_pass']}';\n\ntry {\n\t\$db = new PDO(\"mysql:host=\$host;dbname=\$db_name;charset=utf8mb4\", \$username, \$password);\n\t\$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n} catch (PDOException \$e {\n\tdie(\"Connection error\");\n}";
            file_put_contents($config_file, $new_cfg);
            
            $_SESSION['install_complete'] = true;
        } catch (Exception $e) { $message = "<div class='alert alert-danger text-end small'>خطا در عملیات نهایی: " . $e->getMessage() . "</div>"; }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.css">
    <title>نصب هوشمند VMS</title>
    <style>
        body { background: #f0f2f5; font-family: 'Tahoma', sans-serif; direction: rtl; text-align: right; }
        .install-card { max-width: 750px; margin: 50px auto; border-radius: 20px; box-shadow: 0 15px 45px rgba(0,0,0,0.1); background: #fff; border:none; }
        .text-ltr { direction: ltr !important; text-align: left !important; }
        .btn-persian { background: #1C39BB; color: #fff; border-radius: 12px; padding: 12px; border: none; }
        .btn-persian:disabled { background: #a0aec0; cursor: not-allowed; }
        .code-box { background: #2d3436; color: #00cec9; padding: 15px; border-radius: 10px; font-family: monospace; direction: ltr; text-align: left; }
        label { margin-bottom: 5px; font-weight: bold; font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="card install-card p-4 p-md-5">

        <?php if (isset($_SESSION['install_complete'])): ?>
            <div class="text-center">
                <i class="bi bi-patch-check-fill text-success" style="font-size: 5rem;"></i>
                <h3 class="fw-bold mt-3 mb-4 text-success">نصب با موفقیت پایان یافت!</h3>
                <div class="alert alert-warning text-end p-3 rounded-4 small">
                    <h6 class="fw-bold"><i class="bi bi-alarm me-2"></i> تنظیم حیاتی کرون‌جاب (Cron Job)</h6>
                    برای عملکرد صحیح سیستم و بررسی وضعیت سرورها، دستور زیر را در هاست خود تنظیم کنید (هر ۱ دقیقه):
                </div>
                <div class="code-box mb-4">
                    * * * * * php <?= realpath(__DIR__ . '/../cron/check_subscriptions.php') ?> > /dev/null 2>&1
                </div>
                <a href="../auth/login.php" class="btn btn-persian w-100 py-3 shadow">ورود به پنل کاربری</a>
            </div>

        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h4 class="fw-bold text-primary mb-0">نصب‌کننده مرحله‌ای VMS</h4>
                <span class="badge bg-primary rounded-pill px-3 py-2">گام <?= $step ?> از ۴</span>
            </div>

            <?= $message ?>

            <?php if ($step == '1'): ?>
                <h6 class="fw-bold mb-4 text-secondary text-end border-end border-4 pe-2 border-primary">گام اول: بررسی اتصال دیتابیس</h6>
                <form method="post" action="index.php?step=1">
                    <div class="mb-3 text-end"><label>میزبان (Host):</label><input type="text" name="db_host" id="db_host" class="form-control text-ltr text-center" value="<?= $old_config['host'] ?>"></div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 text-end"><label>نام دیتابیس:</label><input type="text" name="db_name" id="db_name" class="form-control text-ltr text-center" value="<?= $old_config['name'] ?>"></div>
                        <div class="col-md-6 text-end"><label>نام کاربری:</label><input type="text" name="db_user" id="db_user" class="form-control text-ltr text-center" value="<?= $old_config['user'] ?>"></div>
                    </div>
                    <div class="mb-4 text-end"><label>رمز عبور:</label><input type="password" name="db_pass" id="db_pass" class="form-control text-ltr text-center" value="<?= $old_config['pass'] ?>"></div>
                    <div id="db_status_msg" class="text-center mb-3"></div>
                    <div class="row g-2">
                        <div class="col-8"><button type="submit" id="nextBtn" class="btn btn-persian w-100 shadow" disabled>مرحله بعد <i class="bi bi-chevron-left ms-1"></i></button></div>
                        <div class="col-4"><button type="button" onclick="testConnection()" class="btn btn-outline-primary w-100 py-3 shadow-sm">تست اتصال</button></div>
                    </div>
                </form>

            <?php elseif ($step == '2'): ?>
                <h6 class="fw-bold mb-4 text-secondary text-end border-end border-4 pe-2 border-primary">گام دوم: اطلاعات مدیر ارشد (Super Admin)</h6>
                <form method="post" action="index.php?step=2">
                    <div class="mb-3 text-end">
                        <label>نام مدیر فعلی شناسایی شده:</label>
                        <input type="text" class="form-control bg-light text-center" value="<?= ($admin_info['first_name'] ?? 'یافت نشد') . ' ' . ($admin_info['last_name'] ?? '') ?>" readonly>
                    </div>
                    <div class="mb-3 text-end"><label>شماره موبایل مدیر (نام کاربری):</label><input type="text" name="admin_mobile" class="form-control text-center text-ltr" value="<?= $admin_info['mobile'] ?? '' ?>" required></div>
                    <div class="mb-3 text-end"><label>ایمیل بازیابی (فیلد جدید):</label><input type="email" name="admin_email" class="form-control text-center text-ltr" value="<?= $admin_info['email'] ?? '' ?>" required placeholder="info@example.com"></div>
                    <div class="mb-4 border-top pt-3 text-end"><label class="text-danger">رمز عبور جدید (فقط برای نصب تازه):</label><input type="password" name="admin_pass" class="form-control text-center text-ltr"></div>
                    <button type="submit" class="btn btn-persian w-100 py-3 shadow-sm">تایید و مرحله بعد <i class="bi bi-chevron-left ms-1"></i></button>
                </form>

            <?php elseif ($step == '3'): ?>
                <h6 class="fw-bold mb-4 text-secondary text-end border-end border-4 pe-2 border-primary">گام سوم: پشتیبان‌گیری از داده‌ها</h6>
                <div class="text-center py-4">
                    <div class="alert alert-info border-0 shadow-sm p-3 text-end mb-4">پیشنهاد می‌شود قبل از اعمال تغییرات نهایی، یک نسخه پشتیبان کامل از دیتابیس فعلی خود دانلود کنید.</div>
                    <div id="backup_status"></div>
                    <div class="d-grid gap-3">
                        <button type="button" onclick="runBackup()" class="btn btn-outline-dark py-3 fw-bold shadow-sm"><i class="bi bi-cloud-arrow-down me-2"></i> تهیه و دانلود بک‌آپ SQL</button>
                        <a href="index.php?step=4" class="btn btn-persian py-2">اطمینان دارم؛ مرحله نهایی <i class="bi bi-chevron-left ms-1"></i></a>
                    </div>
                </div>

            <?php elseif ($step == '4'): ?>
                <h6 class="fw-bold mb-5 text-center">گام چهارم: انتخاب روش نهایی اجرا</h6>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card p-4 border-success h-100 shadow-sm text-center">
                            <i class="bi bi-arrow-repeat display-4 text-success mb-3"></i>
                            <h6 class="fw-bold">بروزرسانی ساختار</h6>
                            <p class="small text-muted mb-4">فقط فیلدها و جداول جدید اضافه می‌شوند و اطلاعات کاربران و سرورها حفظ می‌گردد.</p>
                            <form method="post" action="index.php?step=4"><button name="mode_update" class="btn btn-success w-100 shadow-sm py-2">Update Structure</button></form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-4 border-danger h-100 shadow-sm text-center">
                            <i class="bi bi-trash3 display-4 text-danger mb-3"></i>
                            <h6 class="fw-bold">نصب تازه (Fresh)</h6>
                            <p class="small text-muted mb-4">تمامی ۱۴ جدول حذف و طبق آخرین ساختار از نو ساخته می‌شوند. تمامی اطلاعات قبلی پاک خواهد شد.</p>
                            <form method="post" action="index.php?step=4"><button name="mode_fresh" class="btn btn-danger w-100 shadow-sm py-2">Reinstall Complete</button></form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/jquery-4.0.0.min.js"></script>
<script>
// تست زنده اتصال دیتابیس
function testConnection() {
    $('#db_status_msg').html('<div class="alert alert-info py-1 small text-center">در حال بررسی اطلاعات...</div>');
    $.post('db_test.php', {
        host: $('#db_host').val(), user: $('#db_user').val(),
        pass: $('#db_pass').val(), name: $('#db_name').val()
    }, function(res) {
        if(res.trim() === 'success') {
            $('#db_status_msg').html('<div class="alert alert-success py-1 small text-center"><i class="bi bi-check2-circle"></i> اتصال با موفقیت برقرار شد.</div>');
            $('#nextBtn').prop('disabled', false);
        } else {
            $('#db_status_msg').html('<div class="alert alert-danger py-1 small text-center">'+res+'</div>');
            $('#nextBtn').prop('disabled', true);
        }
    }).fail(function() {
        $('#db_status_msg').html('<div class="alert alert-danger py-1 small text-center">خطا در فراخوانی فایل تست!</div>');
    });
}

// اجرای بک‌آپ
function runBackup() {
    $('#backup_status').html('<div class="alert alert-warning py-1 small text-center">دیتابیس در حال پردازش است...</div>');
    window.location.href = 'backup_handler.php';
    setTimeout(() => { $('#backup_status').empty(); }, 5000);
}
</script>
</body>
</html>