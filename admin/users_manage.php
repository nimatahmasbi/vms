<?php
// User & Reseller Management | مدیریت کاربران و نمایندگان
include 'includes/header.php';
session_start();
require_once '../config/db.php';
if ($_SESSION['role'] !== 'admin') die("Access Denied");

// بروزرسانی اطلاعات کاربر
if (isset($_POST['save_user'])) {
    $stmt = $db->prepare("UPDATE users SET first_name=?, last_name=?, wallet=?, role=?, status=?, reseller_percent=? WHERE id=?");
    $stmt->execute([$_POST['f_name'], $_POST['l_name'], $_POST['wallet'], $_POST['role'], $_POST['status'], $_POST['res_percent'], $_POST['user_id']]);
}

$users = $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/bootstrap.rtl.min.css">
    <title>مدیریت کاربران</title>
</head>
<body class="bg-light p-4">
    <h3 class="mb-4">لیست کاربران و نمایندگان</h3>
    <div class="table-responsive bg-white shadow-sm p-3 rounded">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>نام</th><th>موبایل</th><th>کیف پول (تومان)</th><th>نقش</th><th>سود نماینده (%)</th><th>وضعیت</th><th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <form method="post">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <tr>
                        <td><input type="text" name="f_name" value="<?= $u['first_name'] ?>" class="form-control form-control-sm"></td>
                        <td><?= $u['mobile'] ?></td>
                        <td><input type="number" name="wallet" value="<?= $u['wallet'] ?>" class="form-control form-control-sm"></td>
                        <td>
                            <select name="role" class="form-select form-select-sm">
                                <option value="user" <?= $u['role']=='user'?'selected':'' ?>>کاربر</option>
                                <option value="reseller" <?= $u['role']=='reseller'?'selected':'' ?>>نماینده</option>
                            </select>
                        </td>
                        <td><input type="number" name="res_percent" value="<?= $u['reseller_percent'] ?>" class="form-control form-control-sm"></td>
                        <td>
                            <select name="status" class="form-select form-select-sm">
                                <option value="active" <?= $u['status']=='active'?'selected':'' ?>>فعال</option>
                                <option value="pending" <?= $u['status']=='pending'?'selected':'' ?>>منتظر تایید</option>
                                <option value="blocked" <?= $u['status']=='blocked'?'selected':'' ?>>مسدود</option>
                            </select>
                        </td>
                        <td><button name="save_user" class="btn btn-sm btn-primary">ذخیره</button></td>
                    </tr>
                </form>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
<?php include 'includes/footer.php'; ?>
</html>