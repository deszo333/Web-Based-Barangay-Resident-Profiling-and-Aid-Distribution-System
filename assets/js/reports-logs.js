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
                        backgroundColor: ["#006B2D", "#C81B20"]
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
    // generate report and filter
    // ================================
    const generateBtn = document.querySelector(".generate-report");

    if (generateBtn) {
        generateBtn.addEventListener("click", () => {

            const programName = document.getElementById("reportType").value;

            if (!programName) {
                Popup.open({ 
                    title: "Action Required", 
                    message: "Please select a program from the dropdown first.", 
                    type: "warning" 
                });
                return; 
            }

            fetch("filter_programs.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "program_name=" + encodeURIComponent(programName)
            })
            .then(res => res.text())
            .then(data => {
                const list = document.querySelector(".program-list");
                list.innerHTML = data;

                // render the new, single enhanced pie chart
                const canvas = document.querySelector(".mini-chart");
                if (canvas) {
                    canvas.style.display = "block";
                    new Chart(canvas.getContext("2d"), {
                        type: "pie",
                        data: {
                            labels: ["Claimed", "Remaining"],
                            datasets: [{
                                data: [canvas.dataset.claimed, canvas.dataset.remaining],
                                backgroundColor: ["#006B2D", "#C81B20"]
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false }
                    });
                }

                // make the card tappable to open the modal
                const tappableCard = document.querySelector(".tappable-chart");
                console.log("Looking for tappable card...", tappableCard);
                
                if (tappableCard) {
                    console.log("Found tappable card, attaching click listener");
                    tappableCard.addEventListener("click", () => {
                        const progName = tappableCard.dataset.program;
                        console.log("Card clicked! Program name:", progName);
                        
                        // fetch modal data
                        fetch("get_detailed_report.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: "program_name=" + encodeURIComponent(progName)
                        })
                        .then(res => {
                            console.log("Fetch response received:", res.status);
                            if (!res.ok) {
                                throw new Error("HTTP error, status = " + res.status);
                            }
                            return res.text();
                        })
                        .then(html => {
                            console.log("HTML received, updating modal");
                            document.getElementById("modalReportContent").innerHTML = html;
                            const modal = document.getElementById("detailedReportModal");
                            modal.classList.add("show");
                            console.log("Modal displayed");
                        })
                        .catch(err => {
                            console.error("Fetch error:", err);
                            alert("Error loading detailed report: " + err.message);
                        });
                    });
                } else {
                    console.warn("Tappable card not found");
                }
            })
            .catch(err => console.error("Error:", err));
        });
    }

    // ================================
    // modal close logic
    // ================================
    const closeBtn = document.getElementById("closeReportModal");
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            console.log("Close button clicked");
            document.getElementById("detailedReportModal").classList.remove("show");
        });
    }
    
    // close modal when clicking outside
    const modalOverlay = document.getElementById("detailedReportModal");
    if (modalOverlay) {
        modalOverlay.addEventListener("click", (e) => {
            if (e.target === modalOverlay) {
                console.log("Modal overlay clicked, closing");
                modalOverlay.classList.remove("show");
            }
        });
    }

});