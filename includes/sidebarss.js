const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("toggleBtn") || document.getElementById("toggleSidebar");
const toggleIcon = document.getElementById("toggleIcon") || (toggleBtn ? toggleBtn.querySelector("i") : null);
const main = document.querySelector(".rp-dashboard") || document.getElementById("mainContent");
const logoutBtn = document.getElementById("logoutBtn"); 

if (toggleBtn) toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("expanded");
    sidebar.classList.toggle("collapsed");
    main.classList.toggle("expanded");

    if (toggleIcon) {
        toggleIcon.classList.toggle("fa-bars");
        toggleIcon.classList.toggle("fa-xmark");
    }
});

if (logoutBtn) logoutBtn.addEventListener("click", () => {
        Popup.open({
            title: "Confirm Logout",
            message: "Are you sure you want to logout?",
            type: "warning",
            onOk: () => {
                // actually destroy
                window.location.href = "../public/logout.php";
            }
        });
    });