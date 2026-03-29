<?php
// install/db_test.php
header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['host'] ?? '';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $name = $_POST['name'] ?? '';

    try {
        // تلاش برای ایجاد اتصال آزمایشی
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo 'success';
    } catch (PDOException $e) {
        // ارسال پیام خطا به صورت فارسی
        echo "خطا در اتصال: " . $e->getMessage();
    }
} else {
    echo "دسترسی غیرمجاز!";
}
exit;