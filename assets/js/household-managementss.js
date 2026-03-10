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
    const addressInput = document.querySelector("input[name='address']");
    
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

        fetch("search_household.php?search=" + encodeURIComponent(searchValue))
        .then(response => response.text())
        .then(data => {
            tableBody.innerHTML = data;
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
       MAIN FORM BUTTONS (Add Household)
    ========================= */
    if (addBtn) {
        addBtn.addEventListener('click', () => {
            if (form) form.reset();
            if (householdId) householdId.value = "";
            if (membersInput) membersInput.value = ""; 
            renderMembersTable(); 

            if (saveBtn) saveBtn.innerText = "Save Household";
            if (modalTitle) modalTitle.innerText = "Add New Household";
            if (modalIcon) modalIcon.className = "fa-solid fa-house";

            fetch('get_next_household_number.php')
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
            })
            .catch(err => alert("Error: " + err));
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
            if (form.household_number) form.household_number.value = editBtn.dataset.number;
            if (headInput) headInput.value = editBtn.dataset.head;
            if (addressInput) addressInput.value = editBtn.dataset.address;
            if (rfidInput) rfidInput.value = editBtn.dataset.rfid;
            
            if (membersInput) {
                membersInput.value = editBtn.dataset.members;
                renderMembersTable();
            }

            if (saveBtn) saveBtn.innerText = "Update Household";
            if (modalTitle) modalTitle.innerText = "Edit Household";
            if (modalIcon) modalIcon.className = "fa-solid fa-pen-to-square";

            openModal();
        }

        const deleteBtn = e.target.closest(".delete");
        if (deleteBtn) {
            e.preventDefault();
            const id = deleteBtn.dataset.id;
            
            if (confirm("Are you sure you want to delete this household?")) {
                fetch('delete_household.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${encodeURIComponent(id)}`
                })
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === 'success') {
                        deleteBtn.closest('tr').remove();
                    } else {
                        alert("Failed to delete: " + data);
                    }
                })
                .catch(err => alert("Server error: " + err));
            }
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