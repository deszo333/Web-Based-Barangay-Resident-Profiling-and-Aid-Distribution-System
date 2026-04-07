window.initAdminsDashboard = function() {
    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const main = document.getElementById("mainContent");
    const icon = document.querySelector("#toggleSidebar i");

    const logoutBtn = document.getElementById("logoutBtn");

    if (toggleBtn) toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("expanded");
        sidebar.classList.toggle("collapsed");
        if (main) main.classList.toggle("expanded");
        if (icon) {
            icon.classList.toggle("fa-bars");
            icon.classList.toggle("fa-xmark");
        }
    });

    /* ---------- LOGOUT CONFIRM (UNCHANGED) ---------- */
    if (logoutBtn) logoutBtn.addEventListener("click", () => {
        Popup.open({
            title: "Confirm Logout",
            message: "Are you sure you want to logout?",
            type: "warning",
            onOk: () => {
                window.location.href = "../public/logout.php";
            }
        });
    });

    // Profile dropdown and password modal logic has been moved to includes/sidebarss.js
};

// Call immediately if DOM is ready, otherwise wait
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initAdminsDashboard);
} else {
    window.initAdminsDashboard();
}