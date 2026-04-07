window.initHouseholdManagement = function() {

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
    const addressInput = document.querySelector("input[name='address']");
    const headIdInput = document.getElementById("headIdInput");
    const membersIdInput = document.getElementById("membersIdInput");
    
    const membersInput = document.getElementById("membersInput"); 
    const openMembersPickerBtn = document.getElementById("openMembersPickerBtn");
    const membersTableBody = document.getElementById("membersTableBody");

    const residentPicker = document.getElementById("residentPicker");

    const modalTitle = document.getElementById("modalTitle");
    const modalIcon = document.getElementById("modalIcon");
    const saveBtn = document.getElementById("saveHouseholdBtn");

    const membersOverlay = document.getElementById("membersOverlay");
    const membersBody = document.getElementById("membersBody");

    const scanBtn = document.getElementById("scanRfidBtn");
    const rfidOverlay = document.getElementById("rfidOverlay");
    const cancelRfid = document.getElementById("cancelRfid");
    const rfidInput = document.getElementById("rfidInput");

    /* =========================
   AJAX HOUSEHOLD SEARCH
========================= */

const searchInput = document.getElementById("searchHousehold");
const tableBody = document.getElementById("householdTableBody");

if (searchInput) {

    searchInput.addEventListener("input", function () {

        const searchValue = this.value;

        fetch("../api/search_household.php?search=" + encodeURIComponent(searchValue))
        .then(response => response.text())
        .then(data => {
            tableBody.innerHTML = data;
        })
        .catch(error => console.error("Search error:", error));

    });

}

    /* =========================
       APPEND AFTER PICK
    ========================= */
    if (residentPicker) {
        document.body.appendChild(residentPicker);
    }

    /* =========================
       STATE VARIABLES
    ========================= */
    let pickerMode = null; 
    let tempSelectedMembers = []; 
    let rfidBuffer = "";
    let scanning = false;

    /* =========================
       MODAL FUNCTIONS
    ========================= */
    function openModal() {
        if (modal) modal.classList.add('show');
        if (overlay) overlay.classList.add('show');
    }

    function closeModal() {
        if (modal) modal.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
        togglePicker(false);
    }

    /* =========================
       MEMBER TABLE HELPERS (MAIN FORM)
    ========================= */
    function removeMemberFromInput(idToRemove) {
        tempSelectedMembers = tempSelectedMembers.filter(m => m.id !== idToRemove);
        if (membersIdInput) membersIdInput.value = tempSelectedMembers.map(m => m.id).join(',');
        if (membersInput) membersInput.value = tempSelectedMembers.map(m => m.name).join(', ');
        renderMembersTable();
    }

    function renderMembersTable() {
        if (!membersTableBody) return;
        membersTableBody.innerHTML = "";

        if (tempSelectedMembers.length === 0) {
            membersTableBody.innerHTML = `<tr><td colspan="2" style="text-align: center; color: #94a3b8; font-style: italic;">No members added yet.</td></tr>`;
            return;
        }

        tempSelectedMembers.forEach(member => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${member.name}</td>
                <td style="text-align: center;">
                    <button type="button" class="remove-member-btn" data-id="${member.id}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            `;
            membersTableBody.appendChild(tr);
        });
    }

    /* =========================
       INITIALIZE PICKER UI
    ========================= */
    function initPickerUI() {
        if (!residentPicker) return;
        const pickerTable = residentPicker.querySelector("table");
        if (!pickerTable) return;
        
        if (document.querySelector(".picker-custom-header")) return;

        const firstTr = pickerTable.querySelector("thead tr:first-child");
        if (firstTr) firstTr.remove();

        const headerHTML = `
            <div class="picker-custom-header">
                <div class="picker-top-bar">
                    <div class="picker-title">
                        <i class="fa-solid fa-users"></i>
                        <h4 id="pickerDynamicTitle">Registered Residents</h4>
                    </div>
                    <button type="button" class="close-picker-btn">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <input type="text" id="memberSearch" placeholder="Search resident by name...">
            </div>
        `;
        pickerTable.insertAdjacentHTML("beforebegin", headerHTML);

        const footerHTML = `
            <div class="picker-custom-footer" id="pickerFooter" style="display:none;">
                <div class="picker-selected-count">
                    <span id="pickerCount">0</span> member(s) selected
                </div>
                <button type="button" class="picker-done-btn" id="pickerDoneBtn">Confirm Selection</button>
            </div>
        `;
        residentPicker.insertAdjacentHTML("beforeend", footerHTML);

        const wrapper = document.createElement('div');
        wrapper.className = 'picker-table-wrapper';
        pickerTable.parentNode.insertBefore(wrapper, pickerTable);
        wrapper.appendChild(pickerTable);

        // SMART SEARCH: Hides Head of Family dynamically during search
        document.getElementById("memberSearch")?.addEventListener("keyup", function () {
            const filter = this.value.toLowerCase();
            const currentHeadId = (pickerMode === "members" && headIdInput) ? headIdInput.value.trim() : "";

            document.querySelectorAll("#residentPicker tbody tr").forEach(row => {
                const btn = row.querySelector(".picker-action");
                const rowId = btn ? btn.dataset.id : "";

                // Force hide if they are the head of family
                if (pickerMode === "members" && rowId === currentHeadId && currentHeadId !== "") {
                    row.style.display = "none";
                } else {
                    row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
                }
            });
        });

        document.querySelector(".close-picker-btn")?.addEventListener("click", () => togglePicker(false));
        
        document.getElementById("pickerDoneBtn")?.addEventListener("click", () => {
            if (membersInput) {
                membersInput.value = tempSelectedMembers.join(', ');
                renderMembersTable();
            }
            togglePicker(false);
        });
    }
    
    initPickerUI();

    /* =========================
       PICKER STATE MANAGEMENT
    ========================= */
    function updatePickerButtons() {
        if (pickerMode === "members") {
            document.getElementById("pickerFooter").style.display = "flex";
            document.getElementById("pickerDynamicTitle").innerText = "Select Members";
            document.getElementById("pickerCount").innerText = tempSelectedMembers.length;

            document.querySelectorAll(".picker-action").forEach(btn => {
                const id = btn.dataset.id; 
                if (tempSelectedMembers.some(m => m.id === id)) {
                    btn.classList.add("selected-state");
                    btn.innerHTML = `<i class="fa-solid fa-check"></i> Selected`;
                } else {
                    btn.classList.remove("selected-state");
                    btn.innerHTML = `Select`;
                }
            });
        } else {
            document.getElementById("pickerFooter").style.display = "none";
            document.getElementById("pickerDynamicTitle").innerText = "Select Head of Family";

            document.querySelectorAll(".picker-action").forEach(btn => {
                btn.classList.remove("selected-state");
                btn.innerHTML = `Select`;
            });
        }
    }

    function togglePicker(show) {
        if (!residentPicker) return;
        if (show) {
            residentPicker.classList.add("show");
            
            if (pickerMode === "members" && membersIdInput) {
                const ids = (membersIdInput.value || "").split(',').filter(Boolean);
                const names = (membersInput && membersInput.value || "").split(',').map(m => m.trim()).filter(Boolean);
                tempSelectedMembers = ids.map((id, idx) => ({ id: id, name: names[idx] || '' }));
            }

            const searchInput = document.getElementById("memberSearch");
            if (searchInput) searchInput.value = "";

            // SMART FILTER: Hide the current Head of Family row instantly when opening members
            const currentHeadId = (pickerMode === "members" && headIdInput) ? headIdInput.value.trim() : "";
            document.querySelectorAll("#residentPicker tbody tr").forEach(row => {
                const btn = row.querySelector(".picker-action");
                const rowId = btn ? btn.dataset.id : "";

                if (pickerMode === "members" && rowId === currentHeadId && currentHeadId !== "") {
                    row.style.display = "none"; // Hide Head
                } else {
                    row.style.display = ""; // Show everyone else
                }
            });

            updatePickerButtons();
        } else {
            residentPicker.classList.remove("show");
            pickerMode = null;
            tempSelectedMembers = []; 
        }
    }

    /* =========================
       OPEN PICKER MODAL EVENTS
    ========================= */
    if (headInput) {
        headInput.readOnly = true; 
        headInput.style.cursor = "pointer";
        headInput.addEventListener("click", () => {
            pickerMode = "head";
            togglePicker(true);
        });
    }

    if (openMembersPickerBtn) {
        openMembersPickerBtn.addEventListener("click", () => {
            pickerMode = "members";
            togglePicker(true);
        });
    }

    /* =========================
       MAIN FORM BUTTONS (Add Household)
    ========================= */
    if (addBtn) {
        addBtn.addEventListener('click', () => {
            if (form) form.reset();
            if (householdId) householdId.value = "";
            if (headIdInput) headIdInput.value = "";
            if (membersIdInput) membersIdInput.value = "";
            if (membersInput) membersInput.value = ""; 
            tempSelectedMembers = [];
            renderMembersTable(); 

            if (saveBtn) saveBtn.innerText = "Save Household";
            if (modalTitle) modalTitle.innerText = "Add New Household";
            if (modalIcon) modalIcon.className = "fa-solid fa-house";

            fetch('../api/get_next_household_number.php')
                .then(res => res.text())
                .then(num => { if(form.household_number) form.household_number.value = num; })
                .catch(err => console.error(err));

            openModal();
        });
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (overlay) overlay.addEventListener('click', closeModal);

    /* =========================
       FORM SUBMISSION
    ========================= */
    if (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const isUpdate = householdId && householdId.value;

        Popup.open({
            title: isUpdate ? "Update Household" : "Add Household",
            message: isUpdate
                ? "Are you sure you want to update this household?"
                : "Are you sure you want to add this household?",
            type: "warning",
            onOk: () => {

                fetch('../api/add_household.php', {
                    method: 'POST',
                    body: new FormData(form)
                })
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === 'success') {
                        Popup.open({
                            title: "Success",
                            message: isUpdate ? "Household updated successfully!" : "Household added successfully!",
                            type: "success",
                            onOk: () => {
                                location.reload();
                            }
                        });
                    } else if (data.trim() === 'conflict') {
                        // === OCC CONFLICT HANDLER ===
                        Popup.open({
                            title: "Update Conflict",
                            message: "Data changed! Another staff member updated this household while you were viewing it. Please refresh and try again.",
                            type: "danger"
                        });
                    } else if (data.trim().startsWith('head_conflict:')) {
                        const conflictingHousehold = data.trim().split(':')[1];
                        Popup.open({
                            title: "Duplicate Head of Family",
                            message: `This resident is already assigned as the Head of Family for <b>${conflictingHousehold}</b>.<br><br>To prevent missing data, please select a different Head, or reassign ${conflictingHousehold} first.`,
                            type: "warning"
                        });
                    } else {
                        Popup.open({
                            title: "Save Failed",
                            message: "Failed to save: " + data,
                            type: "danger"
                        });
                    }
                })
                .catch(err => {
                    Popup.open({
                        title: "Error",
                        message: "Error: " + err,
                        type: "danger"
                    });
                });

            }
        });
    });
}

    /* =========================
       RFID SCANNER
    ========================= */
    if (scanBtn) {
        scanBtn.addEventListener("click", () => {
            if (rfidOverlay) rfidOverlay.classList.add("show");
            rfidBuffer = "";
            scanning = true;
        });
    }

    if (cancelRfid) {
        cancelRfid.addEventListener("click", () => {
            if (rfidOverlay) rfidOverlay.classList.remove("show");
            scanning = false;
            rfidBuffer = "";
        });
    }

    document.addEventListener("keydown", (e) => {
        if (!scanning) return;
        if (e.key === "Enter") {
            if (rfidInput) rfidInput.value = rfidBuffer;
            if (rfidOverlay) rfidOverlay.classList.remove("show");
            scanning = false;
            rfidBuffer = "";
        } else if (e.key.length === 1) {
            rfidBuffer += e.key;
        }
    });

    /* =========================
       GLOBAL CLICK LISTENER
    ========================= */
    document.addEventListener("click", (e) => {

        const editBtn = e.target.closest(".edit");
        if (editBtn) {
            e.preventDefault();
            if (form) form.reset();
            
            if (householdId) householdId.value = editBtn.dataset.id;
            if (form.version) form.version.value = editBtn.dataset.version; 
            if (form.household_number) form.household_number.value = editBtn.dataset.number;
            
            if (headInput) headInput.value = editBtn.dataset.headname;
            if (headIdInput) headIdInput.value = editBtn.dataset.headid;
            if (addressInput) addressInput.value = editBtn.dataset.address;
            if (rfidInput) rfidInput.value = editBtn.dataset.rfid;
            
            tempSelectedMembers = [];
            const ids = (editBtn.dataset.memberids || "").split(",");
            const names = (editBtn.dataset.membernames || "").split(",");
            
            for(let i = 0; i < ids.length; i++) {
                if(ids[i]) tempSelectedMembers.push({ id: ids[i], name: names[i].trim() });
            }
            
            if (membersIdInput) membersIdInput.value = ids.join(',');
            if (membersInput) membersInput.value = names.join(', ');
            renderMembersTable();

            if (saveBtn) saveBtn.innerText = "Update Household";
            if (modalTitle) modalTitle.innerText = "Edit Household";
            if (modalIcon) modalIcon.className = "fa-solid fa-pen-to-square";
            openModal();
            return;
        }

        const deleteBtn = e.target.closest(".delete");
        if (deleteBtn) {
    e.preventDefault();
    const id = deleteBtn.dataset.id;

    Popup.open({
        title: "Delete Household",
        message: "Are you sure you want to delete this household?",
        type: "warning",
        onOk: () => {

            fetch('../api/delete_household.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${encodeURIComponent(id)}`
            })
            .then(res => res.text())
            .then(data => {
                if (data.trim() === 'success') {
                    deleteBtn.closest('tr').remove();
                    Popup.open({
                        title: "Deleted",
                        message: "Household archived successfully.",
                        type: "success"
                    });
                } else {
                    Popup.open({
                        title: "Delete Failed",
                        message: "Failed to delete: " + data,
                        type: "danger"
                    });
                }
            })
            .catch(err => {
                Popup.open({
                    title: "Server Error",
                    message: "Server error: " + err,
                    type: "danger"
                });
            });

        }
    });
}

        const pickerBtn = e.target.closest(".picker-action");
        if (pickerBtn) {
            e.preventDefault(); 
            e.stopPropagation(); 
            
            const id = pickerBtn.dataset.id;
            const name = pickerBtn.dataset.name.trim();
            const address = pickerBtn.dataset.address;
            const hhNum = pickerBtn.dataset.hhnum;
            const role = pickerBtn.dataset.role;

            const executeSelection = () => {
                if (pickerMode === "head") {
                    if (headInput) headInput.value = name;
                    if (headIdInput) headIdInput.value = id;
                    if (addressInput && address) addressInput.value = address;
                    
                    tempSelectedMembers = tempSelectedMembers.filter(m => m.id !== id);
                    if (membersIdInput) membersIdInput.value = tempSelectedMembers.map(m => m.id).join(',');
                    if (membersInput) membersInput.value = tempSelectedMembers.map(m => m.name).join(', ');
                    renderMembersTable();
                    
                    togglePicker(false);
                } 
                else if (pickerMode === "members") {
                    if (headIdInput && headIdInput.value === id) {
                        Popup.open({ title: "Invalid", message: "The Head of Family cannot also be a member.", type: "warning" });
                        return;
                    }

                    const existingIndex = tempSelectedMembers.findIndex(m => m.id === id);
                    if (existingIndex > -1) {
                        tempSelectedMembers.splice(existingIndex, 1);
                        pickerBtn.classList.remove("selected-state");
                        pickerBtn.innerHTML = role === "Available" ? `Select` : `Transfer`;
                    } else {
                        tempSelectedMembers.push({ id: id, name: name });
                        pickerBtn.classList.add("selected-state");
                        pickerBtn.innerHTML = `<i class="fa-solid fa-check"></i> Selected`;
                    }
                    
                    if (membersIdInput) membersIdInput.value = tempSelectedMembers.map(m => m.id).join(',');
                    if (membersInput) membersInput.value = tempSelectedMembers.map(m => m.name).join(', ');
                    renderMembersTable();
                    
                    const countText = document.getElementById("pickerCount");
                    if (countText) countText.innerText = tempSelectedMembers.length;
                }
            };

            if (role !== "Available" && !pickerBtn.classList.contains("selected-state")) {
                
                if (role === "HEAD") {
                    Popup.open({
                        title: "Action Blocked",
                        message: `This resident is actively the Head of Family for <b>${hhNum}</b>.<br><br>To prevent orphaned households, you cannot transfer an active Head. Please edit ${hhNum} and assign a new head there first before moving this resident.`,
                        type: "danger"
                    });
                    return;
                }

                if (role === "MEMBER") {
                    Popup.open({
                        title: "Transfer Resident?",
                        message: `This resident is currently assigned as a Member of <b>${hhNum}</b>.<br><br>Do you want to transfer them to this new household?`,
                        type: "warning",
                        onOk: () => {
                            executeSelection();
                        }
                    });
                    return;
                }

            } else {
                executeSelection();
            }
            return;
        }

        const removeMemberBtn = e.target.closest(".remove-member-btn");
        if (removeMemberBtn) {
            e.preventDefault();
            const id = removeMemberBtn.dataset.id;
            removeMemberFromInput(id); 
        }

        if (e.target.classList.contains("member-count")) {
            const members = (e.target.dataset.members || "").split(",").map(m => m.trim()).filter(Boolean);
            if (membersBody) {
                membersBody.innerHTML = members.length
                    ? "<ul>" + members.map(m => `<li>${m}</li>`).join("") + "</ul>"
                    : "<p><i>No members found</i></p>";
                if (membersOverlay) membersOverlay.classList.add("show");
            }
        }

        if (e.target.id === "closeMembersOverlay" || e.target.id === "membersOverlay") {
            if (membersOverlay) membersOverlay.classList.remove("show");
        }
    });

};

// Call immediately if DOM is ready, otherwise wait
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initHouseholdManagement);
} else {
    window.initHouseholdManagement();
}