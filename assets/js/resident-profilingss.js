document.addEventListener("DOMContentLoaded", () => {

    /* =========================
       DOM ELEMENTS
    ========================= */
    const addBtn = document.querySelector('.add-resident');
    const modal = document.getElementById('residentModal');
    const overlay = document.getElementById('modalOverlay');
    const closeBtn = document.getElementById('closeModal');
    const form = document.getElementById('addResidentForm');

    const householdId = document.getElementById('resident_id');
    const headInput = document.getElementById("headInput");
    const addressInput = document.querySelector("input[name='address']");
    
    // Member Elements
    const membersInput = document.getElementById("membersInput"); 
    const openMembersPickerBtn = document.getElementById("openMembersPickerBtn");
    const membersTableBody = document.getElementById("membersTableBody");

    const residentPicker = document.getElementById("residentPicker");

    const modalTitle = document.getElementById("modalTitle");
    const modalIcon = document.getElementById("modalIcon");
    const saveBtn = form.querySelector("button[type='submit']");

    const membersOverlay = document.getElementById("membersOverlay");
    const membersBody = document.getElementById("membersBody");

    const scanBtn = document.getElementById("scanRfidBtn");
    const rfidOverlay = document.getElementById("rfidOverlay");
    const cancelRfid = document.getElementById("cancelRfid");
    const rfidInput = document.getElementById("rfidInput");

        /* =========================
    AUTO CALCULATE AGE
    ========================= */
    const birthdateInput = document.querySelector("input[name='birthdate']");
    const ageInput = document.querySelector("input[name='age']");

    if (birthdateInput && ageInput) {
        birthdateInput.addEventListener("change", function () {

            const birthDate = new Date(this.value);
            const today = new Date();

            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            ageInput.value = age;
        });
    }

    /* =========================
   AJAX SEARCH
========================= */

const searchInput = document.getElementById("searchInput");
const tableBody = document.getElementById("residentTableBody");

if (searchInput) {

    searchInput.addEventListener("keyup", function () {

        const searchValue = this.value;

        fetch("search_resident.php?search=" + encodeURIComponent(searchValue))
        .then(response => response.text())
        .then(data => {

            tableBody.innerHTML = data;

            if (data.trim() === "") {
                document.getElementById("noResultRow").style.display = "";
            } else {
                document.getElementById("noResultRow").style.display = "none";
            }

        })
        .catch(error => console.error("Search error:", error));

    });

}

    /* =========================
       ESCAPE CSS TRANSFORM TRAP
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
    function removeMemberFromInput(name) {
        if (!membersInput) return;
        const current = membersInput.value.split(',').map(m => m.trim()).filter(m => m && m !== name);
        membersInput.value = current.join(', ');
        renderMembersTable();
    }

    function renderMembersTable() {
        if (!membersTableBody || !membersInput) return;
        membersTableBody.innerHTML = "";

        const members = membersInput.value.split(',').map(m => m.trim()).filter(Boolean);

        if (members.length === 0) {
            membersTableBody.innerHTML = `<tr><td colspan="2" style="text-align: center; color: #94a3b8; font-style: italic;">No members added yet.</td></tr>`;
            return;
        }

        members.forEach(name => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${name}</td>
                <td style="text-align: center;">
                    <button type="button" class="remove-member-btn" data-name="${name}">
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
            const currentHead = (pickerMode === "members" && headInput) ? headInput.value.trim() : "";

            document.querySelectorAll("#residentPicker tbody tr").forEach(row => {
                const btn = row.querySelector(".picker-action");
                const rowName = btn ? btn.dataset.name.trim() : "";

                // Force hide if they are the head of family
                if (pickerMode === "members" && rowName === currentHead && currentHead !== "") {
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
                const name = btn.dataset.name.trim(); 
                if (tempSelectedMembers.includes(name)) {
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
            
            if (pickerMode === "members" && membersInput) {
                tempSelectedMembers = membersInput.value.split(',').map(m => m.trim()).filter(Boolean);
            }

            const searchInput = document.getElementById("memberSearch");
            if (searchInput) searchInput.value = "";

            // SMART FILTER: Hide the current Head of Family row instantly when opening members
            const currentHead = (pickerMode === "members" && headInput) ? headInput.value.trim() : "";
            document.querySelectorAll("#residentPicker tbody tr").forEach(row => {
                const btn = row.querySelector(".picker-action");
                const rowName = btn ? btn.dataset.name.trim() : "";

                if (pickerMode === "members" && rowName === currentHead && currentHead !== "") {
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
   MAIN FORM BUTTONS (ADD RESIDENT)
========================= */
if (addBtn) {
    addBtn.addEventListener('click', () => {

        if (form) form.reset();

        const residentId = document.getElementById("resident_id");
        if (residentId) residentId.value = "";

        if (saveBtn) saveBtn.innerText = "Save Resident";
        if (modalTitle) modalTitle.innerText = "Add New Resident";
        if (modalIcon) modalIcon.className = "fa-solid fa-user-plus";

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

        const residentId = document.getElementById("resident_id").value;

        const isUpdate = residentId !== "";

        const url = isUpdate ? "update_resident.php" : "add_resident.php";

        Popup.open({
            title: isUpdate ? "Update Resident" : "Add Resident",
            message: isUpdate
                ? "Are you sure you want to update this resident?"
                : "Are you sure you want to add this resident?",
            type: "warning",

            onOk: () => {

                if (isUpdate) {
                    const versionVal = document.getElementById("resident_version").value;
                    if (!versionVal || versionVal === "") {
                        Popup.open({
                            title: "Error",
                            message: "Version missing. Please close and reopen the record.",
                            type: "danger"
                        });
                        return;
                    }
                }

                fetch(url, {
                    method: 'POST',
                    body: new FormData(form)
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
                        if (isUpdate) {
                            const residentId = document.getElementById("resident_id").value;
                            const editBtn = document.querySelector(`.edit[data-id='${residentId}']`);
                            if (editBtn) {
                                const currentVersion = parseInt(editBtn.dataset.version || "1");
                                editBtn.dataset.version = currentVersion + 1;
                            }

                            // Also update the hidden version input so the form itself is current
                            const versionInput = document.getElementById("resident_version");
                            if (versionInput) {
                                versionInput.value = parseInt(versionInput.value || "1") + 1;
                            }
                        }

                        // Close the edit modal immediately before showing success
                        closeModal();

                        Popup.open({
                            title: "Success",
                            message: isUpdate
                                ? "Resident updated successfully."
                                : "Resident added successfully.",
                            type: "success",
                            onOk: () => location.reload()
                        });
                    } 
                    // === START OCC ALGORITHM: HANDLE CONFLICT RESPONSE ===
                    else if (response === 'conflict') {
                        Popup.open({
                            title: "Update Conflict",
                            message: "Data changed! Another staff member updated this record while you were viewing it. Please refresh the page and try again.",
                            type: "danger"
                        });
                    }
                    // === END OCC ALGORITHM ===
                    else {

                        Popup.open({
                            title: "Save Failed",
                            message: data,
                            type: "danger"
                        });

                    }

                })
                .catch(err => {

                    Popup.open({
                        title: "Server Error",
                        message: err,
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

        // Set ID
        const residentId = document.getElementById("resident_id");
        if (residentId) residentId.value = editBtn.dataset.id;

        // Fill form fields
        if (form.first_name) form.first_name.value = editBtn.dataset.first;
        if (form.middle_name) form.middle_name.value = editBtn.dataset.middle;
        if (form.last_name) form.last_name.value = editBtn.dataset.last;
        if (form.address) form.address.value = editBtn.dataset.address;
        if (form.birthdate) form.birthdate.value = editBtn.dataset.birthdate;
        if (form.age) form.age.value = editBtn.dataset.age;
        if (form.gender) form.gender.value = editBtn.dataset.gender;
        if (form.civil_status) form.civil_status.value = editBtn.dataset.civil;
        if (form.occupation) form.occupation.value = editBtn.dataset.occupation;
        if (form.voters_registration_no) form.voters_registration_no.value = editBtn.dataset.voters;
        if (form.contact) form.contact.value = editBtn.dataset.contact;


        // occ map the version to form
        if (form.version) form.version.value = editBtn.dataset.version;

        // Update modal text
        if (modalTitle) modalTitle.innerText = "Edit Resident";
        if (modalIcon) modalIcon.className = "fa-solid fa-user-pen";

        // Open modal
        openModal();
    }

        const deleteBtn = e.target.closest(".delete");

if (deleteBtn) {

    e.preventDefault();

    const id = deleteBtn.dataset.id;

    Popup.open({
        title: "Delete Resident",
        message: "Are you sure you want to delete this resident?",
        type: "warning",

        onOk: () => {

            fetch('delete_resident.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${encodeURIComponent(id)}`
            })
            .then(res => res.text())
            .then(data => {

                if (data.trim() === 'success') {

                    deleteBtn.closest('tr').remove();

                    Popup.open({
                        title: "Deleted",
                        message: "Resident deleted successfully.",
                        type: "success"
                    });

                } else {

                    Popup.open({
                        title: "Delete Failed",
                        message: data,
                        type: "danger"
                    });

                }

            })
            .catch(err => {

                Popup.open({
                    title: "Server Error",
                    message: err,
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
            
            const name = pickerBtn.dataset.name.trim(); 
            const address = pickerBtn.dataset.address;

            if (pickerMode === "head") {
                if (headInput) headInput.value = name;
                if (addressInput) addressInput.value = address;
                
                // If the new head was previously selected as a member, remove them!
                if (membersInput) {
                    let currentMembers = membersInput.value.split(',').map(m => m.trim()).filter(Boolean);
                    if (currentMembers.includes(name)) {
                        currentMembers = currentMembers.filter(m => m !== name);
                        membersInput.value = currentMembers.join(', ');
                        renderMembersTable();
                    }
                }
                togglePicker(false);
            } 
            else if (pickerMode === "members") {
                if (tempSelectedMembers.includes(name)) {
                    tempSelectedMembers = tempSelectedMembers.filter(m => m !== name);
                    pickerBtn.classList.remove("selected-state");
                    pickerBtn.innerHTML = `Select`;
                } else {
                    tempSelectedMembers.push(name);
                    pickerBtn.classList.add("selected-state");
                    pickerBtn.innerHTML = `<i class="fa-solid fa-check"></i> Selected`;
                }
                
                const countText = document.getElementById("pickerCount");
                if (countText) countText.innerText = tempSelectedMembers.length;
            }
        }

        const removeMemberBtn = e.target.closest(".remove-member-btn");
        if (removeMemberBtn) {
            e.preventDefault();
            const name = removeMemberBtn.dataset.name;
            removeMemberFromInput(name); 
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

});