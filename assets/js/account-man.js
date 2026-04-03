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

});