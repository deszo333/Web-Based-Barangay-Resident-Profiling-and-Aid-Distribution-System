document.addEventListener("DOMContentLoaded", () => {
    
    document.addEventListener("click", function (e) {
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
                type: isDeactivating ? "warning" : "info",
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
            // Reset icons back to closed eyes
            document.getElementById("addPassword").setAttribute("type", "password");
            document.getElementById("toggleAddPassword").classList.replace("fa-eye-slash", "fa-eye");
            document.getElementById("addConfirmPassword").setAttribute("type", "password");
            document.getElementById("toggleAddConfirmPassword").classList.replace("fa-eye-slash", "fa-eye");
        });
    }

    // Toggle Password Visibility Logic
    const toggleAddPassword = document.getElementById("toggleAddPassword");
    const addPassword = document.getElementById("addPassword");
    if (toggleAddPassword && addPassword) {
        toggleAddPassword.addEventListener("click", () => {
            const type = addPassword.getAttribute("type") === "password" ? "text" : "password";
            addPassword.setAttribute("type", type);
            toggleAddPassword.classList.toggle("fa-eye-slash");
        });
    }

    const toggleAddConfirmPassword = document.getElementById("toggleAddConfirmPassword");
    const addConfirmPassword = document.getElementById("addConfirmPassword");
    if (toggleAddConfirmPassword && addConfirmPassword) {
        toggleAddConfirmPassword.addEventListener("click", () => {
            const type = addConfirmPassword.getAttribute("type") === "password" ? "text" : "password";
            addConfirmPassword.setAttribute("type", type);
            toggleAddConfirmPassword.classList.toggle("fa-eye-slash");
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

            // 2. Visual feedback
            const submitBtn = addAccountForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = "Saving...";
            submitBtn.disabled = true;

            const formData = new FormData(addAccountForm);

            // 3. Send to API
            fetch("../api/add_account_process.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;

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
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                Popup.open({
                    title: "System Error",
                    message: "Could not connect to the server.",
                    type: "danger"
                });
            });
        });
    }

});