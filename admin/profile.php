<?php 
include 'includes/header.php'; 

$admin_id = $_SESSION['user_id'];
$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$admin_id]);
$data = $user->fetch();

if (isset($_POST['update_profile'])) {
    $f_name = $_POST['f_name'];
    $l_name = $_POST['l_name'];
    $new_pass = $_POST['password'];

    if (!empty($new_pass)) {
        $hashed_pass = password_hash($new_pass, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET first_name=?, last_name=?, password=? WHERE id=?");
        $stmt->execute([$f_name, $l_name, $hashed_pass, $admin_id]);
    } else {
        $stmt = $db->prepare("UPDATE users SET first_name=?, last_name=? WHERE id=?");
        $stmt->execute([$f_name, $l_name, $admin_id]);
    }
    echo "<div class='alert alert-success'>مشخصات با موفقیت بروز شد.</div>";
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>ویرایش پروفایل مدیر</h3>
        <a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-right"></i> بازگشت</a>
    </div>

    <div class="card shadow-sm border-0 p-4" style="max-width: 600px;">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">نام</label>
                <input type="text" name="f_name" value="<?= $data['first_name'] ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">نام خانوادگی</label>
                <input type="text" name="l_name" value="<?= $data['last_name'] ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">موبایل (نام کاربری - غیرقابل تغییر)</label>
                <input type="text" class="form-control" value="<?= $data['mobile'] ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">رمز عبور جدید (اگر قصد تغییر دارید)</label>
                <input type="password" name="password" class="form-control" placeholder="خالی بگذارید تا تغییر نکند">
            </div>
            <button name="update_profile" class="btn btn-primary w-100">بروزرسانی پروفایل</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>