<?php
// Dynamic QR Code Generator | تولید لحظه‌ای کیوآر
require_once '../libs/phpqrcode/qrlib.php';
session_start();

if (!isset($_SESSION['user_id'])) die("Access Denied");

$conf = $_GET['data']; // محتوای فایل کانفیگ وایرگارد
header('Content-Type: image/png');
QRcode::png($conf, null, QR_ECLEVEL_L, 10);