<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

// امنیت: فقط ادمین | Admin Security
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /auth/login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';
$message = "";

// عملیات مدیریت لوکیشن | CRUD Operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ۱. افزودن لوکیشن جدید
    if (isset($_POST['add_location'])) {
        $name    = htmlspecialchars($_POST['country_name']);
        $code    = htmlspecialchars($_POST['country_code']);
        $addr    = htmlspecialchars($_POST['server_address']);
        $local   = htmlspecialchars($_POST['local_ip_range']);
        $remote  = htmlspecialchars($_POST['remote_ip_range']);
        
        $stmt = $db->prepare("INSERT INTO locations (country_name, country_code, server_address, local_ip_range, remote_ip_range) VALUES (?, ?, ?, ?, ?)");
        if($stmt->execute([$name, $code, $addr, $local, $remote])) {
            $message = "<div class='alert alert-success shadow-sm border-0'>لوکیشن جدید با موفقیت ثبت شد.</div>";
        }
    }

    // ۲. تغییر وضعیت (فعال/غیرفعال)
    if (isset($_POST['toggle_status'])) {
        $id = intval($_POST['id']);
        $new_status = ($_POST['current'] == 'active') ? 'inactive' : 'active';
        $db->prepare("UPDATE locations SET status = ? WHERE id = ?")->execute([$new_status, $id]);
    }

    // ۳. حذف لوکیشن
    if (isset($_POST['delete_loc'])) {
        $id = intval($_POST['id']);
        $db->prepare("DELETE FROM locations WHERE id = ?")->execute([$id]);
        $message = "<div class='alert alert-warning shadow-sm border-0'>لوکیشن حذف شد.</div>";
    }
}

// واکشی لیست لوکیشن‌ها | Fetch Locations
$locations = $db->query("SELECT * FROM locations ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid text-end">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-persian-blue"><i class="bi bi-geo-fill me-2"></i> پیکربندی لوکیشن‌ها و شبکه</h4>
        <button class="btn btn-persian btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#locModal">
            <i class="bi bi-plus-lg me-1"></i> افزودن لوکیشن
        </button>
    </div>

    <?= $message ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-dark">
                    <tr>
                        <th class="p-3">کشور</th>
                        <th>Host/IP</th>
                        <th>Local IP</th>
                        <th>Remote Pool</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($locations as $l): ?>
                    <tr>
                        <td class="p-3 fw-bold"><?= htmlspecialchars($l['country_name']) ?> (<?= strtoupper($l['country_code']) ?>)</td>
                        <td><code class="text-primary"><?= $l['server_address'] ?></code></td>
                        <td><span class="badge bg-light text-dark border"><?= $l['local_ip_range'] ?></span></td>
                        <td><small class="text-muted"><?= $l['remote_ip_range'] ?></small></td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <input type="hidden" name="current" value="<?= $l['status'] ?>">
                                <button type="submit" name="toggle_status" class="btn btn-sm rounded-pill px-3 <?= $l['status']=='active'?'btn-success':'btn-secondary' ?>" style="font-size: 11px;">
                                    <?= $l['status'] == 'active' ? 'فعال' : 'غیرفعال' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <form method="post" onsubmit="return confirm('حذف شود؟');" class="d-inline">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <button type="submit" name="delete_loc" class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="locModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content border-0 shadow-lg rounded-4 text-end">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold text-persian-blue">تعریف لوکیشن و رنج IP</h5>
                <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-8">
                        <label class="form-label small fw-bold">نام کشور:</label>
                        <input type="text" name="country_name" class="form-control" placeholder="آلمان" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-bold">کد (DE):</label>
                        <input type="text" name="country_code" class="form-control text-center" placeholder="de" required>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label small fw-bold">آدرس سرور (Host):</label>
                        <input type="text" name="server_address" class="form-control text-ltr text-center" placeholder="srv1.domain.com" required>
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label small fw-bold text-success">Local IP (Fixed):</label>
                        <input type="text" name="local_ip_range" class="form-control text-center" value="10.10.10.1" required>
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label small fw-bold text-danger">Remote Range:</label>
                        <input type="text" name="remote_ip_range" class="form-control text-center" placeholder="10.10.10.2-10.10.10.254" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="submit" name="add_location" class="btn btn-persian w-100 py-2 fw-bold">ذخیره تنظیمات</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>