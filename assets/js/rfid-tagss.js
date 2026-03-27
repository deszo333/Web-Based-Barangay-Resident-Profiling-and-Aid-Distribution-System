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
        rfidId.value = ""; // clear hidden id
        submitBtn.innerText = "Issue Tag"; // Only update submit button text
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

        fetch("add_rfid_tags.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            data = data.trim();
            if (data === "success") {
                alert("RFID Tag saved successfully!");
                closeModal();
                form.reset();
                location.reload();
            } else if (data === "conflict") {
                // === OCC CONFLICT HANDLER ===
                alert("Update Conflict! Another staff member modified this RFID tag. Please refresh the page and try again.");
            } else if (data === "rfid_exists") {
                alert("RFID already exists!");
            } else if (data === "missing") {
                alert("Please fill in required fields");
            } else {
                console.log(data);
                alert("Failed to save RFID tag");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Server error");
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

        submitBtn.innerText = "Update Tag"; // Only update submit button
        modal.classList.add("show");
        overlay.classList.add("show");
    });

    // DELETE BUTTON
    document.addEventListener("click", function (e) {
        const deleteBtn = e.target.closest(".delete");
        if (!deleteBtn) return;

        const rfidId = deleteBtn.dataset.id;
        if (!rfidId) return;

        if (!confirm("Are you sure you want to delete this RFID tag?")) return;

        fetch("delete_rfid_tag.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `rfid_id=${encodeURIComponent(rfidId)}`
        })
        .then(res => res.text())
        .then(data => {
            data = data.trim();
            if (data === "success") {
                const row = deleteBtn.closest("tr");
                if (row) row.remove();
                alert("RFID tag deleted successfully!");
            } else {
                alert("Failed to delete RFID tag: " + data);
            }
        })
        .catch(err => {
            console.error(err);
            alert("Server error");
        });
    });

    // ACTIVATE / DEACTIVATE BUTTONS
    document.addEventListener("click", function(e) {
        const activateBtn = e.target.closest(".activate-btn");
        const deactivateBtn = e.target.closest(".deactivate-btn");

        const btn = activateBtn || deactivateBtn;
        if (!btn) return;

        const rfidId = btn.dataset.id;
        if (!rfidId) return;

        const action = activateBtn ? "Active" : "Disabled";

        fetch("toggle_rfid_status.php", {
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
            } else {
                alert("Failed to update status: " + data);
            }
        })
        .catch(err => {
            console.error(err);
            alert("Server error");
        });
    });
});