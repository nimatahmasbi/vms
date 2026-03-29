<?php
/**
 * Sidebar Component - VMS Project
 * پشتیبانی از تفکیک دسترسی و بارگذاری AJAX
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تشخیص نقش کاربر
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

// تشخیص صفحه فعلی برای حالت سنتی (اگر مستقیم لود شود)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="nav flex-column" id="sidebar-menu">
    
    <?php if ($is_admin): ?>
        <div class="px-3 mb-2 mt-2 small text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;">
            <i class="bi bi-shield-lock me-1"></i> مدیریت سیستم
        </div>
        
        <a href="/admin/dashboard.php" class="nav-link ajax-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>" data-url="/admin/dashboard.php">
            <i class="bi bi-speedometer2 me-2"></i> پیش‌خوان ادمین
        </a>
        
        <a href="/admin/users_manage.php" class="nav-link ajax-link <?= ($current_page == 'users_manage.php') ? 'active' : '' ?>" data-url="/admin/users_manage.php">
            <i class="bi bi-people me-2"></i> مدیریت کاربران
        </a>
        
        <a href="/admin/plans_manage.php" class="nav-link ajax-link <?= ($current_page == 'plans_manage.php') ? 'active' : '' ?>" data-url="/admin/plans_manage.php">
            <i class="bi bi-card-checklist me-2"></i> پلن‌های فروش
        </a>
        
        <a href="/admin/locations_setup.php" class="nav-link ajax-link <?= ($current_page == 'locations_setup.php') ? 'active' : '' ?>" data-url="/admin/locations_setup.php">
            <i class="bi bi-geo-alt me-2"></i> لوکیشن‌ها (سرور)
        </a>
        
        <a href="/admin/server_configs.php" class="nav-link ajax-link <?= ($current_page == 'server_configs.php') ? 'active' : '' ?>" data-url="/admin/server_configs.php">
            <i class="bi bi-hdd-network me-2"></i> تنظیمات میکروتیک (v7)
        </a>

		<a href="/admin/settings.php" class="nav-link ajax-link <?= ($current_page == 'settings.php') ? 'active' : '' ?>" data-url="/admin/settings.php">
			<i class="bi bi-gear-fill me-2"></i> تنظیمات عمومی سامانه
		</a>
    <?php else: ?>
        <div class="px-3 mb-2 mt-2 small text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;">
            <i class="bi bi-person me-1"></i> منوی کاربری
        </div>
        
        <a href="/user/dashboard.php" class="nav-link ajax-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>" data-url="/user/dashboard.php">
            <i class="bi bi-house-door me-2"></i> داشبورد من
        </a>
        
        <a href="/user/services/purchase.php" class="nav-link ajax-link <?= ($current_page == 'purchase.php') ? 'active' : '' ?>" data-url="/user/services/purchase.php">
            <i class="bi bi-cart-plus me-2"></i> خرید سرویس جدید
        </a>

        <a href="/user/referrals.php" class="nav-link ajax-link <?= ($current_page == 'referrals.php') ? 'active' : '' ?>" data-url="/user/referrals.php">
            <i class="bi bi-person-plus me-2"></i> زیرمجموعه‌های من
        </a>
        
        <a href="/user/wallet.php" class="nav-link ajax-link <?= ($current_page == 'wallet.php') ? 'active' : '' ?>" data-url="/user/wallet.php">
            <i class="bi bi-wallet2 me-2"></i> شارژ کیف پول
        </a>
        
        <a href="/user/switch_location.php" class="nav-link ajax-link <?= ($current_page == 'switch_location.php') ? 'active' : '' ?>" data-url="/user/switch_location.php">
            <i class="bi bi-arrow-left-right me-2"></i> تغییر لوکیشن
        </a>
    <?php endif; ?>

    <hr class="text-secondary mx-3 my-2 opacity-25">
    
    <div class="px-3 mb-2 small text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;">
        <i class="bi bi-info-circle me-1"></i> عمومی
    </div>

    <a href="/tickets/list.php" class="nav-link ajax-link <?= ($current_page == 'list.php') ? 'active' : '' ?>" data-url="/tickets/list.php">
        <i class="bi bi-chat-dots me-2"></i> تیکت‌های پشتیبانی
    </a>
    
    <a href="/includes/profile.php" class="nav-link ajax-link <?= ($current_page == 'profile.php') ? 'active' : '' ?>" data-url="/includes/profile.php">
        <i class="bi bi-person-circle me-2"></i> پروفایل من
    </a>
    
    <a href="/auth/logout.php" class="nav-link text-danger mt-3">
        <i class="bi bi-box-arrow-right me-2"></i> خروج از حساب
    </a>
</nav>

<style>
/* استایل‌های اختصاصی سایدبار | Sidebar Custom Styles */
#sidebar-menu .nav-link {
    color: #a0aec0;
    padding: 0.8rem 1.5rem;
    transition: all 0.3s ease;
    border-right: 3px solid transparent;
    font-size: 14px;
}

#sidebar-menu .nav-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.05);
}

#sidebar-menu .nav-link.active {
    color: #00d2ff;
    background: rgba(0, 210, 255, 0.1);
    border-right-color: #00d2ff;
    font-weight: bold;
}

#sidebar-menu .bi {
    font-size: 1.1rem;
}

hr {
    border-top: 1px solid rgba(255,255,255,0.1);
}
</style>