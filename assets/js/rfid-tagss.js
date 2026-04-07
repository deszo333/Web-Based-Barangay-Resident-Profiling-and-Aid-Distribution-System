window.initRfidTags = function() {
    // FIX 1: Guard to prevent double-binding event listeners
    if (window.rfidInitialized) return;
    window.rfidInitialized = true;

    const openBtn = document.querySelector(".add-tag");
    const modal = document.getElementById("residentModal");
    const overlay = document.getElementById("modalOverlay");
    const closeBtn = document.getElementById("closeModal");
    const form = document.getElementById("addResidentForm");
    const submitBtn = document.getElementById("submitBtn");

    const rfidId = document.getElementById("rfid_id");
    const rfidNumber = document.getElementById("rfid_number");
    const householdSelect = document.getElementById("household_id");

    if (!openBtn || !modal || !overlay || !closeBtn || !form || !rfidId || !submitBtn) {
        console.error("Modal elements missing");
        return;
    }

    modal.classList.remove("show");
    overlay.classList.remove("show");

    const rfidOverlay = document.getElementById("rfidOverlay");
    if (rfidOverlay) rfidOverlay.classList.remove("show");

    // OPEN MODAL FOR ADD
    openBtn.addEventListener("click", () => {
        form.reset();
        rfidId.value = "";
        submitBtn.innerText = "Issue Tag";
        const modalTitleEl = document.getElementById("modalTitle");
        if (modalTitleEl) modalTitleEl.innerText = "Issue RFID Tag";
        modal.classList.add("show");
        overlay.classList.add("show");
    });

    // CLOSE MODAL
    function closeModal() {
        modal.classList.remove("show");
        overlay.classList.remove("show");
    }
    closeBtn.addEventListener("click", closeModal);
    overlay.addEventListener("click", closeModal);

    // SUBMIT FORM (Add or Update)
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        
        // FIX 2: Lock the submit button to prevent double-clicks
        const originalText = submitBtn.innerText;
        submitBtn.disabled = true;
        submitBtn.innerText = "Saving...";

        const formData = new FormData(form);

        fetch("../api/add_rfid_tags.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(text => {
            let data = text.trim();
            
            // Extract the status if the backend sends JSON debug logs
            try {
                if (text.startsWith("{")) {
                    const parsed = JSON.parse(text);
                    data = parsed.status || data; 
                }
            } catch(e) {}

            if (data === "success") {
                Popup.open({
                    title: "Success",
                    message: "RFID Tag saved successfully!",
                    type: "success",
                    onOk: () => { location.reload(); }
                });
            } else if (data === "has_active") {
                Popup.open({
                    title: "Active Tag Exists",
                    message: "This household already has an Active RFID tag assigned to it.<br><br>Please deactivate their old tag before issuing a new one.",
                    type: "warning"
                });
            } else if (data === "conflict") {
                Popup.open({ title: "Update Conflict", message: "Another staff member modified this RFID tag. Please refresh and try again.", type: "danger" });
            } else if (data === "rfid_exists") {
                Popup.open({ title: "Duplicate RFID", message: "This RFID number is already registered in the system.", type: "warning" });
            } else if (data === "missing") {
                Popup.open({ title: "Incomplete Data", message: "Please fill in all required fields.", type: "warning" });
            } else {
                Popup.open({ title: "Save Failed", message: "Failed to save RFID tag: " + data, type: "danger" });
            }
        })
        .catch(err => {
            console.error(err);
            Popup.open({ title: "Server Error", message: "A network error occurred.", type: "danger" });
        })
        .finally(() => {
            // Unlock the button if it fails so they can try again
            submitBtn.disabled = false;
            submitBtn.innerText = originalText;
        });
    });

    // EDIT BUTTON
    document.addEventListener("click", function (e) {
        const editBtn = e.target.closest(".edit");
        if (!editBtn) return;

        rfidId.value = editBtn.dataset.id || "";
        const versionField = document.getElementById("rfid_version");
        if (versionField) versionField.value = editBtn.dataset.version || "";
        rfidNumber.value = editBtn.dataset.rfid || "";
        if (householdSelect) householdSelect.value = editBtn.dataset.householdid || "";

        submitBtn.innerText = "Update Tag";
        const modalTitleEl = document.getElementById("modalTitle");
        if (modalTitleEl) modalTitleEl.innerText = "Edit RFID Tag";
        modal.classList.add("show");
        overlay.classList.add("show");
    });

    // ACTIVATE / DEACTIVATE BUTTONS — FIXED: now inside DOMContentLoaded
    document.addEventListener("click", function (e) {
        const activateBtn = e.target.closest(".activate-btn");
        const deactivateBtn = e.target.closest(".deactivate-btn");

        const btn = activateBtn || deactivateBtn;
        if (!btn) return;

        const id = btn.dataset.id;
        if (!id) return;

        const action = activateBtn ? "Active" : "Inactive";
        const actionLabel = activateBtn ? "activate" : "deactivate";

        Popup.open({
            title: `Confirm ${activateBtn ? "Activation" : "Deactivation"}`,
            message: `Are you sure you want to ${actionLabel} this RFID tag?`,
            type: "warning",
            onOk: () => {
                fetch("../api/toggle_rfid_status.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `rfid_id=${encodeURIComponent(id)}&status=${encodeURIComponent(action)}`
                })
                .then(res => res.text())
                .then(data => {
                    data = data.trim();
                    if (data === "success") {
                        const row = btn.closest("tr");
                        const statusSpan = row ? row.querySelector(".status") : null;
                        const toggleCell = btn.closest("td");

                        if (statusSpan) {
                            statusSpan.textContent = action;
                            statusSpan.className = "status " + (action === "Active" ? "active" : "inactive");
                        }
                        if (toggleCell) {
                            toggleCell.innerHTML = activateBtn
                                ? `<button class="deactivate-btn" data-id="${id}">Deactivate</button>`
                                : `<button class="activate-btn" data-id="${id}">Activate</button>`;
                        }

                        Popup.open({
                            title: "Status Updated",
                            message: `RFID tag has been marked as <b>${action}</b>.`,
                            type: "success"
                        });
                    } else if (data === "has_active") {
                        Popup.open({
                            title: "Activation Blocked",
                            message: "This household already has an <b>Active</b> tag. Deactivate it first.",
                            type: "warning"
                        });
                    } else {
                        Popup.open({ title: "Update Failed", message: "Failed to update status: " + data, type: "danger" });
                    }
                })
                .catch(() => {
                    Popup.open({ title: "Server Error", message: "A network error occurred.", type: "danger" });
                });
            }
        });
    });
};

// Call immediately if DOM is ready, otherwise wait
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initRfidTags);
} else {
    window.initRfidTags();
}