document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("residentModal");
    const overlay = document.getElementById("modalOverlay");
    const closeBtn = document.querySelector(".close-btn");
    const form = document.getElementById("addResidentForm");
    const programId = document.getElementById("program_id");

    // Open modal for adding new program
    const addBtn = document.querySelector(".add-resident");
    if (addBtn) {
        addBtn.addEventListener("click", () => {
            form.reset();
            programId.value = "";
            form.querySelector("button").innerText = "Save Program";
            modal.classList.add("show");
            overlay.classList.add("show");
        });
    }

    // Close modal
    function closeModal() {
        modal.classList.remove("show");
        overlay.classList.remove("show");
    }
    closeBtn.addEventListener("click", closeModal);
    overlay.addEventListener("click", closeModal);

    // Open modal for editing
    document.addEventListener("click", function (e) {
        const editBtn = e.target.closest(".edit");
        if (!editBtn) return;

        programId.value = editBtn.dataset.id || "";
        if(form.version) form.version.value = editBtn.dataset.version || ""; // Map version for OCC
        form.program_name.value = editBtn.dataset.name || "";
        form.aid_type.value = editBtn.dataset.type || "";
        form.date_scheduled.value = editBtn.dataset.date || "";
        form.beneficiaries.value = editBtn.dataset.beneficiaries || "";
        form.status.value = editBtn.dataset.status || "";

        form.querySelector("button").innerText = "Update Program";

        modal.classList.add("show");
        overlay.classList.add("show");
    });

    // Submit form (Add or Update)
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const isUpdate = !!programId.value;

        Popup.open({
            title: isUpdate ? "Confirm Update" : "Confirm Add",
            message: isUpdate
                ? "Are you sure you want to update this aid program?"
                : "Are you sure you want to add this aid program?",
            type: "warning",
            onOk: () => {

                const formData = new FormData(form);

                fetch("add_aid_program.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    data = data.trim();

                    if (data === "success") {
                        closeModal();

                        Popup.open({
                            title: "Success",
                            message: isUpdate
                                ? "Aid program updated successfully."
                                : "Aid program added successfully.",
                            type: "success",
                            onOk: () => location.reload()
                        });
                    } else if (data === "conflict") {
                        // === OCC CONFLICT HANDLER ===
                        closeModal();
                        Popup.open({
                            title: "Update Conflict",
                            message: "Another staff member updated this aid program while you were viewing it. Please refresh and try again.",
                            type: "danger"
                        });
                    } else {
                        Popup.open({
                            title: "Error",
                            message: "Error: " + data,
                            type: "danger"
                        });
                    }
                })
                .catch(() => {
                    Popup.open({
                        title: "Server Error",
                        message: "Something went wrong while saving the program.",
                        type: "danger"
                    });
                });

            }
        });
    });

    // DELETE FUNCTION
    document.addEventListener("click", function(e) {
        const deleteBtn = e.target.closest(".delete");
        if (!deleteBtn) return;

        const id = deleteBtn.dataset.id;

        Popup.open({
            title: "Confirm Delete",
            message: "Are you sure you want to delete this record?",
            type: "warning",
            onOk: () => {
                fetch("delete_aid_program.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "id=" + encodeURIComponent(id)
                })
                .then(res => res.text())
                .then(data => {
                    data = data.trim();
                    if (data === "success") {
                        deleteBtn.closest("tr")?.remove();
                    } else {
                        Popup.open({
                            title: "Delete Failed",
                            message: "Failed to delete: " + data,
                            type: "danger"
                        });
                    }
                })
                .catch(() => {
                    Popup.open({
                        title: "Server Error",
                        message: "Unable to delete the record.",
                        type: "danger"
                    });
                });
            }
        });
    });

});
