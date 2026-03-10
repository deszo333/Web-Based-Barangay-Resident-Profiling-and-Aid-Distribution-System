document.addEventListener("DOMContentLoaded", () => {
    const seeMoreBtn = document.querySelector(".see-more-btn");
    const listWrapper = document.querySelector(".program-list-wrapper");
    const hiddenPrograms = document.querySelectorAll(".hidden-program");

    if (!seeMoreBtn || !listWrapper || hiddenPrograms.length === 0) return;

    let expanded = false;

    seeMoreBtn.addEventListener("click", () => {
        if (!expanded) {
            // Show hidden items first (so wrapper height can expand)
            hiddenPrograms.forEach(item => (item.style.display = "flex"));

            // Expand wrapper smoothly
            const fullHeight = listWrapper.scrollHeight;
            listWrapper.style.maxHeight = fullHeight + "px";
            listWrapper.classList.add("expanded");

            seeMoreBtn.textContent = "See Less";
        } else {
            // Collapse wrapper first
            const currentHeight = listWrapper.scrollHeight;
            listWrapper.style.maxHeight = currentHeight + "px";

            // Trigger reflow for transition
            listWrapper.offsetHeight; 

            // Animate to original height
            listWrapper.style.maxHeight = "450px";
            listWrapper.classList.remove("expanded");

            // Wait for transition to finish before hiding items
            listWrapper.addEventListener(
                "transitionend",
                function handler() {
                    hiddenPrograms.forEach(item => (item.style.display = "none"));
                    listWrapper.removeEventListener("transitionend", handler);
                }
            );

            seeMoreBtn.textContent = "See More";
        }

        expanded = !expanded;
    });
});