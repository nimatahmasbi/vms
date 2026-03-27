<?php
// Secure Logout | خروج امن از سیستم
session_start();
session_unset();
session_destroy();

// Redirect to login page | هدایت به صفحه ورود
header("Location: login.php");
exit;