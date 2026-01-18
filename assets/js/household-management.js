document.addEventListener("DOMContentLoaded", () => {
    const addBtn = document.querySelector('.add-household');
    const modal = document.getElementById('residentModal');
    const overlay = document.getElementById('modalOverlay');
    const closeBtn = document.getElementById('closeModal');
    const form = document.getElementById('addResidentForm');
    const householdId = document.getElementById('resident_id');

    // toast
    const toast = document.getElementById('members-toast');
    const toastText = document.getElementById('members-text');
    const closeToastBtn = document.getElementById('close-toast');

    if (!addBtn || !modal || !overlay || !closeBtn || !form || !householdId || !toast) {
        console.error("Some elements are missing.");
        return;
    }

    // open modal
    addBtn.addEventListener('click', () => {
        form.reset();
        householdId.value = "";
        form.querySelector("button").innerText = "Save Household";
        modal.classList.add('show');
        overlay.classList.add('show');
    });

    // close modal
    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    function closeModal() {
        modal.classList.remove('show');
        overlay.classList.remove('show');
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clean members before sending
        const cleanedMembers = form.household_members.value
            .split(',')
            .map(m => m.trim())
            .filter(Boolean)
            .join(', ');
        form.household_members.value = cleanedMembers;

        const formData = new FormData(form);

        fetch('add_household.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(data => {
            data = data.trim();
            if(data === 'success'){
                // Update row dynamically if editing
                if(householdId.value) {
                    const row = document.querySelector(`tr[data-id='${householdId.value}']`);
                    if(row){
                        row.querySelector('.member-count').dataset.members = cleanedMembers;
                        const memberCount = cleanedMembers.split(',').length;
                        row.querySelector('.member-count').innerText = `${memberCount} members`;
                        row.querySelector('.edit').dataset.number = form.household_number.value;
                        row.querySelector('.edit').dataset.head = form.head_of_family.value;
                        row.querySelector('.edit').dataset.address = form.address.value;
                        row.querySelector('.edit').dataset.members = cleanedMembers;
                    }
                } else {
                    // Reload page to show new entry
                    location.reload();
                }
                closeModal();
                toastText.innerText = "Household saved successfully!";
                toast.classList.add('show');
            } else {
                alert('Failed to save household: ' + data);
            }
        })
        .catch(err => { 
            console.error(err); 
            alert('Server error'); 
        });
    });

    // edit
    document.addEventListener('click', function(e){
        if(e.target.closest('.edit')) {
            const btn = e.target.closest('.edit');
            householdId.value = btn.dataset.id;
            form.household_number.value = btn.dataset.number;
            form.head_of_family.value = btn.dataset.head;
            form.address.value = btn.dataset.address;
            form.household_members.value = btn.dataset.members;
            form.querySelector("button").innerText = "Update Household";

            modal.classList.add('show');
            overlay.classList.add('show');
        }
    });

    // delete
    document.addEventListener('click', function(e){
        if(e.target.closest('.delete')){
            const btn = e.target.closest('.delete');
            const id = btn.dataset.id;

            if(confirm("Are you sure you want to delete this household?")) {
                fetch('delete_household.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${encodeURIComponent(id)}`
                })
                .then(res => res.text())
                .then(data => {
                    data = data.trim();
                    if(data === 'success'){
                        // Remove row from table
                        const row = btn.closest('tr');
                        if(row) row.remove();
                    } else {
                        alert("Failed to delete: " + data);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Server error");
                });
            }
        }
    });

    // show members toast
    document.addEventListener('click', function(e){
        if(e.target.classList.contains('member-count')) {
            e.stopPropagation();
            toastText.innerText = "Household Members:\n" + e.target.dataset.members;
            toast.classList.add('show');
        }
    });

    // close toast
    closeToastBtn.addEventListener('click', () => {
        toast.classList.remove('show');
    });
});
