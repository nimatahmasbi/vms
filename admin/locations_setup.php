<?php
// Router & Subnet Configuration | تنظیمات سرور و زیرشبکه
include 'includes/header.php';
session_start();
require_once '../config/db.php';
if ($_SESSION['role'] !== 'admin') die("Access Denied");

if (isset($_POST['add_loc'])) {
    $stmt = $db->prepare("INSERT INTO locations (name, server_ip, local_gateway, subnet_range) VALUES (?,?,?,?)");
    $stmt->execute([$_POST['name'], $_POST['ip'], $_POST['gw'], $_POST['subnet']]);
}

$locs = $db->query("SELECT * FROM locations")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/bootstrap.rtl.min.css">
    <title>تنظیمات لوکیشن</title>
</head>
<body class="bg-light p-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h5>افزودن لوکیشن جدید</h5>
                <form method="post">
                    <input type="text" name="name" class="form-control mb-2" placeholder="نام (مثلا آلمان)">
                    <input type="text" name="ip" class="form-control mb-2" placeholder="IP میکروتیک">
                    <input type="text" name="gw" class="form-control mb-2" placeholder="لوکال Gateway">
                    <input type="text" name="subnet" class="form-control mb-2" placeholder="Subnet (10.10.0.0/24)">
                    <button name="add_loc" class="btn btn-success w-100">ثبت لوکیشن</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <table class="table bg-white shadow-sm">
                <thead><tr><th>نام</th><th>IP میکروتیک</th><th>Subnet</th></tr></thead>
                <tbody>
                    <?php foreach($locs as $l): ?>
                    <tr><td><?= $l['name'] ?></td><td><?= $l['server_ip'] ?></td><td><?= $l['subnet_range'] ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
<?php include 'includes/footer.php'; ?>
</html>