document.addEventListener("click", function (e) {
    if (e.target.closest(".deactivate") || e.target.closest(".activate")) {

        let btn = e.target.closest("button");
        let id = btn.getAttribute("data-id");

        fetch("toggle_user_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + id
        })
        .then(res => res.text())
        .then(status => {
            location.reload(); // refresh table
        });
    }
});