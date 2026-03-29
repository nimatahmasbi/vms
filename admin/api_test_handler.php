<?php
// فایل: admin/api_test_handler.php
require_once __DIR__ . '/../includes/routeros_api.class.php'; // کلاس API میکروتیک را اینجا قرار دهید

if (isset($_POST['ajax_test'])) {
    $api = new RouterosAPI();
    $api->debug = false;

    if ($api->connect($_POST['ip'], $_POST['user'], $_POST['pass'], $_POST['port'])) {
        echo '<div class="alert alert-success py-1 small mb-0"><i class="bi bi-check-all"></i> ارتباط با موفقیت برقرار شد.</div>';
        $api->disconnect();
    } else {
        echo '<div class="alert alert-danger py-1 small mb-0"><i class="bi bi-exclamation-triangle"></i> خطا در اتصال! تنظیمات را چک کنید.</div>';
    }
}