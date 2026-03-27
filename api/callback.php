<?php
// Payment Callback Handler | مدیریت بازگشت از درگاه
require_once '../config/db.php';
require_once '../core/Wallet.php';

$merchant_id = "YOUR_MERCHANT_ID";
$amount = $_GET['amount']; // مبلغ تراکنش
$authority = $_GET['Authority'];

if ($_GET['Status'] == 'OK') {
    // تایید تراکنش در زرین‌پال (Verification)
    $data = array("merchant_id" => $merchant_id, "authority" => $authority, "amount" => $amount);
    $jsonData = json_encode($data);
    $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
    curl_setopt($ch, CURLOPT_USERAGENT, 'Zarinpal Rest Api v4');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($jsonData)));
    $result = curl_exec($ch);
    $res = json_decode($result, true);

    if (isset($res['data']['code']) && $res['data']['code'] == 100) {
        // ۱. فعال‌سازی سرویس در دیتابیس
        // ۲. واریز سود به کیف پول معرف
        Wallet::addCommission($db, $_SESSION['user_id'], $amount);
        echo "Payment Successful | پرداخت با موفقیت انجام شد";
    }
} else {
    echo "Payment Failed | پرداخت ناموفق بود";
}