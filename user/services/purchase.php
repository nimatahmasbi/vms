<?php
// Start Payment Process | شروع فرآیند پرداخت
session_start();
require_once '../../config/db.php';

$plan_id = $_POST['plan_id'];
$stmt = $db->prepare("SELECT price FROM plans WHERE id = ?");
$stmt->execute([$plan_id]);
$price = $stmt->fetchColumn();

$data = array(
    "merchant_id" => "YOUR_MERCHANT_ID",
    "amount" => $price * 10, // تبدیل تومان به ریال برای زرین پال
    "callback_url" => "https://my.21s.ir/api/callback.php?amount=".$price,
    "description" => "خرید سرویس VPN",
);

$jsonData = json_encode($data);
$ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$result = curl_exec($ch);
$res = json_decode($result, true);

if ($res['data']['code'] == 100) {
    header('Location: https://www.zarinpal.com/pg/StartPay/' . $res['data']['authority']);
} else {
    echo "Error in Payment Request | خطا در درخواست پرداخت";
}