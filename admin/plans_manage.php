<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /auth/login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';
$message = "";

// عملیات مدیریت پلن | Plan Management
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['add_plan'])) {
        $title = htmlspecialchars($_POST['title']);
        $gb_vol = intval($_POST['volume_gb']);
        $bytes = $gb_vol * 1024 * 1024 * 1024; // تبدیل گیگابایت به بایت
        $price = $_POST['price'];
        $days = intval($_POST['duration_days']);
        $selected_locs = $_POST['locations'] ?? []; // لوکیشن‌های انتخاب شده

        $db->beginTransaction();
        try {
            // ۱. ثبت پلن اصلی
            $ins = $db->prepare("INSERT INTO plans (title, volume_bytes, price, duration_days) VALUES (?, ?, ?, ?)");
            $ins->execute([$title, $bytes, $price, $days]);
            $plan_id = $db->lastInsertId();

            // ۲. ثبت لوکیشن‌های متصل به این پلن
            if (!empty($selected_locs)) {
                $loc_ins = $db->prepare("INSERT INTO plan_locations (plan_id, location_id) VALUES (?, ?)");
                foreach ($selected_locs as $l_id) {
                    $loc_ins->execute([$plan_id, $l_id]);
                }
            }

            $db->commit();
            $message = "<div class='alert alert-success shadow-sm border-0'>پلن جدید با موفقیت ثبت شد.</div>";
        } catch (Exception $e) {
            $db->rollBack();
            $message = "<div class='alert alert-danger shadow-sm border-0'>خطا در ثبت پلن!</div>";
        }
    }

    if (isset($_POST['delete_plan'])) {
        $id = intval($_POST['id']);
        $db->prepare("DELETE FROM plans WHERE id = ?")->execute([$id]);
    }
}

// واکشی داده‌ها
$plans = $db->query("SELECT * FROM plans ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$locations = $db->query("SELECT id, country_name FROM locations WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid text-end">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-persian-blue"><i class="bi bi-cart-check-fill me-2"></i> مدیریت پلن‌های فروش</h4>
        <button class="btn btn-persian btn-sm" data-bs-toggle="modal" data-bs-target="#planModal">
            <i class="bi bi-plus-lg me-1"></i> تعریف پلن جدید
        </button>
    </div>

    <?= $message ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive text-center">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="p-3">عنوان پلن</th>
                        <th>حجم (GB)</th>
                        <th>مدت (روز)</th>
                        <th>لوکیشن‌های مجاز</th>
                        <th>قیمت (تومان)</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($plans as $p): 
                        // واکشی لوکیشن‌های هر پلن
                        $p_id = $p['id'];
                        $p_locs = $db->query("SELECT l.country_name FROM plan_locations pl JOIN locations l ON pl.location_id = l.id WHERE pl.plan_id = $p_id")->fetchAll(PDO::FETCH_COLUMN);
                    ?>
                    <tr>
                        <td class="p-3 fw-bold"><?= htmlspecialchars($p['title']) ?></td>
                        <td><?= round($p['volume_bytes'] / (1024**3)) ?> GB</td>
                        <td><?= $p['duration_days'] ?> روز</td>
                        <td>
                            <?php foreach($p_locs as $loc_name): ?>
                                <span class="badge bg-info-subtle text-info border border-info small px-2"><?= $loc_name ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td class="text-success fw-bold"><?= number_format($p['price']) ?></td>
                        <td>
                            <form method="post" class="d-inline" onsubmit="return confirm('حذف شود؟');">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" name="delete_plan" class="btn btn-sm text-danger border-0"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="planModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered text-end">
        <form method="post" class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold text-persian-blue">تعریف پلن فروش جدید</h5>
                <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">عنوان پلن:</label>
                        <input type="text" name="title" class="form-control" placeholder="مثلاً: ۳۰ روزه ۵۰ گیگابایت" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">حجم (GB):</label>
                        <input type="number" name="volume_gb" class="form-control text-center" value="50" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">مدت (روز):</label>
                        <input type="number" name="duration_days" class="form-control text-center" value="30" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">قیمت (تومان):</label>
                        <input type="number" name="price" class="form-control text-center" placeholder="150000" required>
                    </div>

                    <div class="col-12 mt-3">
                        <label class="form-label d-block small fw-bold border-bottom pb-2 mb-3">انتخاب لوکیشن‌های مجاز برای این پلن:</label>
                        <div class="row g-2">
                            <?php foreach($locations as $loc): ?>
                            <div class="col-md-4">
                                <div class="form-check form-check-inline border rounded-3 p-2 w-100 bg-light-subtle">
                                    <input class="form-check-input ms-2 me-0" type="checkbox" name="locations[]" value="<?= $loc['id'] ?>" id="loc_<?= $loc['id'] ?>">
                                    <label class="form-check-label w-100 cursor-pointer" for="loc_<?= $loc['id'] ?>">
                                        <?= $loc['country_name'] ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="submit" name="add_plan" class="btn btn-persian w-100 py-2 fw-bold shadow">ثبت و انتشار پلن</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>