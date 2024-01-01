<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar bg-light h-100 p-0">
    <div class="ims__navbar-content border border-dark d-flex flex-column h-100 py-3 pe-3 overflow-hidden">
        <div class="ims__navbar-logo-container ps-3">
            <i class="fa-solid fa-bars ims__navbar-icon" id="ims__navbar-toggler" onclick="toggleNavbar()"></i>
        </div>
        <div class="mt-3 mb-5 ps-3">
            <img id="ims__navbar-logo" class="w-100 img-fluid" src="assets/images/inventory-mgmt-sys-logo-large.png">
        </div>
        <div class="ims__navbar-link-container">
            <ul class="navbar-nav flex-column">
                <li class="nav-item position-relative">
                    <a href="index.php" class="nav-link ps-3 align-items-center <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                        <div class="d-flex align-items-center gap-1">
                            <div class="ims__navbar-logo-container d-flex align-items-center">
                                <i class="fa-solid fa-table ims__navbar-icon"></i>
                            </div>
                            <div>
                                <p class="m-0">Dashboard</p>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="nav-item position-relative">
                    <a href="product.php" class="nav-link ps-3 align-items-center <?php echo ($current_page == 'product.php') ? 'active' : ''; ?>">
                        <div class="d-flex align-items-center gap-1">
                            <div class="ims__navbar-logo-container d-flex align-items-center">
                                <i class="fa-solid fa-box ims__navbar-icon"></i>
                            </div>
                            <div>
                                <p class="m-0">Product</p>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="nav-item position-relative">
                    <a href="transaction.php" class="nav-link ps-3 align-items-center <?php echo ($current_page == 'transaction.php') ? 'active' : ''; ?>">
                        <div class="d-flex align-items-center gap-1">
                            <div class="ims__navbar-logo-container d-flex align-items-center">
                                <i class="fa-solid fa-money-bill-transfer ims__navbar-icon"></i>
                            </div>
                            <div>
                                <p class="m-0">Transaction</p>
                            </div>
                        </div>
                    </a>

                </li>
                <li class="nav-item position-relative">
                    <a href="supplier.php" class="nav-link ps-3 align-items-center <?php echo ($current_page == 'supplier.php') ? 'active' : ''; ?>">
                        <div class="d-flex align-items-center gap-1">
                            <div class="ims__navbar-logo-container d-flex align-items-center">
                                <i class="fa-solid fa-truck-field ims__navbar-icon"></i>
                            </div>
                            <div>
                                <p class="m-0">Supplier</p>
                            </div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>