<?php
// User Location Switcher UI | رابط کاربری تغییر لوکیشن کاربر
session_start();
require_once '../config/db.php';
require_once '../core/LocationSwitcher.php';

$userId = $_SESSION['user_id'];
$locations = $db->query("SELECT * FROM locations")->fetchAll();
$current_service = $db->prepare("SELECT * FROM services WHERE user_id = ?");
$current_service->execute([$userId]);
$srv = $current_service->fetch();

if (isset($_POST['do_switch'])) {
    $newLocId = $_POST['new_location'];
    $switcher = new LocationSwitcher();
    if ($switcher->switch($db, $userId, $newLocId)) {
        $success = "Location Changed Successfully | لوکیشن با موفقیت تغییر کرد";
    } else {
        $error = "Switch Failed! No IP available? | خطا! آی‌پی خالی در این لوکیشن موجود نیست";
    }
}
?>
<div class="container py-4 text-center" dir="rtl">
    <h4>تغییر لوکیشن سرور</h4>
    <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <form method="post" class="mt-4">
        <select name="new_location" class="form-select mb-3">
            <?php foreach($locations as $loc): ?>
                <option value="<?= $loc['id'] ?>" <?= ($srv['location_id']==$loc['id'])?'selected':'' ?>>
                    <?= $loc['name'] ?> (<?= $loc['subnet_range'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button name="do_switch" class="btn btn-warning w-100">درخواست جابجایی و صدور آی‌پی جدید</button>
    </form>
</div>