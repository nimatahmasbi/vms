<?php
// Plan Management | مدیریت پلن‌ها
include 'includes/header.php';
session_start();
require_once '../config/db.php';

if (isset($_POST['add_plan'])) {
    $vol = $_POST['vol_gb'] * 1024 * 1024 * 1024;
    $stmt = $db->prepare("INSERT INTO plans (title, volume_bytes, up_speed, down_speed, price, can_switch) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$_POST['title'], $vol, $_POST['up'], $_POST['down'], $_POST['price'], isset($_POST['switch'])?1:0]);
}

$plans = $db->query("SELECT * FROM plans")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/bootstrap.rtl.min.css">
    <title>مدیریت پلن‌ها</title>
</head>
<body class="bg-light p-4">
    <form method="post" class="card p-4 shadow-sm mb-4">
        <h5>تعریف پکیج جدید</h5>
        <div class="row">
            <div class="col-md-3"><input type="text" name="title" class="form-control" placeholder="نام پلن"></div>
            <div class="col-md-2"><input type="number" name="vol_gb" class="form-control" placeholder="حجم (GB)"></div>
            <div class="col-md-2"><input type="text" name="up" class="form-control" placeholder="آپلود (مثلا 2M)"></div>
            <div class="col-md-2"><input type="text" name="down" class="form-control" placeholder="دانلود (مثلا 5M)"></div>
            <div class="col-md-2"><input type="number" name="price" class="form-control" placeholder="قیمت (تومان)"></div>
            <div class="col-md-1"><button name="add_plan" class="btn btn-primary">ثبت</button></div>
        </div>
        <div class="mt-2"><input type="checkbox" name="switch"> قابلیت تغییر لوکیشن</div>
    </form>
</body>
<?php include 'includes/footer.php'; ?>
</html>