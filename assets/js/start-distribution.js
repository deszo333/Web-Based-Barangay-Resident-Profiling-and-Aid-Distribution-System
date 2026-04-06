// ==========================================
// 1. MASTER CONTROL LOGIC (Buttons)
// ==========================================
function toggleProgramStatus(programId, newStatus) {
    let actionText = newStatus === 'Ongoing' ? "start this event" : 
                     newStatus === 'Paused' ? "pause this event" : "permanently end this event";
    let warningType = newStatus === 'Completed' ? "danger" :
                      newStatus === 'Paused' ? "warning" : "info";

    Popup.open({
        title: `Confirm Action`,
        message: `Are you sure you want to ${actionText}?`,
        type: warningType,
        onOk: () => {
            fetch("../api/toggle_program_state.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ program_id: programId, new_status: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    location.reload(); 
                } else {
                    Popup.open({ title: "Error", message: data.message, type: "danger" });
                }
            })
            .catch(err => {
                Popup.open({ title: "Network Error", message: "Failed to communicate with server.", type: "danger" });
            });
        }
    });
}

// ==========================================
// 2. SCANNER & TRANSACTION LOGIC
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    const scanBtn = document.getElementById("scan_trigger");
    const rfidInput = document.getElementById("hidden_rfid_input");
    const overlay = document.getElementById("confirmOverlay");
    const modal = document.getElementById("confirmModal");
    const closeBtn = document.getElementById("closeConfirm");
    const cancelBtn = document.getElementById("cancelDistBtn");
    const confirmBtn = document.getElementById("confirmDistBtn");
    const scanStatus = document.getElementById("scan_status");

    // Load Recent Transactions
    function loadRecentTransactions() {
        if (typeof currentProgramId === 'undefined' || currentProgramId === 0) return;
        
        fetch(`../api/fetch_recent_transactions.php?program_id=${currentProgramId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                const targetEl = document.getElementById("progTarget");
                const claimedEl = document.getElementById("progClaimed");
                const barFill = document.getElementById("progressBarFill");
                
                if(targetEl) targetEl.textContent = data.target;
                if(claimedEl) claimedEl.textContent = data.claimed;
                
                if(barFill && data.target > 0) {
                    let percent = Math.min((data.claimed / data.target) * 100, 100);
                    barFill.style.width = percent + "%";
                }

                const claimedUl = document.getElementById("recentTransactionsList");
                if(claimedUl) {
                    claimedUl.innerHTML = "";
                    if (data.recent.length > 0) {
                        data.recent.forEach(txn => {
                            claimedUl.innerHTML += `<li><strong>${txn.household_number}</strong> - ${txn.head_of_family} <br> <span style="font-size: 11px; color: #9cb1c4;">${txn.formatted_date}</span></li>`;
                        });
                    } else {
                        claimedUl.innerHTML = `<li style='text-align:center; background:transparent; border:none; color:#9cb1c4;'>No claims yet.</li>`;
                    }
                }
            }
        })
        .catch(err => console.error("Error loading stats:", err));
    }

    // Call it initially if we are on the Ongoing screen
    if (document.getElementById("recentTransactionsList")) {
        loadRecentTransactions();
    }
    
    // MANUAL RFID ENTRY
    const manualInput = document.getElementById("manual_rfid_input");
    const manualProcessBtn = document.getElementById("manual_process_btn");

    if (manualProcessBtn && manualInput) {
        manualProcessBtn.addEventListener("click", () => {
            const manualRfid = manualInput.value.trim().toUpperCase(); 
            if (manualRfid === "") {
                Popup.open({ title: "Input Required", message: "Please enter an RFID number.", type: "warning" });
                return;
            }
            assignRFIDToInput(manualRfid);
            manualInput.value = "";
        });

        manualInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                manualProcessBtn.click();
            }
        });
    }

    // TRIGGER SCANNER CONNECTION
    if (scanBtn) {
        scanBtn.addEventListener("click", () => {
            if (typeof rfidPort !== "undefined" && rfidPort) {
                console.log("RFID already connected");
                return;
            }
            console.log("Connecting RFID scanner...");
            connectRFIDScanner(assignRFIDToInput, scanBtn);
            scanStatus.textContent = "Waiting for Scan...";
            scanBtn.classList.remove("pulse");
        });
    }

    // ASSIGN SCANNED ID TO INPUT AND FETCH INFO
    function assignRFIDToInput(scannedID) {
        console.log("Distributing to household rfid: ", scannedID);
        if (rfidInput) rfidInput.value = scannedID;

        fetch("../api/get_householdinfo.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ rfid_number: scannedID, program_id: currentProgramId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                showModal(data.household, data.claimed);
            } else {
                Popup.open({ title: "Scan Rejected", message: data.message, type: "danger" });
                if(rfidInput) rfidInput.value = "";
            }
        })
        .catch(err => console.error("Error fetching household:", err));
    }

    // SHOW CONFIRMATION MODAL
    function showModal(household, claimed) {
        document.getElementById("modHhNo").textContent = household.household_number;
        document.getElementById("modHead").textContent = household.head_of_family;
        document.getElementById("modAddress").textContent = household.address;
        document.getElementById("modMembers").textContent = household.household_members;

        const warningMsg = document.getElementById("claimedWarning");
        if (claimed) {
            warningMsg.style.display = "block";
            confirmBtn.disabled = true;
        } else {
            warningMsg.style.display = "none";
            confirmBtn.disabled = false;
        }

        overlay.classList.add("show");
        modal.classList.add("show");
    }

    // CONFIRM BUTTON LISTENER (Submit to Database)
    if (confirmBtn) {
        confirmBtn.addEventListener("click", () => {
            confirmBtn.disabled = true;
            confirmBtn.textContent = "Processing...";

            const finalRfid = rfidInput.value;

            fetch("../api/process_scan.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ rfid_number: finalRfid, program_id: currentProgramId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    Popup.open({ 
                        title: "Aid Claimed!", 
                        message: `Aid successfully recorded for <b>${data.head_of_family}</b>.`, 
                        type: "success" 
                    });
                    closeModal();
                    loadRecentTransactions();
                    if(rfidInput) rfidInput.value = "";
                } else {
                    Popup.open({ title: "Transaction Failed", message: data.message, type: "danger" });
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = "Confirm & Log Aid";
                }
            })
            .catch(err => {
                console.error("Processing error:", err);
                confirmBtn.disabled = false;
                confirmBtn.textContent = "Confirm & Log Aid";
            });
        });
    }

    // CLOSE MODAL LOGIC
    function closeModal() {
        if (overlay) overlay.classList.remove("show");
        if (modal) modal.classList.remove("show");
        if (confirmBtn) confirmBtn.textContent = "Confirm & Log Aid";
    }

    if (closeBtn) closeBtn.addEventListener("click", closeModal);
    if (cancelBtn) cancelBtn.addEventListener("click", closeModal);
    if (overlay) overlay.addEventListener("click", closeModal);
});