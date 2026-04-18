<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user_role    = $auth_user->role ?? 'staff';
$user_name    = ($auth_user->first_name ?? '') . ' ' . ($auth_user->last_name ?? '');
$user_initials = strtoupper(substr($auth_user->first_name ?? 'U', 0, 1) . substr($auth_user->last_name ?? '', 0, 1));

function nav_active(string $page, string $current): string
{
    return $page === $current ? 'active' : '';
}
?>

<aside id="sidebar" class="w-60 bg-white border-r border-gray-200 flex flex-col shrink-0 h-screen overflow-y-auto overflow-x-hidden">
    <!-- Logo area -->
    <div class="flex items-center gap-3 px-4 py-5 border-b border-gray-100">
        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shrink-0">
            <img src="assets/images/inventory-mgmt-sys-logo-small.png" />
        </div>
        <div class="sidebar-logo-text overflow-hidden">
            <p class="text-gray-900 font-semibold text-sm leading-tight">InvenTrack</p>
            <p class="text-gray-400 text-xs">Management System</p>
        </div>
    </div>
    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-0.5">
        <p class="nav-group-label">Main</p>
        <a href="/dashboard.php" class="nav-item <?= nav_active('dashboard.php', $current_page) ?>">
            <i class="fa-solid fa-gauge-high"></i>
            <span class="sidebar-label">Dashboard</span>
        </a>
        <p class="nav-group-label">Inventory</p>
        <a href="/products.php" class="nav-item <?= nav_active('products.php', $current_page) ?>">
            <i class="fa-solid fa-box"></i>
            <span class="sidebar-label">Products</span>
        </a>
        <a href="/categories.php" class="nav-item <?= nav_active('categories.php', $current_page) ?>">
            <i class="fa-solid fa-tag"></i>
            <span class="sidebar-label">Categories</span>
        </a>
        <a href="/suppliers.php" class="nav-item <?= nav_active('suppliers.php', $current_page) ?>">
            <i class="fa-solid fa-truck-field"></i>
            <span class="sidebar-label">Suppliers</span>
        </a>
        <p class="nav-group-label">Operations</p>
        <a href="/transactions.php" class="nav-item <?= nav_active('transactions.php', $current_page) ?>">
            <i class="fa-solid fa-money-bill-transfer"></i>
            <span class="sidebar-label">Transactions</span>
        </a>
        <?php if ($user_role === 'admin'): ?>
            <p class="nav-group-label">Administration</p>
            <a href="/users.php" class="nav-item <?= nav_active('users.php', $current_page) ?>">
                <i class="fa-solid fa-users"></i>
                <span class="sidebar-label">Staff Accounts</span>
            </a>
        <?php endif; ?>
        <p class="nav-group-label">Account</p>
        <a href="/profile.php" class="nav-item <?= nav_active('profile.php', $current_page) ?>">
            <i class="fa-solid fa-circle-user"></i>
            <span class="sidebar-label">My Profile</span>
        </a>
    </nav>
    <!-- User card + logout -->
    <div class="px-3 py-3 border-t border-gray-100">
        <div class="flex flex-col md:flex-row items-center gap-3 px-3 py-2.5 rounded-lg">
            <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-semibold shrink-0">
                <?= htmlspecialchars($user_initials) ?>
            </div>
            <div class="sidebar-label overflow-hidden flex-1">
                <p class="text-gray-800 text-sm font-medium leading-tight truncate"><?= htmlspecialchars(trim($user_name)) ?></p>
                <p class="text-gray-400 text-xs capitalize"><?= htmlspecialchars($user_role) ?></p>
            </div>
            <a href="/logout.php" title="Logout" class="text-gray-400 hover:text-red-500 transition-colors shrink-0 cursor-pointer">
                <i class="fa-solid fa-right-from-bracket text-sm"></i>
            </a>
        </div>
    </div>
</aside>