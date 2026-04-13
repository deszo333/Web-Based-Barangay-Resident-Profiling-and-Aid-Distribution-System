window.initAccountMan = function() {
    // FIX: Guard to prevent double-binding listeners!
    if (window.amInitialized) return;
    window.amInitialized = true;
    
    document.addEventListener("click", function (e) {
        // Handle Edit button
        const editBtn = e.target.closest(".edit");
        if (editBtn) {
            e.preventDefault();
            const userId = editBtn.getAttribute("data-id");
            const fullName = editBtn.getAttribute("data-name");
            const username = editBtn.getAttribute("data-username");
            const role = editBtn.getAttribute("data-role");
            
            // Parse full name into first and last name
            const nameParts = fullName.split(' ');
            const firstName = nameParts[0];
            const lastName = nameParts.slice(1).join(' ');
            
            // Populate form
            document.getElementById("editUserId").value = userId;
            document.getElementById("editFirstName").value = firstName;
            document.getElementById("editLastName").value = lastName;
            document.getElementById("editUsername").value = username;
            document.getElementById("editRole").value = role;
            
            // Open modal
            document.getElementById("editAccountModal").style.display = "flex";
            return;
        }

        const btn = e.target.closest(".deactivate") || e.target.closest(".activate");
        
        if (btn) {
            e.preventDefault(); 
            
            const id = btn.getAttribute("data-id");
            const isDeactivating = btn.classList.contains("deactivate");
            const actionText = isDeactivating ? "Deactivate" : "Activate";

            // Ask for confirmation
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
                                onOk: () => {
                                    location.reload(); 
                                }
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

    // --- EDIT ACCOUNT MODAL LOGIC ---
    const editAccountModal = document.getElementById("editAccountModal");
    const closeEditAccountBtn = document.getElementById("closeEditAccountBtn");
    const editAccountForm = document.getElementById("editAccountForm");

    if (closeEditAccountBtn) {
        closeEditAccountBtn.addEventListener("click", () => {
            editAccountModal.style.display = "none";
            editAccountForm.reset();
        });
    }

    if (editAccountForm) {
        editAccountForm.addEventListener("submit", (e) => {
            e.preventDefault();
            
            const userId = document.getElementById("editUserId").value;
            const firstName = document.getElementById("editFirstName").value;
            const lastName = document.getElementById("editLastName").value;
            const username = document.getElementById("editUsername").value;
            const role = document.getElementById("editRole").value;

            // Ask for confirmation before updating account
            Popup.open({
                title: "Confirm Account Update",
                message: `Are you sure you want to update this account?\n\nName: ${firstName} ${lastName}\nUsername: ${username}\nRole: ${role}`,
                type: "warning",
                onOk: () => {
                    // User confirmed - proceed with account update
                    const submitBtn = editAccountForm.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = "Updating...";
                    submitBtn.disabled = true;

                    const formData = new FormData(editAccountForm);

                    // Send to API
                    fetch("../api/update_account_process.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            Popup.open({
                                title: "Success!",
                                message: "The account has been successfully updated.",
                                type: "success",
                                onOk: () => { location.reload(); }
                            });
                        } else {
                            Popup.open({
                                title: "Error",
                                message: data.message || "Failed to update account.",
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

    // --- ADD ACCOUNT MODAL LOGIC ---
    const addAccountModal = document.getElementById("addAccountModal");
    const openAddAccountBtn = document.getElementById("openAddAccountBtn");
    const closeAddAccountBtn = document.getElementById("closeAddAccountBtn");
    const addAccountForm = document.getElementById("addAccountForm");

    // Open/Close Modal
    if (openAddAccountBtn) {
        openAddAccountBtn.addEventListener("click", () => {
            addAccountModal.style.display = "flex";
        });
    }

    if (closeAddAccountBtn) {
        closeAddAccountBtn.addEventListener("click", () => {
            addAccountModal.style.display = "none";
            addAccountForm.reset();
            // Reset form state
            const submitBtn = addAccountForm.querySelector('button[type="submit"]');
            submitBtn.innerHTML = "Save Account";
            submitBtn.disabled = false;
            // Reset icons back to closed eyes
            document.getElementById("addPassword").setAttribute("type", "password");
            document.getElementById("toggleAddPassword").classList.remove("fa-eye-slash");
            document.getElementById("toggleAddPassword").classList.add("fa-eye");
            document.getElementById("addConfirmPassword").setAttribute("type", "password");
            document.getElementById("toggleAddConfirmPassword").classList.remove("fa-eye-slash");
            document.getElementById("toggleAddConfirmPassword").classList.add("fa-eye");
        });
    }

    // Toggle Password Visibility Logic
    const toggleAddPassword = document.getElementById("toggleAddPassword");
    const addPassword = document.getElementById("addPassword");
    if (toggleAddPassword && addPassword) {
        toggleAddPassword.addEventListener("click", () => {
            const type = addPassword.getAttribute("type") === "password" ? "text" : "password";
            addPassword.setAttribute("type", type);
            const icon = toggleAddPassword.querySelector("i");
            if (type === "text") {
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });
    }

    const toggleAddConfirmPassword = document.getElementById("toggleAddConfirmPassword");
    const addConfirmPassword = document.getElementById("addConfirmPassword");
    if (toggleAddConfirmPassword && addConfirmPassword) {
        toggleAddConfirmPassword.addEventListener("click", () => {
            const type = addConfirmPassword.getAttribute("type") === "password" ? "text" : "password";
            addConfirmPassword.setAttribute("type", type);
            const icon = toggleAddConfirmPassword.querySelector("i");
            if (type === "text") {
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });
    }

    // Form Submission
    if (addAccountForm) {
        addAccountForm.addEventListener("submit", (e) => {
            e.preventDefault();
            
            // 1. Confirm Passwords Match
            const pass = addAccountForm.querySelector('input[name="password"]').value;
            const confirmPass = addAccountForm.querySelector('input[name="confirm_password"]').value;

            if (pass !== confirmPass) {
                Popup.open({
                    title: "Error",
                    message: "Passwords do not match. Please try again.",
                    type: "danger"
                });
                return; // Stop the submission!
            }

            // 2. Get form data for confirmation
            const firstName = addAccountForm.querySelector('input[name="first_name"]').value;
            const lastName = addAccountForm.querySelector('input[name="last_name"]').value;
            const username = addAccountForm.querySelector('input[name="username"]').value;
            const role = addAccountForm.querySelector('select[name="role"]').value;

            // 3. Ask for confirmation before creating account
            Popup.open({
                title: "Confirm Account Creation",
                message: `Are you sure you want to create this account?\n\nName: ${firstName} ${lastName}\nUsername: ${username}\nRole: ${role}`,
                type: "warning",
                onOk: () => {
                    // User confirmed - proceed with account creation
                    const submitBtn = addAccountForm.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = "Saving...";
                    submitBtn.disabled = true;

                    const formData = new FormData(addAccountForm);

                    // 4. Send to API
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
                        // FIX: Hardcode reset text so it never gets stuck on "Saving..."
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