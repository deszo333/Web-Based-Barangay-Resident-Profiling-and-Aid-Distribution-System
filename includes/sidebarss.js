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
    requestAnimationFrame(() => {
        setTimeout(() => {
            if (changeForm) changeForm.reset();
            if (strengthFill) strengthFill.style.width = "0%";
            if (strengthText) strengthText.textContent = "Password strength";
            if (callback) callback();
        }, 200);
    });
}

if (changePasswordBtn && passModal) {
    changePasswordBtn.addEventListener("click", () => { passModal.classList.add("show"); });
}
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
        const newPassword = changeForm.querySelector("input[name='new_password']").value;
        const confirmPassword = changeForm.querySelector("input[name='confirm_password']").value;

        if (newPassword !== confirmPassword) {
            Popup.open({ title: "Error", message: "Passwords do not match.", type: "danger" });
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
                onOk: () => { submitPasswordForm(); }
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
            setTimeout(() => {
                if (data.toLowerCase().includes("success")) {
                    Popup.open({ title: "Success", message: "Password changed successfully!", type: "success" });
                } else {
                    Popup.open({ title: "Error", message: data, type: "danger" });
                }
            }, 50);
        });
    })
    .catch(() => {
        closePassModal(() => {
            setTimeout(() => { Popup.open({ title: "Error", message: "Network error.", type: "danger" }); }, 50);
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