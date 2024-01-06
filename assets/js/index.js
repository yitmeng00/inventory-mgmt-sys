document.addEventListener("DOMContentLoaded", function () {
    var currentPage = document.body.dataset.page;

    switch (currentPage) {
        case "login":
            Login.initLogin();
            break;
        case "dashboard":
            Dashboard.initDashboard();
            break;
        case "product":
            Product.initProduct();
            break;
        case "supplier":
            Supplier.initSupplier();
            break;
        case "transaction":
            Transaction.initTransaction();
            break;
    }
});

const toggleNavbar = () => {
    const NAVBAR_CONTENT = document.querySelector(".ims__navbar-content");
    const ICON_TOGGLER = document.getElementById("ims__navbar-toggler");
    const NAVBAR_LOGO = document.getElementById("ims__navbar-logo");

    NAVBAR_CONTENT.classList.toggle("ims__collapsible-navbar");
    ICON_TOGGLER.classList.toggle("fa-bars");
    ICON_TOGGLER.classList.toggle("fa-xmark");

    if (NAVBAR_CONTENT.classList.contains("ims__collapsible-navbar")) {
        NAVBAR_LOGO.src = "assets/images/inventory-mgmt-sys-logo-small.png";
    } else {
        NAVBAR_LOGO.src = "assets/images/inventory-mgmt-sys-logo-large.png";
    }
};
