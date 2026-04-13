window.initAccountMan = function() {
    // Guard to prevent double-binding listeners
    if (window.amInitialized) return;
    window.amInitialized = true;

    // =========================================================
    // MODAL OPEN/CLOSE HELPERS (consistent with other pages)
    // =========================================================
    const modalOverlay = document.getElementById("modalOverlay");

    function openModal(modalEl) {
        if (modalEl) modalEl.classList.add("show");
        if (modalOverlay) modalOverlay.classList.add("show");
    }

    function closeModalEl(modalEl, formEl) {
        if (modalEl) modalEl.classList.remove("show");
        if (modalOverlay) modalOverlay.classList.remove("show");
        if (formEl) formEl.reset();
    }

    // =========================================================
    // EDIT ACCOUNT MODAL
    // =========================================================
    const editAccountModal = document.getElementById("editAccountModal");
    const closeEditAccountBtn = document.getElementById("closeEditAccountBtn");
    const cancelEditAccountBtn = document.getElementById("cancelEditAccountBtn");
    const editAccountForm = document.getElementById("editAccountForm");

    if (closeEditAccountBtn) {
        closeEditAccountBtn.addEventListener("click", () => {
            closeModalEl(editAccountModal, editAccountForm);
        });
    }
    if (cancelEditAccountBtn) {
        cancelEditAccountBtn.addEventListener("click", () => {
            closeModalEl(editAccountModal, editAccountForm);
        });
    }

    // Close on overlay click
    if (modalOverlay) {
        modalOverlay.addEventListener("click", () => {
            closeModalEl(editAccountModal, editAccountForm);
            closeModalEl(addAccountModal, addAccountForm);
        });
    }

    // Edit button click — delegate from document (handles AJAX-rendered rows too)
    document.addEventListener("click", function (e) {
        const editBtn = e.target.closest(".edit");
        if (editBtn) {
            e.preventDefault();

            const userId   = editBtn.getAttribute("data-id");
            const fullName = editBtn.getAttribute("data-name");
            const username = editBtn.getAttribute("data-username");
            const role     = editBtn.getAttribute("data-role");
            const version  = editBtn.getAttribute("data-version") || "0";

            // Parse full name into first and last
            const nameParts = fullName.split(' ');
            const firstName = nameParts[0];
            const lastName  = nameParts.slice(1).join(' ');

            // Populate form
            document.getElementById("editUserId").value    = userId;
            document.getElementById("editVersion").value   = version;
            document.getElementById("editFirstName").value = firstName;
            document.getElementById("editLastName").value  = lastName;
            document.getElementById("editUsername").value  = username;
            document.getElementById("editRole").value      = role;

            openModal(editAccountModal);
            return;
        }

        // =========================================================
        // DEACTIVATE / ACTIVATE TOGGLE
        // =========================================================
        const btn = e.target.closest(".deactivate") || e.target.closest(".activate");
        if (btn) {
            e.preventDefault();
            const id = btn.getAttribute("data-id");
            const isDeactivating = btn.classList.contains("deactivate");
            const actionText = isDeactivating ? "Deactivate" : "Activate";

            Popup.open({
                title: `Confirm ${actionText}`,
                message: `Are you sure you want to ${actionText.toLowerCase()} this user account?`,
                type: "warning",
                onOk: () => {
                    fetch("../api/toggle_user_status.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ id: id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            Popup.open({
                                title: "Success!",
                                message: `Account successfully ${actionText.toLowerCase()}d.`,
                                type: "success",
                                onOk: () => { location.reload(); }
                            });
                        } else {
                            Popup.open({
                                title: "Action Failed",
                                message: data.message || "Failed to update user status.",
                                type: "danger"
                            });
                        }
                    });
                }
            });
        }
    });

    // =========================================================
    // EDIT FORM SUBMIT (with OCC version)
    // =========================================================
    if (editAccountForm) {
        editAccountForm.addEventListener("submit", (e) => {
            e.preventDefault();

            const userId    = document.getElementById("editUserId").value;
            const version   = document.getElementById("editVersion").value;
            const firstName = document.getElementById("editFirstName").value;
            const lastName  = document.getElementById("editLastName").value;
            const username  = document.getElementById("editUsername").value;
            const role      = document.getElementById("editRole").value;

            Popup.open({
                title: "Confirm Account Update",
                message: `Are you sure you want to update this account?\n\nName: ${firstName} ${lastName}\nUsername: ${username}\nRole: ${role}`,
                type: "warning",
                onOk: () => {
                    const submitBtn = editAccountForm.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = "Updating...";
                    submitBtn.disabled = true;

                    const formData = new FormData(editAccountForm);

                    fetch("../api/update_account_process.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.text())
                    .then(data => {
                        console.log("RAW SERVER RESPONSE:", JSON.stringify(data));
                        console.log("TRIMMED:", data.trim());

                        let response = data.trim();
                        let debugInfo = null;

                        if (response.includes("|")) {
                            const parts = response.split("|");
                            response = parts[0];
                            try {
                                debugInfo = JSON.parse(parts[1]);
                                console.log("OCC DEBUG INFO:", debugInfo);
                            } catch (e) {
                                console.log("Could not parse debug JSON");
                            }
                        }

                        if (response === 'success') {
                            // Immediately bump the version in the edit button's data attribute
                            const editBtn = document.querySelector(`.edit[data-id='${userId}']`);
                            if (editBtn) {
                                const currentVersion = parseInt(editBtn.dataset.version || "1");
                                editBtn.dataset.version = currentVersion + 1;
                            }

                            // Also update the hidden version input so the form itself is current
                            const versionInput = document.getElementById("editVersion");
                            if (versionInput) {
                                versionInput.value = parseInt(versionInput.value || "1") + 1;
                            }

                            closeModalEl(editAccountModal, editAccountForm);

                            Popup.open({
                                title: "Success!",
                                message: "The account has been successfully updated.",
                                type: "success",
                                onOk: () => { location.reload(); }
                            });
                        }
                        // === START OCC ALGORITHM: HANDLE CONFLICT RESPONSE ===
                        else if (response === 'conflict') {
                            Popup.open({
                                title: "Update Conflict",
                                message: "Data changed! Another admin updated this record while you were viewing it. Please close and reopen the record to try again.",
                                type: "danger"
                            });
                        }
                        // === END OCC ALGORITHM ===
                        else {
                            Popup.open({
                                title: "Error",
                                message: data,
                                type: "danger"
                            });
                        }
                    })
                    .catch(err => {
                        console.error("Error:", err);
                        Popup.open({
                            title: "System Error",
                            message: "Could not connect to the server.",
                            type: "danger"
                        });
                    })
                    .finally(() => {
                        submitBtn.innerHTML = "Update Account";
                        submitBtn.disabled = false;
                    });
                }
            });
        });
    }

    // =========================================================
    // ADD ACCOUNT MODAL
    // =========================================================
    const addAccountModal   = document.getElementById("addAccountModal");
    const openAddAccountBtn = document.getElementById("openAddAccountBtn");
    const closeAddAccountBtn = document.getElementById("closeAddAccountBtn");
    const cancelAddAccountBtn = document.getElementById("cancelAddAccountBtn");
    const addAccountForm    = document.getElementById("addAccountForm");

    function resetAddModal() {
        closeModalEl(addAccountModal, addAccountForm);
        // Reset password visibility
        const addPass = document.getElementById("addPassword");
        const addConf = document.getElementById("addConfirmPassword");
        const iconPass = document.querySelector("#toggleAddPassword i");
        const iconConf = document.querySelector("#toggleAddConfirmPassword i");
        if (addPass) addPass.setAttribute("type", "password");
        if (addConf) addConf.setAttribute("type", "password");
        if (iconPass) { iconPass.classList.remove("fa-eye-slash"); iconPass.classList.add("fa-eye"); }
        if (iconConf) { iconConf.classList.remove("fa-eye-slash"); iconConf.classList.add("fa-eye"); }
        // Reset submit button
        const submitBtn = addAccountForm ? addAccountForm.querySelector('button[type="submit"]') : null;
        if (submitBtn) { submitBtn.innerHTML = "Save Account"; submitBtn.disabled = false; }
    }

    if (openAddAccountBtn) {
        openAddAccountBtn.addEventListener("click", () => {
            if (addAccountForm) addAccountForm.reset();
            openModal(addAccountModal);
        });
    }
    if (closeAddAccountBtn) closeAddAccountBtn.addEventListener("click", resetAddModal);
    if (cancelAddAccountBtn) cancelAddAccountBtn.addEventListener("click", resetAddModal);

    // Password toggle — Add modal
    const toggleAddPassword = document.getElementById("toggleAddPassword");
    const addPassword = document.getElementById("addPassword");
    if (toggleAddPassword && addPassword) {
        toggleAddPassword.addEventListener("click", () => {
            const type = addPassword.getAttribute("type") === "password" ? "text" : "password";
            addPassword.setAttribute("type", type);
            const icon = toggleAddPassword.querySelector("i");
            icon.classList.toggle("fa-eye", type === "password");
            icon.classList.toggle("fa-eye-slash", type === "text");
        });
    }

    const toggleAddConfirmPassword = document.getElementById("toggleAddConfirmPassword");
    const addConfirmPassword = document.getElementById("addConfirmPassword");
    if (toggleAddConfirmPassword && addConfirmPassword) {
        toggleAddConfirmPassword.addEventListener("click", () => {
            const type = addConfirmPassword.getAttribute("type") === "password" ? "text" : "password";
            addConfirmPassword.setAttribute("type", type);
            const icon = toggleAddConfirmPassword.querySelector("i");
            icon.classList.toggle("fa-eye", type === "password");
            icon.classList.toggle("fa-eye-slash", type === "text");
        });
    }

    // Add form submit
    if (addAccountForm) {
        addAccountForm.addEventListener("submit", (e) => {
            e.preventDefault();

            const pass        = addAccountForm.querySelector('input[name="password"]').value;
            const confirmPass = addAccountForm.querySelector('input[name="confirm_password"]').value;

            if (pass !== confirmPass) {
                Popup.open({
                    title: "Error",
                    message: "Passwords do not match. Please try again.",
                    type: "danger"
                });
                return;
            }

            const firstName = addAccountForm.querySelector('input[name="first_name"]').value;
            const lastName  = addAccountForm.querySelector('input[name="last_name"]').value;
            const username  = addAccountForm.querySelector('input[name="username"]').value;
            const role      = addAccountForm.querySelector('select[name="role"]').value;

            Popup.open({
                title: "Confirm Account Creation",
                message: `Are you sure you want to create this account?\n\nName: ${firstName} ${lastName}\nUsername: ${username}\nRole: ${role}`,
                type: "warning",
                onOk: () => {
                    const submitBtn = addAccountForm.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = "Saving...";
                    submitBtn.disabled = true;

                    const formData = new FormData(addAccountForm);

                    fetch("../api/add_account_process.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            Popup.open({
                                title: "Success!",
                                message: "The new account has been successfully created.",
                                type: "success",
                                onOk: () => { location.reload(); }
                            });
                        } else {
                            Popup.open({
                                title: "Error",
                                message: data.message || "Failed to create account.",
                                type: "danger"
                            });
                        }
                    })
                    .catch(err => {
                        console.error("Error:", err);
                        Popup.open({
                            title: "System Error",
                            message: "Could not connect to the server.",
                            type: "danger"
                        });
                    })
                    .finally(() => {
                        submitBtn.innerHTML = "Save Account";
                        submitBtn.disabled = false;
                    });
                }
            });
        });
    }

};

// Call immediately if DOM is ready, otherwise wait
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initAccountMan);
} else {
    window.initAccountMan();
}