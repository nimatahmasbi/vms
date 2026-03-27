<?php
// Display Errors for Debugging | نمایش خطاها برای جلوگیری از صفحه سفید
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config_file = '../config/db.php';
$sql_file = 'vms_database.sql';

// ۱. تلاش برای خواندن اطلاعات قبلی دیتابیس
$host = 'localhost';
$db_name = '';
$username = '';
$password = '';

if (file_exists($config_file)) {
    @include $config_file; // استفاده از @ برای جلوگیری از توقف در صورت وجود خطای سینتکس در فایل قدیمی
}

// ۲. پردازش فرم مرحله اول (اتصال دیتابیس و اجرای SQL)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setup_db'])) {
    $h = $_POST['host'];
    $n = $_POST['dbname'];
    $u = $_POST['dbuser'];
    $p = $_POST['dbpass'];

    try {
        // ایجاد اتصال اولیه برای ساخت دیتابیس
        $conn = new PDO("mysql:host=$h", $u, $p);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("CREATE DATABASE IF NOT EXISTS `$n` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // اتصال به دیتابیس اصلی و اجرای فایل SQL
        $db_conn = new PDO("mysql:host=$h;dbname=$n;charset=utf8mb4", $u, $p);
        if (file_exists($sql_file)) {
            $query = file_get_contents($sql_file);
            $db_conn->exec($query);
        } else {
            throw new Exception("File vms_database.sql not found in install folder!");
        }

        // ایجاد محتوای فایل db.php بدون خطای سینتکس
        $php_content = "<?php
\$host = '$h';
\$db_name = '$n';
\$username = '$u';
\$password = '$p';

try {
    \$db = new PDO(\"mysql:host=\$host;dbname=\$db_name;charset=utf8mb4\", \$username, \$password);
    \$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException \$e) {
    die(\"Connection error: \" . \$e->getMessage());
}";

        if (!is_dir('../config')) {
            mkdir('../config', 0755, true);
        }
        
        // ذخیره فایل
        if (file_put_contents($config_file, $php_content) === false) {
            throw new Exception("Could not write to config/db.php. Check permissions.");
        }

        header("Location: index.php?step=2");
        exit;
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// ۳. پردازش فرم مرحله دوم (ساخت سوپر ادمین)
if (isset($_GET['step']) && $_GET['step'] == '2') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setup_admin'])) {
        if (file_exists($config_file)) {
            require_once $config_file;
            
            $m = $_POST['adm_mobile'];
            $pass = password_hash($_POST['adm_pass'], PASSWORD_BCRYPT);
            
            $stmt = $db->prepare("INSERT INTO users (first_name, last_name, mobile, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['Super', 'Admin', $m, $pass, 'admin', 'active']);
            
            die("<div dir='rtl' style='text-align:center; padding:50px; font-family:tahoma;'><h2>نصب با موفقیت انجام شد!</h2><p>حالا می‌توانید وارد پنل شوید. <b>پوشه install را حذف کنید.</b></p><a href='../user/auth/login.php' style='display:inline-block; padding:10px 20px; background:#28a745; color:#fff; text-decoration:none; border-radius:5px;'>ورود به پنل مدیریت</a></div>");
        } else {
            $error = "Config file missing. Please go back to step 1.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب سیستم مدیریت VMS</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f8f9fa; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .install-box { background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        h3 { text-align: center; color: #333; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        button:hover { background: #0056b3; }
        .alert { background: #fff3f3; color: #d9534f; padding: 10px; border-radius: 5px; border: 1px solid #f5c6cb; font-size: 13px; margin-bottom: 15px; }
        .info { font-size: 11px; color: #666; text-align: center; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="install-box">
        <?php if (!isset($_GET['step'])): ?>
            <h3>مرحله ۱: تنظیمات پایگاه داده</h3>
            <?php if (isset($error)) echo "<div class='alert'>$error</div>"; ?>
            <form method="post">
                <label>میزبان دیتابیس (Host):</label>
                <input type="text" name="host" value="<?= htmlspecialchars($host) ?>" required>
                <label>نام دیتابیس (Database):</label>
                <input type="text" name="dbname" value="<?= htmlspecialchars($db_name) ?>" required>
                <label>نام کاربری (Username):</label>
                <input type="text" name="dbuser" value="<?= htmlspecialchars($username) ?>" required>
                <label>رمز عبور (Password):</label>
                <input type="password" name="dbpass" value="<?= htmlspecialchars($password) ?>">
                <button type="submit" name="setup_db">اتصال و اجرای فایل SQL</button>
            </form>
        <?php else: ?>
            <h3>مرحله ۲: تعریف مدیر سیستم</h3>
            <?php if (isset($error)) echo "<div class='alert'>$error</div>"; ?>
            <form method="post">
                <label>نام کاربری مدیر (شماره موبایل):</label>
                <input type="text" name="adm_mobile" placeholder="مثال: 09123456789" required>
                <label>رمز عبور پنل مدیریت:</label>
                <input type="password" name="adm_pass" placeholder="حداقل ۸ کاراکتر" required>
                <button type="submit" name="setup_admin">اتمام نصب و ذخیره</button>
            </form>
        <?php endif; ?>
        <p class="info">VMS Project Framework | Powered by Mr.NT</p>
    </div>
</body>
</html>