(function () {

    let overlay, box, title, message, actions, closeBtn, icon;

    function init() {
        overlay = document.getElementById("popupOverlay");
        box = document.getElementById("popupBox");
        title = document.getElementById("popupTitle");
        message = document.getElementById("popupMessage");
        actions = document.getElementById("popupActions");
        closeBtn = document.getElementById("popupClose");
        icon = document.getElementById("popupIcon");

        overlay.addEventListener("click", close);
        closeBtn.addEventListener("click", close);
    }

    function open({
        title: t = "Message",
        message: m = "",
        type = "info", // info | warning | danger | success
        onOk = null
    }) {
        if (!overlay) init();

        title.innerText = t;
        message.innerHTML = m;
        actions.innerHTML = "";

        const icons = {
            info: '<i class="fa-solid fa-circle-info"></i>',
            warning: '<i class="fa-solid fa-triangle-exclamation"></i>',
            danger: '<i class="fa-solid fa-trash"></i>',
            success: '<i class="fa-solid fa-circle-check"></i>'
        };

        icon.innerHTML = icons[type] || icons.info;
        icon.className = `popup-icon ${type}`;

        const okBtn = document.createElement("button");
        okBtn.textContent = "OK";
        okBtn.className = "btn-primary";
        okBtn.onclick = () => {
            if (onOk) onOk();
            close();
        };

        actions.appendChild(okBtn);

        /* SHOW popup */
        overlay.style.display = "block";
        box.style.display = "block";

        setTimeout(() => {
            overlay.classList.add("show");
            box.classList.add("show");
        }, 10);
    }

    function close() {

        overlay.classList.remove("show");
        box.classList.remove("show");

        setTimeout(() => {
            overlay.style.display = "none";
            box.style.display = "none";
        }, 300);
    }

    window.Popup = { open, close };

})();