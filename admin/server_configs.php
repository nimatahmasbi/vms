<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /auth/login.php"); exit();
}

$message = "";

// ذخیره سرور اصلی
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_main_server'])) {
    $name = htmlspecialchars($_POST['server_name']);
    $ip   = htmlspecialchars($_POST['ip_address']);
    $port = intval($_POST['api_port']);
    $user = htmlspecialchars($_POST['api_user']);
    $pass = htmlspecialchars($_POST['api_pass']);

    $stmt = $db->prepare("INSERT INTO server_configs (id, server_name, ip_address, api_port, api_user, api_pass) 
                          VALUES (1, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
                          server_name=VALUES(server_name), ip_address=VALUES(ip_address), 
                          api_port=VALUES(api_port), api_user=VALUES(api_user), api_pass=VALUES(api_pass)");
    $stmt->execute([$name, $ip, $port, $user, $pass]);
    $message = "<div class='alert alert-success py-2 border-0 shadow-sm'>تنظیمات سرور اصلی با موفقیت ذخیره شد.</div>";
}

// افزودن روتر NAS
if (isset($_POST['add_nas_router'])) {
    $r_name = htmlspecialchars($_POST['nas_name']);
    $r_ip = htmlspecialchars($_POST['nas_ip']);
    $r_secret = htmlspecialchars($_POST['nas_secret']);
    $stmt = $db->prepare("INSERT INTO nas_routers (nas_name, nas_ip, nas_secret) VALUES (?, ?, ?)");
    $stmt->execute([$r_name, $r_ip, $r_secret]);
    $message = "<div class='alert alert-success py-2 border-0 shadow-sm'>روتر NAS جدید به لیست اضافه شد.</div>";
}

// حذف روتر NAS
if (isset($_GET['delete_nas'])) {
    $id = intval($_GET['delete_nas']);
    $db->prepare("DELETE FROM nas_routers WHERE id = ?")->execute([$id]);
}

$main_server = $db->query("SELECT * FROM server_configs WHERE id = 1")->fetch();
$nas_routers = $db->query("SELECT * FROM nas_routers ORDER BY id DESC text-end")->fetchAll();

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include __DIR__ . '/../includes/header.php';
?>

<div id="content-area" class="text-end">
    <h4 class="fw-bold text-persian-blue mb-4"><i class="bi bi-hdd-network-fill me-2"></i> مدیریت زیرساخت میکروتیک</h4>
    <?= $message ?>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">اتصال به API میکروتیک (v7)</h6>
                <form method="post" id="apiTestForm">
                    <div class="mb-3">
                        <label class="small fw-bold">نام مستعار سرور:</label>
                        <input type="text" name="server_name" id="api_name" value="<?= $main_server['server_name'] ?? '' ?>" class="form-control shadow-none">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="small fw-bold">IP سرور:</label>
                            <input type="text" name="ip_address" id="api_ip" value="<?= $main_server['ip_address'] ?? '' ?>" class="form-control text-ltr text-center shadow-none">
                        </div>
                        <div class="col-4">
                            <label class="small fw-bold">Port:</label>
                            <input type="number" name="api_port" id="api_port" value="<?= $main_server['api_port'] ?? '8728' ?>" class="form-control text-center shadow-none">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="small fw-bold">User:</label>
                            <input type="text" name="api_user" id="api_user" value="<?= $main_server['api_user'] ?? '' ?>" class="form-control text-ltr text-center shadow-none">
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold">Pass:</label>
                            <input type="password" name="api_pass" id="api_pass" value="<?= $main_server['api_pass'] ?? '' ?>" class="form-control text-ltr text-center shadow-none">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="save_main_server" class="btn btn-persian w-100 fw-bold">ذخیره</button>
                        <button type="button" onclick="testMikrotik()" class="btn btn-outline-info"><i class="bi bi-lightning-charge"></i> تست</button>
                    </div>
                    <div id="testResult" class="mt-3"></div>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="fw-bold mb-3">افزودن Radius NAS (روترهای فرعی)</h6>
                <form method="post" class="row g-2">
                    <div class="col-md-4"><input type="text" name="nas_name" class="form-control form-control-sm" placeholder="نام روتر" required></div>
                    <div class="col-md-4"><input type="text" name="nas_ip" class="form-control form-control-sm text-ltr text-center" placeholder="IP روتر" required></div>
                    <div class="col-md-3"><input type="text" name="nas_secret" class="form-control form-control-sm text-center" placeholder="Secret" required></div>
                    <div class="col-md-1"><button type="submit" name="add_nas_router" class="btn btn-sm btn-dark w-100"><i class="bi bi-plus"></i></button></div>
                </form>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <table class="table table-hover align-middle mb-0 text-center small">
                    <thead class="table-light">
                        <tr>
                            <th>نام روتر</th>
                            <th>آی‌پی (NAS IP)</th>
                            <th>Shared Secret</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($nas_routers as $nas): ?>
                        <tr>
                            <td class="fw-bold"><?= $nas['nas_name'] ?></td>
                            <td class="text-ltr"><?= $nas['nas_ip'] ?></td>
                            <td><code><?= $nas['nas_secret'] ?></code></td>
                            <td>
                                <a href="?delete_nas=<?= $nas['id'] ?>" class="btn btn-sm text-danger border-0 ajax-link"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($nas_routers)) echo "<tr><td colspan='4' class='py-4 text-muted small'>هیچ روتری تعریف نشده است.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function testMikrotik() {
    const res = document.getElementById('testResult');
    res.innerHTML = '<div class="alert alert-info py-2 small"><span class="spinner-border spinner-border-sm me-2"></span> در حال تست اتصال به میکروتیک...</div>';
    
    $.ajax({
        url: '/admin/api_test_handler.php',
        method: 'POST',
        data: {
            ajax_test: 1,
            ip: $('#api_ip').val(),
            port: $('#api_port').val(),
            user: $('#api_user').val(),
            pass: $('#api_pass').val()
        },
        success: function(response) {
            res.innerHTML = response;
        },
        error: function() {
            res.innerHTML = '<div class="alert alert-danger py-2 small">خطای سیستمی در اجرای اسکریپت تست!</div>';
        }
    });
}
</script>

<?php if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) include __DIR__ . '/../includes/footer.php'; ?>