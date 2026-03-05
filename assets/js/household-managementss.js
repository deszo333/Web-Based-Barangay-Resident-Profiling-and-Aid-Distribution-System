document.addEventListener("DOMContentLoaded", () => {

    /* =========================
       DOM ELEMENTS
    ========================= */
    const addBtn = document.querySelector('.add-household');
    const modal = document.getElementById('residentModal');
    const overlay = document.getElementById('modalOverlay');
    const closeBtn = document.getElementById('closeModal');
    const form = document.getElementById('addResidentForm');

    const householdId = document.getElementById('resident_id');
    const headInput = document.getElementById("headInput");
    const membersInput = document.getElementById("membersInput");
    const addressInput = document.querySelector("input[name='address']");
    const residentPicker = document.getElementById("residentPicker");
    const memberSearch = document.getElementById("memberSearch");

    const modalTitle = document.getElementById("modalTitle");
    const modalIcon = document.getElementById("modalIcon");
    const saveBtn = document.getElementById("saveHouseholdBtn");

    const membersActions = document.getElementById("membersActions");

    const membersOverlay = document.getElementById("membersOverlay");
    const membersBody = document.getElementById("membersBody");
    const closeMembersOverlay = document.getElementById("closeMembersOverlay");

    const scanBtn = document.getElementById("scanRfidBtn");
    const rfidOverlay = document.getElementById("rfidOverlay");
    const cancelRfid = document.getElementById("cancelRfid");
    const rfidInput = document.getElementById("rfidInput");

    /* =========================
       STATE VARIABLES
    ========================= */
    let pickerMode = null; // "head" | "members"
    let dragging = false, offsetX = 0, offsetY = 0;
    let rfidBuffer = "";
    let scanning = false;

    /* =========================
       MODAL FUNCTIONS
    ========================= */
    function openModal() {
        modal.classList.add('show');
        overlay.classList.add('show');
    }

    function closeModal() {
        modal.classList.remove('show');
        overlay.classList.remove('show');
        togglePicker(false);
    }

    /* =========================
       PICKER FUNCTIONS
    ========================= */
    function togglePicker(show) {
        if (show) {
            residentPicker.classList.add("show");
            updatePickerButtons();
        } else {
            residentPicker.classList.remove("show");
            pickerMode = null;
        }
    }

    function updatePickerButtons() {
        document.querySelectorAll(".picker-action").forEach(btn => {
            btn.innerText = pickerMode === "members" ? "Add" : "Select";
        });
    }

    /* =========================
       MEMBER INPUT HELPERS
    ========================= */
    function addMemberToInput(name) {
        const current = membersInput.value
            .split(',')
            .map(m => m.trim())
            .filter(Boolean);

        if (!current.includes(name)) {
            current.push(name);
            membersInput.value = current.join(', ');
            renderMembersActions();
        }
    }

    function removeMemberFromInput(name) {
        const current = membersInput.value
            .split(',')
            .map(m => m.trim())
            .filter(m => m && m !== name);

        membersInput.value = current.join(', ');
        renderMembersActions();
    }

    function renderMembersActions() {
        if (!membersActions) return;
        membersActions.innerHTML = "";

        const members = membersInput.value
            .split(',')
            .map(m => m.trim())
            .filter(Boolean);

        members.forEach(name => {
            const tag = document.createElement("span");
            tag.className = "member-tag";
            tag.innerHTML = `${name} ✕`;
            tag.onclick = () => removeMemberFromInput(name);
            membersActions.appendChild(tag);
        });

        membersInput.classList.toggle("expanded", members.length > 0);
    }

    /* =========================
       OPEN PICKER ON FOCUS
    ========================= */
    headInput.addEventListener("focus", () => {
        pickerMode = "head";
        togglePicker(true);
    });

    membersInput.addEventListener("focus", () => {
        pickerMode = "members";
        togglePicker(true);
    });

    /* =========================
       PICKER SELECTION
    ========================= */
    document.addEventListener("click", (e) => {

        // MEMBER COUNT OVERLAY
        if (e.target.classList.contains("member-count")) {
    const members = (e.target.dataset.members || "")
        .split(",")
        .map(m => m.trim())
        .filter(Boolean);

    membersBody.innerHTML = members.length
        ? "<ul>" + members.map(m => `<li>${m}</li>`).join("") + "</ul>"
        : "<p><i>No members found</i></p>";

    membersOverlay.classList.add("show");
}

        // CLOSE MEMBER OVERLAY
        if (e.target.id === "closeMembersOverlay" || e.target.id === "membersOverlay") {
            membersOverlay.classList.remove("show");
        }

        // PICKER ACTION
        if (e.target.classList.contains("picker-action")) {
            const name = e.target.dataset.name;
            const address = e.target.dataset.address;

            if (pickerMode === "head") {
                headInput.value = name;
                addressInput.value = address;
                togglePicker(false);
            } else if (pickerMode === "members") {
                addMemberToInput(name);
            }
        }

        // CLICK OUTSIDE PICKER
        if (!e.target.closest("#residentPicker") &&
            e.target !== headInput &&
            e.target !== membersInput) {
            togglePicker(false);
        }

        // EDIT HOUSEHOLD
        const editBtn = e.target.closest('.edit');
        if (editBtn) {
            e.stopPropagation();
            householdId.value = editBtn.dataset.id || "";
            form.household_number.value = editBtn.dataset.number || "";
            headInput.value = editBtn.dataset.head || "";
            addressInput.value = editBtn.dataset.address || "";
            membersInput.value = editBtn.dataset.members || "";
            renderMembersActions();
            form.rfid.value = editBtn.dataset.rfid || "";

            saveBtn.innerText = "Update Household";
            modalTitle.innerText = "Edit Household";
            modalIcon.className = "fa-solid fa-pen-to-square";

            openModal();
            return;
        }

        // DELETE HOUSEHOLD
        const deleteBtn = e.target.closest('.delete');
        if (deleteBtn) {
            e.stopPropagation();
            const id = deleteBtn.dataset.id;
            if (!id) return;

            if (typeof Popup !== "undefined") {
                Popup.open({
                    title: "Confirm Delete",
                    message: "Are you sure you want to deleted this household? This action cannot be undone.",
                    type: "danger",
                    onOk: () => deleteHousehold(id, deleteBtn)
                });
            } else {
                if (confirm("Are you sure you want to delete this household?")) {
                    deleteHousehold(id, deleteBtn);
                }
            }
            return;
        }

    });

    // CLOSE MEMBERS OVERLAY BUTTON
    closeMembersOverlay.addEventListener("click", () => {
        membersOverlay.classList.remove("show");
    });

    membersOverlay.addEventListener("click", (e) => {
        if (e.target === membersOverlay) {
            membersOverlay.classList.remove("show");
        }
    });

    /* =========================
       DRAG PICKER
    ========================= */
    residentPicker.addEventListener("mousedown", (e) => {
        dragging = true;
        offsetX = e.clientX - residentPicker.offsetLeft;
        offsetY = e.clientY - residentPicker.offsetTop;
    });

    document.addEventListener("mousemove", (e) => {
        if (!dragging) return;
        residentPicker.style.left = (e.clientX - offsetX) + "px";
        residentPicker.style.top = (e.clientY - offsetY) + "px";
    });

    document.addEventListener("mouseup", () => dragging = false);

    /* =========================
       ADD HOUSEHOLD BUTTON
    ========================= */
    addBtn.addEventListener('click', () => {
        form.reset();
        householdId.value = "";
        membersActions.innerHTML = "";
        membersInput.classList.remove("expanded");

        saveBtn.innerText = "Save Household";
        modalTitle.innerText = "Add New Household";
        modalIcon.className = "fa-solid fa-house";

        fetch('get_next_household_number.php')
            .then(res => res.text())
            .then(num => form.household_number.value = num);

        openModal();
    });

    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    /* =========================
       FORM SUBMISSION
    ========================= */
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        form.household_members.value = membersInput.value
            .split(',')
            .map(m => m.trim())
            .filter(Boolean)
            .join(', ');

        fetch('add_household.php', {
            method: 'POST',
            body: new FormData(form)
        })
            .then(res => res.text())
            .then(data => {
                if (data.trim() === 'success') {
                    location.reload();
                } else {
                    alert("Failed to save: " + data);
                }
            });
    });

    /* =========================
       DELETE FUNCTION
    ========================= */
    function deleteHousehold(id, btn) {
        fetch('delete_household.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${encodeURIComponent(id)}`
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === 'success') {
                btn.closest('tr')?.remove();
            } else {
                alert("Failed to delete: " + data);
            }
        })
        .catch(err => alert("Server error: " + err));
    }

    /* =========================
       MEMBER SEARCH FILTER
    ========================= */
    memberSearch.addEventListener("keyup", function () {
        const filter = this.value.toLowerCase();
        document.querySelectorAll("#residentPicker tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });

    /* =========================
       RFID SCANNER
    ========================= */
    scanBtn.addEventListener("click", () => {
        rfidOverlay.classList.add("show");
        rfidBuffer = "";
        scanning = true;
    });

    cancelRfid.addEventListener("click", () => {
        rfidOverlay.classList.remove("show");
        scanning = false;
        rfidBuffer = "";
    });

    document.addEventListener("keydown", (e) => {
        if (!scanning) return;

        if (e.key === "Enter") {
            rfidInput.value = rfidBuffer;
            rfidOverlay.classList.remove("show");
            scanning = false;
            rfidBuffer = "";
        } else if (e.key.length === 1) {
            rfidBuffer += e.key;
        }
    });

});
