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
        onOk: () => { window.location.href = "../public/logout.php"; }
    });
});

/* ---------- GLOBAL PROFILE DROPDOWN LOGIC ---------- */
const profileWrapper = document.getElementById("profileWrapper");
const profileDropdown = document.getElementById("profileDropdown");

if (profileWrapper && profileDropdown) {
    profileWrapper.addEventListener("click", (e) => {
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

/* ---------- DROPDOWN LOGOUT ---------- */
const logoutDropdownBtn = document.getElementById("logoutDropdownBtn");
if (logoutDropdownBtn) {
    logoutDropdownBtn.addEventListener("click", () => {
        Popup.open({
            title: "Confirm Logout",
            message: "Are you sure you want to logout?",
            type: "warning",
            onOk: () => { window.location.href = "../public/logout.php"; }
        });
    });
}

/* ---------- GLOBAL CHANGE PASSWORD MODAL ---------- */
const changePasswordBtn = document.getElementById("changePasswordBtn");
const passModal = document.getElementById("changePasswordModal");
const closePassBtn = document.getElementById("closeChangePassword");
const changeForm = document.getElementById("changePasswordForm");
const newPasswordInput = document.getElementById("newPassword");
const strengthFill = document.getElementById("strengthFill");
const strengthText = document.getElementById("strengthText");

function closePassModal(callback) {
    if (!passModal) return;
    passModal.classList.remove("show");
    
    // Ensure modal is fully hidden before callback
    requestAnimationFrame(() => {
        setTimeout(() => {
            if (changeForm) changeForm.reset();
            if (strengthFill) strengthFill.style.width = "0%";
            if (strengthText) strengthText.textContent = "Password strength";
            // Ensure modal is not interfering with popups
            passModal.style.pointerEvents = "none";
            if (callback) callback();
        }, 200);
    });
}

if (changePasswordBtn && passModal) {
    changePasswordBtn.addEventListener("click", () => { 
        passModal.style.pointerEvents = "auto";  // Re-enable interactions
        passModal.classList.add("show"); 
    });
}

/* Toggle password visibility */
const togglePasswordBtns = document.querySelectorAll(".toggle-password");
togglePasswordBtns.forEach(btn => {
    btn.addEventListener("click", (e) => {
        e.preventDefault();
        const targetId = btn.getAttribute("data-target");
        const input = document.getElementById(targetId);
        if (input) {
            const isPassword = input.type === "password";
            input.type = isPassword ? "text" : "password";
            // Toggle eye icon
            const icon = btn.querySelector("i");
            if (icon) {
                icon.classList.toggle("fa-eye");
                icon.classList.toggle("fa-eye-slash");
            }
        }
    });
});

if (closePassBtn && passModal) {
    closePassBtn.addEventListener("click", () => { closePassModal(); });
}
if (passModal) {
    passModal.addEventListener("click", (e) => {
        if (e.target === passModal) closePassModal();
    });
}

if (changeForm) {
    changeForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const currentPassword = changeForm.querySelector("input[name='current_password']").value;
        const newPassword = changeForm.querySelector("input[name='new_password']").value;
        const confirmPassword = changeForm.querySelector("input[name='confirm_password']").value;

        if (!currentPassword) {
            Popup.open({ 
                title: "Error", 
                message: "Please enter your current password.", 
                type: "danger",
                showCancel: false 
            });
            return;
        }

        if (newPassword !== confirmPassword) {
            Popup.open({ 
                title: "Error", 
                message: "New passwords do not match.", 
                type: "danger",
                showCancel: false 
            });
            return;
        }

        // Check minimum length first
        if (newPassword.length < 6) {
            Popup.open({ 
                title: "Error", 
                message: "Password must be at least 6 characters.", 
                type: "danger",
                showCancel: false 
            });
            return;
        }

        let strength = 0;
        if (newPassword.length >= 6) strength++;
        if (newPassword.length >= 10) strength++;
        if (/[A-Z]/.test(newPassword)) strength++;
        if (/[0-9]/.test(newPassword)) strength++;
        if (/[^A-Za-z0-9]/.test(newPassword)) strength++;

        if (strength <= 3) {
            Popup.open({
                title: "Weak Password",
                message: "Your password is weak/medium. Are you sure you want to continue?",
                type: "warning",
                onOk: () => { submitPasswordForm(); },
                showCancel: true
            });
            return;
        }
        submitPasswordForm();
    });
}

function submitPasswordForm() {
    const formData = new FormData(changeForm);
    fetch("../api/change-password-process.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        closePassModal(() => {
            // Ensure modal is fully hidden and not blocking popups
            setTimeout(() => {
                if (data.toLowerCase().includes("success")) {
                    Popup.open({ 
                        title: "Success", 
                        message: "Password changed successfully!", 
                        type: "success",
                        showCancel: false 
                    });
                } else {
                    Popup.open({ 
                        title: "Error", 
                        message: data, 
                        type: "danger",
                        showCancel: false 
                    });
                }
            }, 300);
        });
    })
    .catch(() => {
        closePassModal(() => {
            setTimeout(() => { 
                Popup.open({ 
                    title: "Error", 
                    message: "Network error.", 
                    type: "danger",
                    showCancel: false 
                }); 
            }, 300);
        });
    });
}

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
        } else if (strength <= 3) {
            strengthFill.style.background = "#f59e0b";
            strengthText.textContent = "Medium password";
        } else {
            strengthFill.style.background = "#22c55e";
            strengthText.textContent = "Strong password";
        }
        if (value.length === 0) {
            strengthFill.style.width = "0%";
            strengthText.textContent = "Password strength";
        }
    });
}