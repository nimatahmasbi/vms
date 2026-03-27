<nav class="nav flex-column">
    <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
        <i class="bi bi-grid-1x2-fill me-2"></i> پیش‌خوان
    </a>
    <a href="users_manage.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users_manage.php' ? 'active' : '' ?>">
        <i class="bi bi-people-fill me-2"></i> مدیریت کاربران
    </a>
    <a href="plans_manage.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'plans_manage.php' ? 'active' : '' ?>">
        <i class="bi bi-box-seam-fill me-2"></i> پلن‌های فروش
    </a>
    <a href="locations_setup.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'locations_setup.php' ? 'active' : '' ?>">
        <i class="bi bi-geo-alt-fill me-2"></i> لوکیشن‌ها (سرور)
    </a>
    <a href="../tickets/list.php" class="nav-link">
        <i class="bi bi-chat-left-dots-fill me-2"></i> تیکت‌های پشتیبانی
    </a>
    <a href="settings.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
        <i class="bi bi-gear-wide-connected me-2"></i> تنظیمات سامانه
    </a>
</nav>