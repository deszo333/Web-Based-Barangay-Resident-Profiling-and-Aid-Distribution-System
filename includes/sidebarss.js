const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("toggleBtn");
const toggleIcon = document.getElementById("toggleIcon");
const main = document.querySelector(".rp-dashboard");
const logoutBtn = document.getElementById("logoutBtn"); 

toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("expanded");
    sidebar.classList.toggle("collapsed");
    main.classList.toggle("expanded");

    toggleIcon.classList.toggle("fa-bars");
    toggleIcon.classList.toggle("fa-xmark");
});

logoutBtn.addEventListener("click", () => {
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