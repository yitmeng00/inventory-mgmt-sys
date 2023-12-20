document.addEventListener("DOMContentLoaded", function () {
    Dashboard.initDashboard();
});

const toggleNavbar = () => {
    const NAVBAR_CONTENT = document.querySelector(".ims__navbar-content");
    const ICON_TOGGLER = document.getElementById("ims__navbar-toggler");

    NAVBAR_CONTENT.classList.toggle("ims__collapsible-navbar");
    ICON_TOGGLER.classList.toggle("fa-bars");
    ICON_TOGGLER.classList.toggle("fa-xmark");
};
