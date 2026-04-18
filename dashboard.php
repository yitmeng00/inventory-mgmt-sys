<?php
require_once 'lib/jwt_helper.php';
$auth_user  = JWTHelper::authenticate();
$page_title = 'Dashboard';
$include_chartjs = true;
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/header.php'; ?>

<body class="bg-gray-50 font-sans" data-page="dashboard">
    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/navbar.php'; ?>
        <!-- Main area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Topbar -->
            <header class="bg-white border-b border-gray-200 px-6 h-14 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <button id="sidebar-toggle" class="btn-icon">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h1 class="page-title">Dashboard</h1>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <i class="fa-regular fa-calendar hidden! md:block"></i>
                    <span id="current-date" class="text-[11px] md:text-sm"></span>
                </div>
            </header>
            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6" id="dashboard-content">
                <!-- Stat cards -->
                <div id="stat-cards" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
                    <?php foreach (
                        [
                            ['label' => 'Total Stock',  'id' => 'stat-total-stock',   'icon' => 'fa-cubes',            'color' => 'bg-blue-50 text-blue-600'],
                            ['label' => 'Low Stock',    'id' => 'stat-low-stock',     'icon' => 'fa-triangle-exclamation', 'color' => 'bg-amber-50 text-amber-600'],
                            ['label' => 'Out of Stock', 'id' => 'stat-out-of-stock',  'icon' => 'fa-box-open',         'color' => 'bg-red-50 text-red-600'],
                            ['label' => 'Total Purchases', 'id' => 'stat-purchases',    'icon' => 'fa-cart-shopping',    'color' => 'bg-indigo-50 text-indigo-600'],
                            ['label' => 'Total Sales',  'id' => 'stat-sales',         'icon' => 'fa-sack-dollar',      'color' => 'bg-emerald-50 text-emerald-600'],
                        ] as $stat
                    ): ?>
                        <div class="stat-card">
                            <div class="stat-icon <?= $stat['color'] ?>">
                                <i class="fa-solid <?= $stat['icon'] ?>"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide"><?= $stat['label'] ?></p>
                                <p id="<?= $stat['id'] ?>" class="text-xl md:text-2xl font-bold text-gray-900 mt-0.5">—</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Charts row 1 -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-4">
                    <div class="card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="section-title">Products by Category</h3>
                            <button class="btn-secondary btn-sm" onclick="IMS.exportChart('product-chart')">
                                <i class="fa-solid fa-download mr-1"></i>Export
                            </button>
                        </div>
                        <canvas id="product-chart"></canvas>
                    </div>
                    <div class="card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="section-title">Revenue & Profit</h3>
                            <button class="btn-secondary btn-sm" onclick="IMS.exportChart('revenue-chart')">
                                <i class="fa-solid fa-download mr-1"></i>Export
                            </button>
                        </div>
                        <canvas id="revenue-chart"></canvas>
                    </div>
                </div>
                <!-- Charts row 2 -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-4">
                    <div class="card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="section-title">Monthly Purchases</h3>
                            <button class="btn-secondary btn-sm" onclick="IMS.exportChart('purchase-chart')">
                                <i class="fa-solid fa-download mr-1"></i>Export
                            </button>
                        </div>
                        <canvas id="purchase-chart"></canvas>
                    </div>
                    <div class="card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="section-title">Monthly Sales</h3>
                            <button class="btn-secondary btn-sm" onclick="IMS.exportChart('sale-chart')">
                                <i class="fa-solid fa-download mr-1"></i>Export
                            </button>
                        </div>
                        <canvas id="sale-chart"></canvas>
                    </div>
                </div>
                <!-- Charts row 3 -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="section-title">Low Stock Items</h3>
                            <a href="/products.php" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View all →</a>
                        </div>
                        <canvas id="low-stock-chart"></canvas>
                    </div>
                    <div class="card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="section-title">Best-Selling Products</h3>
                        </div>
                        <canvas id="best-selling-chart"></canvas>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="/assets/js/pages/dashboard.js?v=2.0" defer></script>
    <script>
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    </script>
</body>

</html>