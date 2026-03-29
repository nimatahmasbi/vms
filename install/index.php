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
            
            if (isset($_POST['mode_update'])) {
                // بروزرسانی ساختار
                if(file_exists('update_structure.sql')){
                    $sql = file_get_contents('update_structure.sql');
                    $pdo->exec($sql);
                }
                $pdo->prepare("UPDATE users SET email = ?, mobile = ? WHERE role = 'admin'")->execute([$adm_i['admin_email'], $adm_i['admin_mobile']]);
            } else {
                // نصب مجدد
                $sql = file_get_contents('database.sql');
                $pdo->exec($sql);
                $hash = password_hash($adm_i['admin_pass'], PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (first_name, last_name, mobile, email, password, role) VALUES ('مدیر', 'ارشد', ?, ?, ?, 'admin')")
                    ->execute([$adm_i['admin_mobile'], $adm_i['admin_email'], $hash]);
            }

            // بازنویسی فایل db.php
            $new_cfg = "<?php\n\$host = '{$db_i['db_host']}';\n\$db_name = '{$db_i['db_name']}';\n\$username = '{$db_i['db_user']}';\n\$password = '{$db_i['db_pass']}';\n\ntry {\n\t\$db = new PDO(\"mysql:host=\$host;dbname=\$db_name;charset=utf8mb4\", \$username, \$password);\n\t\$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n} catch (PDOException \$e) {\n\tdie(\"Connection error: \" . \$e->getMessage());\n}";
            file_put_contents($config_file, $new_cfg);
            
            $_SESSION['install_complete'] = true;
        } catch (Exception $e) { $message = "<div class='alert alert-danger'>خطا: " . $e->getMessage() . "</div>"; }
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
    </style>
</head>
<body>

<div class="container">
    <div class="card install-card p-4 p-md-5">

        <?php if (isset($_SESSION['install_complete'])): ?>
            <div class="text-center">
                <i class="bi bi-patch-check-fill text-success" style="font-size: 5rem;"></i>
                <h3 class="fw-bold mt-3 mb-4 text-success">نصب با موفقیت پایان یافت!</h3>
                <div class="alert alert-warning text-end p-3 rounded-4">
                    <h6 class="fw-bold"><i class="bi bi-alarm me-2"></i> تنظیم حیاتی کرون‌جاب (Cron Job)</h6>
                    برای عملکرد صحیح سیستم (ارسال پیامک‌های انقضا و حجم)، دستور زیر را در هاست خود برای اجرا در هر ۱ دقیقه تنظیم کنید:
                </div>
                <div class="code-box mb-4">
                    * * * * * php <?= realpath(__DIR__ . '/../cron/check_subscriptions.php') ?> > /dev/null 2>&1
                </div>
                <a href="../auth/login.php" class="btn btn-persian w-100 py-3 shadow">ورود به پنل کاربری</a>
            </div>

        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h4 class="fw-bold text-primary mb-0">نصب‌کننده گام‌به‌گام VMS</h4>
                <span class="badge bg-primary rounded-pill px-3 py-2">گام <?= $step ?> از ۴</span>
            </div>

            <?= $message ?>

            <?php if ($step == '1'): ?>
                <h6 class="fw-bold mb-4 text-secondary">گام اول: بررسی اتصال دیتابیس</h6>
                <form method="post" action="index.php?step=1">
                    <div class="mb-3"><label class="small fw-bold">میزبان (Host):</label><input type="text" name="db_host" id="db_host" class="form-control text-ltr" value="<?= $old_config['host'] ?>"></div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="small fw-bold">نام دیتابیس:</label><input type="text" name="db_name" id="db_name" class="form-control text-ltr" value="<?= $old_config['name'] ?>"></div>
                        <div class="col-md-6"><label class="small fw-bold">نام کاربری:</label><input type="text" name="db_user" id="db_user" class="form-control text-ltr" value="<?= $old_config['user'] ?>"></div>
                    </div>
                    <div class="mb-4"><label class="small fw-bold">رمز عبور:</label><input type="password" name="db_pass" id="db_pass" class="form-control text-ltr" value="<?= $old_config['pass'] ?>"></div>
                    <div id="db_status_msg" class="text-center mb-3"></div>
                    <div class="row g-2">
                        <div class="col-8"><button type="submit" id="nextBtn" class="btn btn-persian w-100 shadow" disabled>مرحله بعد</button></div>
                        <div class="col-4"><button type="button" onclick="testConnection()" class="btn btn-outline-primary w-100 py-3 shadow-sm">تست اتصال</button></div>
                    </div>
                </form>

            <?php elseif ($step == '2'): ?>
                <h6 class="fw-bold mb-4 text-secondary">گام دوم: اطلاعات مدیر ارشد (Super Admin)</h6>
                <form method="post" action="index.php?step=2">
                    <div class="mb-3">
                        <label class="small fw-bold">نام مدیر فعلی:</label>
                        <input type="text" class="form-control bg-light" value="<?= ($admin_info['first_name'] ?? 'نامشخص') . ' ' . ($admin_info['last_name'] ?? '') ?>" readonly>
                    </div>
                    <div class="mb-3"><label class="small fw-bold">شماره موبایل مدیر:</label><input type="text" name="admin_mobile" class="form-control text-center text-ltr" value="<?= $admin_info['mobile'] ?? '' ?>" required></div>
                    <div class="mb-3"><label class="small fw-bold">ایمیل بازیابی:</label><input type="email" name="admin_email" class="form-control text-center text-ltr" value="<?= $admin_info['email'] ?? '' ?>" required></div>
                    <div class="mb-4 border-top pt-3"><label class="small fw-bold text-danger">رمز عبور جدید (فقط برای نصب تازه):</label><input type="password" name="admin_pass" class="form-control text-center text-ltr"></div>
                    <button type="submit" class="btn btn-persian w-100 py-3">ذخیره و مرحله بعد</button>
                </form>

            <?php elseif ($step == '3'): ?>
                <h6 class="fw-bold mb-4 text-secondary">گام سوم: پشتیبان‌گیری از داده‌ها</h6>
                <div class="text-center py-4">
                    <div class="alert alert-info border-0 shadow-sm p-3 text-end mb-4">قبل از اعمال تغییرات نهایی، پیشنهاد می‌شود نسخه پشتیبان دیتابیس را دانلود کنید.</div>
                    <div id="backup_status"></div>
                    <div class="d-grid gap-3">
                        <button type="button" onclick="runBackup()" class="btn btn-outline-dark py-3 fw-bold shadow-sm"><i class="bi bi-download me-2"></i> ایجاد و دانلود فایل بک‌آپ SQL</button>
                        <a href="index.php?step=4" class="btn btn-persian py-2">تایید و گام نهایی</a>
                    </div>
                </div>

            <?php elseif ($step == '4'): ?>
                <h6 class="fw-bold mb-5 text-center">گام چهارم: انتخاب روش اجرا</h6>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card p-4 border-success h-100 shadow-sm text-center">
                            <i class="bi bi-arrow-repeat display-4 text-success mb-3"></i>
                            <h6 class="fw-bold">بروزرسانی (Update)</h6>
                            <p class="small text-muted mb-4">فقط ساختار بروز می‌شود و اطلاعات کاربران حفظ می‌گردد.</p>
                            <form method="post" action="index.php?step=4"><button name="mode_update" class="btn btn-success w-100 shadow-sm">Migrate</button></form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-4 border-danger h-100 shadow-sm text-center">
                            <i class="bi bi-trash3 display-4 text-danger mb-3"></i>
                            <h6 class="fw-bold">نصب خام (Fresh)</h6>
                            <p class="small text-muted mb-4">دیتابیس حذف و از نو ساخته می‌شود (اطلاعات پاک می‌شود).</p>
                            <form method="post" action="index.php?step=4"><button name="mode_fresh" class="btn btn-danger w-100 shadow-sm">Fresh Install</button></form>
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
    $('#db_status_msg').html('<div class="alert alert-info py-2 small">در حال تست...</div>');
    $.post('db_test.php', {
        host: $('#db_host').val(), user: $('#db_user').val(),
        pass: $('#db_pass').val(), name: $('#db_name').val()
    }, function(res) {
        if(res.trim() === 'success') {
            $('#db_status_msg').html('<div class="alert alert-success py-2 small">اتصال موفقیت‌آمیز بود!</div>');
            $('#nextBtn').prop('disabled', false);
        } else {
            $('#db_status_msg').html('<div class="alert alert-danger py-2 small">'+res+'</div>');
            $('#nextBtn').prop('disabled', true);
        }
    }).fail(function() {
        $('#db_status_msg').html('<div class="alert alert-danger py-2 small">خطا در ارسال درخواست به db_test.php</div>');
    });
}

// اجرای بک‌آپ
function runBackup() {
    $('#backup_status').html('<div class="alert alert-warning py-2 small text-center">در حال تهیه فایل...</div>');
    window.location.href = 'backup_handler.php';
    setTimeout(() => { $('#backup_status').empty(); }, 5000);
}
</script>
</body>
</html>