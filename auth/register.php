<?php
// User Registration | ثبت‌نام کاربر
require_once '../config/db.php';
require_once '../core/SMSHandler.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mobile = $_POST['mobile'];
    $f_name = $_POST['f_name'];
    $l_name = $_POST['l_name'];
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $ref_mobile = $_POST['referrer_mobile'];

    // Check Referrer | بررسی معرف
    $ref_id = null;
    if (!empty($ref_mobile)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE mobile = ?");
        $stmt->execute([$ref_mobile]);
        $ref_user = $stmt->fetch();
        if (!$ref_user) die("Referrer not found | معرف یافت نشد");
        $ref_id = $ref_user['id'];
    }

    // Check Admin Approval Setting
    $settings = include '../config/app_settings.php';
    $status = ($settings['manual_approval']) ? 'pending' : 'active';

    $sql = "INSERT INTO users (first_name, last_name, mobile, password, referrer_id, status) VALUES (?, ?, ?, ?, ?, ?)";
    $db->prepare($sql)->execute([$f_name, $l_name, $mobile, $pass, $ref_id, $status]);

    echo "Registration Successful | ثبت‌نام با موفقیت انجام شد";
}
?>
<form method="post">
    <input type="text" name="f_name" placeholder="First Name" required>
    <input type="text" name="l_name" placeholder="Last Name" required>
    <input type="text" name="mobile" placeholder="Mobile (09...)" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="text" name="referrer_mobile" placeholder="Referrer Mobile (Optional)">
    <button type="submit">Register | ثبت نام</button>
</form>