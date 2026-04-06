const taglines = [
    "Fast and Transparent Barangay Services",
    "Simple, Fast, Accessible Services",
    "Efficient Services for Every Resident",
    "Connecting Community Through Service"
];

let index = 0;
const taglineElement = document.getElementById("tagline");

function typeText(text, i = 0) {
    taglineElement.textContent = text.substring(0, i);

    if (i < text.length) {
        setTimeout(() => typeText(text, i + 1), 80);
    }
}

function changeTagline() {
    taglineElement.style.opacity = 0;

    setTimeout(() => {
        index = (index + 1) % taglines.length;
        typeText(taglines[index]);
        taglineElement.style.opacity = 1;
    }, 500);
}

// wait until page is fully loaded
document.addEventListener("DOMContentLoaded", () => {
    typeText(taglines[0]);
    setInterval(changeTagline, 5000);
});