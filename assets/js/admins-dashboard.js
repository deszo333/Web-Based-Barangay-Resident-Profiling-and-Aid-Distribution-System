document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const main = document.getElementById("mainContent");
    const icon = document.getElementById("sidebarIcon");

    const logoutBtn = document.getElementById("logoutBtn"); 

    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("expanded");
        sidebar.classList.toggle("collapsed");
        main.classList.toggle("expanded");

        icon.classList.toggle("fa-bars");
        icon.classList.toggle("fa-xmark");
    });

    /* ---------- LOGOUT CONFIRM (UNCHANGED) ---------- */
    logoutBtn.addEventListener("click", () => {
        Popup.open({
            title: "Confirm Logout",
            message: "Are you sure you want to logout?",
            type: "warning",
            onOk: () => {
                window.location.href = "../public/logout.php";
            }
        });
    });

    /* ---------- PROFILE DROPDOWN ---------- */
    const profileBtn = document.getElementById("profileBtn");
    const profileDropdown = document.getElementById("profileDropdown");

    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle("show");
        });

        document.addEventListener("click", () => {
            profileDropdown.classList.remove("show");
        });

        profileDropdown.addEventListener("click", (e) => {
            e.stopPropagation();
        });
    }

    /* ---------- CHANGE PASSWORD MODAL ---------- */
    const changePasswordBtn = document.getElementById("changePasswordBtn");
    const modal = document.getElementById("changePasswordModal");
    const closeBtn = document.getElementById("closeChangePassword");
    const changeForm = document.getElementById("changePasswordForm");

    function closeModal(callback) {
        if (!modal) return;

        modal.classList.remove("show");

        // wait for browser repaint (NOT just timeout)
        requestAnimationFrame(() => {
            setTimeout(() => {
                if (changeForm) changeForm.reset();
                if (callback) callback();
            }, 200);
        });
    }

    // open modal
    if (changePasswordBtn && modal) {
        changePasswordBtn.addEventListener("click", () => {
            modal.classList.add("show");
        });
    }

    // close modal button
    if (closeBtn && modal) {
        closeBtn.addEventListener("click", () => {
            modal.classList.remove("show");
        });
    }

    // close modal when clicking outside
    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.classList.remove("show");
            }
        });
    }

    /* ---------- CHANGE PASSWORD AJAX + POPUP ---------- */
    if (changeForm) {
    changeForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const newPassword = changeForm.querySelector("input[name='new_password']").value;
        const confirmPassword = changeForm.querySelector("input[name='confirm_password']").value;

        if (newPassword !== confirmPassword) {
            Popup.open({
                title: "Error",
                message: "Passwords do not match.",
                type: "error"
            });
            return;
        }

        let strength = 0;

        if (newPassword.length >= 6) strength++;
        if (newPassword.length >= 10) strength++;
        if (/[A-Z]/.test(newPassword)) strength++;
        if (/[0-9]/.test(newPassword)) strength++;
        if (/[^A-Za-z0-9]/.test(newPassword)) strength++;

        const isWeak = strength <= 1;
        const isMedium = strength > 1 && strength <= 3;

        if (isWeak || isMedium) {
            Popup.open({
                title: "Weak Password Warning",
                message: "Your password is weak/medium. Are you sure you want to continue?",
                type: "warning",
                onOk: () => {
                    submitPassword();
                }
            });
            return;
        }

        submitPassword();
    });

    function submitPassword() {
    const formData = new FormData(changeForm);

    fetch("../api/change-password-process.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {

        closeModal(() => {
            setTimeout(() => {
                if (data.toLowerCase().includes("success")) {
                    Popup.open({
                        title: "Success",
                        message: "Password changed successfully!",
                        type: "success"
                    });
                } else {
                    Popup.open({
                        title: "Error",
                        message: data,
                        type: "error"
                    });
                }
            }, 50);
        });

    })
    .catch(() => {

        closeModal(() => {
            setTimeout(() => {
                Popup.open({
                    title: "Error",
                    message: "Something went wrong. Please try again.",
                    type: "error"
                });
            }, 50);
        });

    });
}
    }

    /* ---------- DROPDOWN LOGOUT (USES YOUR POPUP SYSTEM) ---------- */
    const logoutDropdownBtn = document.getElementById("logoutDropdownBtn");
    if (logoutDropdownBtn) {
        logoutDropdownBtn.addEventListener("click", () => {
            Popup.open({
                title: "Confirm Logout",
                message: "Are you sure you want to logout?",
                type: "warning",
                onOk: () => {
                    window.location.href = "../public/logout.php";
                }
            });
        });
    }

    const newPasswordInput = document.getElementById("newPassword");
    const strengthFill = document.getElementById("strengthFill");
    const strengthText = document.getElementById("strengthText");

    if (newPasswordInput) {
        newPasswordInput.addEventListener("input", () => {
            const value = newPasswordInput.value;

            let strength = 0;

            if (value.length >= 6) strength++;
            if (value.length >= 10) strength++;
            if (/[A-Z]/.test(value)) strength++;
            if (/[0-9]/.test(value)) strength++;
            if (/[^A-Za-z0-9]/.test(value)) strength++;

            let percent = (strength / 5) * 100;

            strengthFill.style.width = percent + "%";

            if (strength <= 1) {
                strengthFill.style.background = "#ef4444";
                strengthText.textContent = "Weak password";
            }
            else if (strength <= 3) {
                strengthFill.style.background = "#f59e0b";
                strengthText.textContent = "Medium password";
            }
            else {
                strengthFill.style.background = "#22c55e";
                strengthText.textContent = "Strong password";
            }

            if (value.length === 0) {
                strengthFill.style.width = "0%";
                strengthText.textContent = "Password strength";
            }
        });
    }

});