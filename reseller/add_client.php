<?php
// Reseller Customer Registration | ثبت مشتری توسط نماینده
session_start();
require_once '../config/db.php';

if ($_SESSION['role'] != 'reseller') die("Access Denied");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mobile = $_POST['mobile'];
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $reseller_id = $_SESSION['user_id'];

    $sql = "INSERT INTO users (first_name, last_name, mobile, password, referrer_id, status) VALUES (?, ?, ?, ?, ?, 'active')";
    $db->prepare($sql)->execute([$_POST['f_name'], $_POST['l_name'], $mobile, $pass, $reseller_id]);
    
    echo "Client Registered Successfully | مشتری با موفقیت ثبت شد";
}
?>
<div class="container mt-4" dir="rtl">
    <h4>ثبت مشتری جدید (زیرمجموعه شما)</h4>
    <form method="post" class="card p-4 shadow-sm">
        <div class="row">
            <div class="col-md-6 mb-2"><input type="text" name="f_name" class="form-control" placeholder="نام"></div>
            <div class="col-md-6 mb-2"><input type="text" name="l_name" class="form-control" placeholder="نام خانوادگی"></div>
            <div class="col-md-6 mb-2"><input type="text" name="mobile" class="form-control" placeholder="موبایل مشتری"></div>
            <div class="col-md-6 mb-2"><input type="password" name="password" class="form-control" placeholder="رمز عبور مشتری"></div>
        </div>
        <button class="btn btn-primary mt-3">ثبت و ایجاد دسترسی</button>
    </form>
</div>