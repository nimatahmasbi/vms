<?php
// Display VPN Config | نمایش کانفیگ وی‌پی‌ان
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) header("Location: auth/login.php");

$userId = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM services WHERE user_id = ?");
$stmt->execute([$userId]);
$service = $stmt->fetch();

if (!$service) die("No active service found | سرویس فعالی یافت نشد");

// ساختار فایل کانفیگ وایرگارد
$conf = "[Interface]\nPrivateKey = YOUR_PRIVATE_KEY\nAddress = {$service['remote_ip']}/32\nDNS = 1.1.1.1\n\n[Peer]\nPublicKey = SERVER_PUB_KEY\nEndpoint = SERVER_IP:51820\nAllowedIPs = 0.0.0.0/0";
$qr_data = urlencode($conf);
?>

<div class="container text-center py-5">
    <h3>Scan QR Code | اسکن کد</h3>
    <img src="../api/get_qr.php?data=<?php echo $qr_data; ?>" class="img-fluid border p-3 bg-white" alt="QR Code">
    
    <div class="mt-4">
        <textarea class="form-control" rows="8" readonly><?php echo $conf; ?></textarea>
        <button class="btn btn-primary mt-2">Download .conf | دانلود فایل</button>
    </div>
</div>