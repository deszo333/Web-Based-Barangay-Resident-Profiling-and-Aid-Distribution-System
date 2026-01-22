const addBtn = document.querySelector('.add-resident');
const modal = document.getElementById('residentModal');
const overlay = document.getElementById('modalOverlay');
const closeBtn = document.querySelector('.close-btn');
const form = document.getElementById("addResidentForm");

let isEdit = false;

// Open Add Resident Modal
addBtn.addEventListener('click', () => {
    isEdit = false;
    form.reset();
    form.querySelector("button").innerText = "Save Resident";
    form.resident_id.value = ""; // clear hidden field
    modal.classList.add('show');
    overlay.classList.add('show');
});

// Close modal
closeBtn.addEventListener('click', closeModal);
overlay.addEventListener('click', closeModal);

function closeModal() {
    modal.classList.remove('show');
    overlay.classList.remove('show');
}

// Edit resident
document.querySelectorAll(".edit").forEach(btn => {
    btn.addEventListener("click", () => {
        isEdit = true;

        // Fill modal with existing data
        form.resident_id.value = btn.dataset.id;
        form.first_name.value = btn.dataset.first;
        form.middle_name.value = btn.dataset.middle;
        form.last_name.value = btn.dataset.last;
        form.address.value = btn.dataset.address;
        form.birthdate.value = btn.dataset.birthdate;
        form.age.value = btn.dataset.age;
        form.gender.value = btn.dataset.gender;
        form.civil_status.value = btn.dataset.civil;
        form.occupation.value = btn.dataset.occupation;
        form.voters_registration_no.value = btn.dataset.voters;
        form.contact.value = btn.dataset.contact;

        form.querySelector("button").innerText = "Update Resident";

        modal.classList.add('show');
        overlay.classList.add('show');
    });
});

// Submit form (Add or Update)
form.addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const url = isEdit ? "update_resident.php" : "add_resident.php";

    fetch(url, {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if(data === "success") {
            alert(isEdit ? "Resident updated successfully!" : "Resident added successfully!");
            location.reload();
        } else {
            alert("Action failed");
        }
    });
});

// Delete resident
document.querySelectorAll(".delete").forEach(btn => {
    btn.addEventListener("click", () => {
        if(!confirm("Are you sure you want to delete this resident?")) return;

        fetch("delete_resident.php", {
            method: "POST",
            headers: {"Content-Type":"application/x-www-form-urlencoded"},
            body: "id=" + btn.dataset.id
        })
        .then(res => res.text())
        .then(data => {
            if(data === "success") {
                alert("Resident deleted successfully!");
                location.reload();
            } else {
                alert("Delete failed");
            }
        });
    });
});
