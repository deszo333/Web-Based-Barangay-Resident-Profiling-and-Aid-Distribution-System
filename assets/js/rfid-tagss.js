document.addEventListener("DOMContentLoaded", () => {
    const openBtn = document.querySelector(".add-tag");
    const modal = document.getElementById("residentModal");
    const overlay = document.getElementById("modalOverlay");
    const closeBtn = document.getElementById("closeModal");
    const form = document.getElementById("addResidentForm");
    const submitBtn = document.getElementById("submitBtn"); // Explicit submit button

    const rfidId = document.getElementById("rfid_id");
    const rfidNumber = document.getElementById("rfid_number");
    const householdSelect = document.getElementById("household_id");
    const scanBtn = document.getElementById("scanRfidBtn");
    const rfidOverlay = document.getElementById("rfidOverlay");
    const cancelRfid = document.getElementById("cancelRfid");
    const rfidInput = document.getElementById("rfid_number");

    if (!openBtn || !modal || !overlay || !closeBtn || !form || !rfidId || !submitBtn) {
        console.error("Modal elements missing");
        return;
    }

    // HIDE modal and overlay on page load
    modal.classList.remove("show");
    overlay.classList.remove("show");
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
    closeBtn.addEventListener("click", closeModal);
    overlay.addEventListener("click", closeModal);
    function closeModal() {
        modal.classList.remove("show");
        overlay.classList.remove("show");
    }

    let rfidBuffer = "";
    let scanning = false;

    // SUBMIT FORM (Add or Update)
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(form);

        fetch("../api/add_rfid_tags.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            data = data.trim();
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
                    message: "This household already has an Active RFID tag assigned to it.<br><br>Please deactivate their old tag before issuing a new one to prevent system conflicts.",
                    type: "warning"
                });
            } else if (data === "conflict") {
                Popup.open({ title: "Update Conflict", message: "Another staff member modified this RFID tag. Please refresh the page and try again.", type: "danger" });
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
        });
    });

    // EDIT BUTTON
    document.addEventListener("click", function (e) {
        const editBtn = e.target.closest(".edit");
        if (!editBtn) return;

        rfidId.value = editBtn.dataset.id || "";
        if(form.version) form.version.value = editBtn.dataset.version || ""; // Map version for OCC
        rfidNumber.value = editBtn.dataset.rfid || "";
        if(householdSelect) householdSelect.value = editBtn.dataset.householdid || "";

        submitBtn.innerText = "Update Tag";
        const modalTitleEl = document.getElementById("modalTitle");
        if (modalTitleEl) modalTitleEl.innerText = "Edit RFID Tag";
        modal.classList.add("show");
        overlay.classList.add("show");
    });

    // ACTIVATE / DEACTIVATE BUTTONS
    document.addEventListener("click", function(e) {
        const activateBtn = e.target.closest(".activate-btn");
        const deactivateBtn = e.target.closest(".deactivate-btn");

        const btn = activateBtn || deactivateBtn;
        if (!btn) return;

        const rfidId = btn.dataset.id;
        if (!rfidId) return;

        const action = activateBtn ? "Active" : "Inactive";
        const actionLabel = activateBtn ? "activate" : "deactivate";

        Popup.open({
            title: `Confirm ${activateBtn ? "Activation" : "Deactivation"}`,
            message: `Are you sure you want to ${actionLabel} this RFID tag?`,
            type: activateBtn ? "info" : "warning",
            onOk: () => {
                fetch("../api/toggle_rfid_status.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `rfid_id=${encodeURIComponent(rfidId)}&status=${encodeURIComponent(action)}`
                })
                .then(res => res.text())
                .then(data => {
                    data = data.trim();
                    if (data === "success") {
                        const row = btn.closest("tr");
                        const statusSpan = row.querySelector(".status");
                        const toggleCell = btn.closest("td");

                        if (statusSpan) statusSpan.textContent = action;
                        if (statusSpan) statusSpan.className = "status " + (action === "Active" ? "active" : "inactive");
                        
                        if (activateBtn) {
                            toggleCell.innerHTML = `<button class="deactivate-btn" data-id="${rfidId}">Deactivate</button>`;
                        } else {
                            toggleCell.innerHTML = `<button class="activate-btn" data-id="${rfidId}">Activate</button>`;
                        }

                        Popup.open({
                            title: "Status Updated",
                            message: `RFID tag has been marked as <b>${action}</b>.`,
                            type: "success"
                        });

                    } else if (data === "has_active") {
                        Popup.open({
                            title: "Activation Blocked",
                            message: "This household already has an <b>Active</b> tag in the system.<br><br>To prevent duplicate tags, you must find their current active tag and deactivate it before you can reactivate this old one.",
                            type: "warning"
                        });
                    } else {
                        Popup.open({ title: "Update Failed", message: "Failed to update status: " + data, type: "danger" });
                    }
                })
                .catch(err => {
                    Popup.open({ title: "Server Error", message: "A network error occurred.", type: "danger" });
                });
            }
        });
    });
            }
        })
        .catch(err => {
            Popup.open({ title: "Server Error", message: "A network error occurred.", type: "danger" });
        });
    });
});