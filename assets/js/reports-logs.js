document.addEventListener("DOMContentLoaded", () => {

    const seeMoreBtn = document.querySelector(".see-more-btn");
    const listWrapper = document.querySelector(".program-list-wrapper");
    const hiddenPrograms = document.querySelectorAll(".hidden-program");

    let expanded = false;

    // ================================
    // INIT CHARTS FUNCTION
    // ================================
    function initCharts() {
        const items = document.querySelectorAll(".program-item");

        items.forEach(item => {

            const beneficiaries = Number(item.dataset.beneficiaries || 0);
            const distributed = Number(item.dataset.distributed || 0);
            const remaining = Math.max(beneficiaries - distributed, 0);

            const canvas = item.querySelector(".mini-chart");
            if (!canvas) return;

            // destroy old chart if exists
            if (canvas.chartInstance) {
                canvas.chartInstance.destroy();
            }

            canvas.style.display = "block";

            canvas.chartInstance = new Chart(canvas.getContext("2d"), {
                type: "pie",
                data: {
                    labels: ["Claimed", "Remaining"],
                    datasets: [{
                        data: [distributed, remaining],
                        backgroundColor: ["#4CAF50", "#FF6384"]
                    }]
                },
                options: {
                    responsive: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    }

    // ================================
    // SEE MORE BUTTON
    // ================================
    if (seeMoreBtn && listWrapper) {
        seeMoreBtn.addEventListener("click", () => {

            if (!expanded) {
                hiddenPrograms.forEach(item => (item.style.display = "flex"));

                listWrapper.style.maxHeight = listWrapper.scrollHeight + "px";
                listWrapper.classList.add("expanded");

                seeMoreBtn.textContent = "See Less";
            } else {

                listWrapper.style.maxHeight = listWrapper.scrollHeight + "px";
                listWrapper.offsetHeight;

                listWrapper.style.maxHeight = "450px";
                listWrapper.classList.remove("expanded");

                listWrapper.addEventListener("transitionend", function handler() {
                    hiddenPrograms.forEach(item => (item.style.display = "none"));
                    listWrapper.removeEventListener("transitionend", handler);
                });

                seeMoreBtn.textContent = "See More";
            }

            expanded = !expanded;
        });
    }

    // ================================
    // AUTO-FILL AID TYPE
    // ================================
    const reportType = document.getElementById("reportType");
    const programDropdown = document.getElementById("program");

    if (reportType && programDropdown) {
        reportType.addEventListener("change", function () {

            let programName = this.value;

            fetch("get_aid_type.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "program_name=" + encodeURIComponent(programName)
            })
            .then(res => res.text())
            .then(data => {

                programDropdown.innerHTML = "";

                let option = document.createElement("option");
                option.value = data;
                option.textContent = data;

                programDropdown.appendChild(option);
                programDropdown.disabled = false;
            })
            .catch(err => console.error("Error fetching aid type:", err));
        });
    }

    // ================================
    // GENERATE REPORT + FILTER
    // ================================
    const generateBtn = document.querySelector(".generate-report");

    if (generateBtn) {
        generateBtn.addEventListener("click", () => {

            const programName = document.getElementById("reportType").value;
            const aidType = document.getElementById("program").value;

            fetch("filter_programs.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body:
                    "program_name=" + encodeURIComponent(programName) +
                    "&aid_type=" + encodeURIComponent(aidType)
            })
            .then(res => res.text())
            .then(data => {

                const list = document.querySelector(".program-list");
                list.innerHTML = data;

                const wrapper = document.querySelector(".program-list-wrapper");
                if (wrapper) wrapper.style.maxHeight = "none";

                // IMPORTANT: render charts AFTER DOM update
                requestAnimationFrame(() => {
                    initCharts();
                });

            })
            .catch(err => console.error("Error:", err));
        });
    }

    

});