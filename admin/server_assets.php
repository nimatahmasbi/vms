<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/db.php';
// فرض بر این است که کلاس API میکروتik را در includes دارید
// require_once __DIR__ . '/../includes/routeros_api.class.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /auth/login.php"); exit();
}

$message = "";

// --- عملیات تست ارتباط (AJAX یا Post) ---
if (isset($_POST['test_connection'])) {
    $ip = $_POST['mikrotik_ip'];
    $user = $_POST['mikrotik_username'];
    $pass = $_POST['mikrotik_password'];
    $port = intval($_POST['mikrotik_api_port']);

    // اینجا منطق اتصال کلاس API قرار می‌گیرد
    // $API = new RouterosAPI();
    // if ($API->connect($ip, $user, $pass, $port)) { ... }
    $message = "<div class='alert alert-info py-2'>در حال توسعه: کلاس API باید در پوشه includes قرار گیرد.</div>";
}

// --- عملیات ذخیره روتر جدید (NAS) ---
if (isset($_POST['add_external_router'])) {
    $asset_id = $_POST['asset_id'];
    $r_name = $_POST['r_name'];
    $r_ip = $_POST['r_ip'];
    $r_secret = $_POST['r_secret'];

    $ins = $db->prepare("INSERT INTO external_routers (asset_id, router_name, router_ip, shared_secret) VALUES (?, ?, ?, ?)");
    $ins->execute([$asset_id, $r_name, $r_ip, $r_secret]);
    $message = "<div class='alert alert-success py-2'>روتر فرعی با موفقیت ثبت شد.</div>";
}

// واکشی داده‌ها
$locations = $db->query("SELECT * FROM locations")->fetchAll(PDO::FETCH_ASSOC);
$assets = $db->query("SELECT sa.*, l.country_name FROM server_assets sa JOIN locations l ON sa.location_id = l.id")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid text-end">
    <h4 class="fw-bold text-persian-blue mb-4"><i class="bi bi-cpu-fill me-2"></i> مدیریت زیرساخت میکروتیک (v7)</h4>

    <?= $message ?>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">اتصال به API یوزرمنجر</h6></div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="small fw-bold">انتخاب لوکیشن:</label>
                            <select name="location_id" class="form-select shadow-none" required>
                                <?php foreach($locations as $loc): ?>
                                    <option value="<?= $loc['id'] ?>"><?= $loc['country_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-8">
                                <label class="small fw-bold">IP سرور:</label>
                                <input type="text" name="mikrotik_ip" class="form-control text-ltr text-center" placeholder="1.2.3.4" required>
                            </div>
                            <div class="col-4">
                                <label class="small fw-bold">Port:</label>
                                <input type="number" name="mikrotik_api_port" class="form-control text-center" value="8728" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">نام کاربری API:</label>
                            <input type="text" name="mikrotik_username" class="form-control text-center" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">رمز عبور API:</label>
                            <input type="password" name="mikrotik_password" class="form-control text-center" required>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="save_asset" class="btn btn-persian w-100">ذخیره تنظیمات</button>
                            <button type="submit" name="test_connection" class="btn btn-outline-info"><i class="bi bi-lightning"></i> تست</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">روترهای متصل به یوزرمن (NAS)</h6></div>
                <div class="card-body">
                    <table class="table table-sm small">
                        <thead>
                            <tr>
                                <th>لوکیشن</th>
                                <th>IP میکروتیک</th>
                                <th>روترهای خارجی</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($assets as $a): 
                                $routers = $db->prepare("SELECT COUNT(*) FROM external_routers WHERE asset_id = ?");
                                $routers->execute([$a['id']]);
                                $r_count = $routers->fetchColumn();
                            ?>
                            <tr>
                                <td><?= $a['country_name'] ?></td>
                                <td><?= $a['mikrotik_ip'] ?></td>
                                <td><span class="badge bg-secondary"><?= $r_count ?> روتر</span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="openNasModal(<?= $a['id'] ?>)">+ افزودن NAS</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="nasModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered text-end">
        <form method="post" class="modal-content border-0 rounded-4">
            <input type="hidden" name="asset_id" id="modal_asset_id">
            <div class="modal-header border-0">
                <h5 class="fw-bold">افزودن روتر خارجی به یوزرمنجر</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="small fw-bold">نام روتر (Identity):</label>
                    <input type="text" name="r_name" class="form-control" placeholder="Main-Router-Tehran" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">IP روتر فرعی:</label>
                    <input type="text" name="r_ip" class="form-control text-ltr text-center" placeholder="1.2.3.4" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">Shared Secret (RADIUS):</label>
                    <input type="text" name="r_secret" class="form-control text-center" placeholder="123456" required>
                </div>
                <p class="text-muted small"><i class="bi bi-info-circle"></i> این اطلاعات در بخش <code>/user-manager/router</code> میکروتیک ست خواهد شد.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" name="add_external_router" class="btn btn-persian w-100">ثبت روتر در سیستم</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNasModal(id) {
    document.getElementById('modal_asset_id').value = id;
    new bootstrap.Modal(document.getElementById('nasModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>