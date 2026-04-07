(function () {

    let overlay, box, title, message, actions, closeBtn, icon;
    let currentType = null;  // Track popup type to trigger reload on success

    function init() {
        overlay = document.getElementById("popupOverlay");
        box = document.getElementById("popupBox");
        title = document.getElementById("popupTitle");
        message = document.getElementById("popupMessage");
        actions = document.getElementById("popupActions");
        closeBtn = document.getElementById("popupClose");
        icon = document.getElementById("popupIcon");

        // Only close if clicking the dark background (overlay), not the white box
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) {
                close();
            }
        });
        closeBtn.addEventListener("click", close);
    }

    function open({
        title: t = "Message",
        message: m = "",
        type = "info",
        onOk = null,
        showCancel = null   // null = auto-detect (show cancel only for confirm flows)
    }) {
        if (!overlay) init();
        
        currentType = type;  // Store the type so close() can trigger refresh if success

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

        // Show cancel only when explicitly a confirmation (onOk and not success/info result)
        // Auto-detect: if showCancel is null, only show cancel for warning/danger with onOk
        const shouldShowCancel = showCancel !== null
            ? showCancel
            : (onOk !== null && (type === "warning" || type === "danger"));

        if (shouldShowCancel) {
            const cancelBtn = document.createElement("button");
            cancelBtn.textContent = "Cancel";
            cancelBtn.className = "btn-secondary";
            cancelBtn.onclick = () => { close(); };
            actions.appendChild(cancelBtn);
        }

        const okBtn = document.createElement("button");
        okBtn.textContent = (shouldShowCancel) ? "Confirm" : "OK";
        okBtn.className = type === "danger" ? "btn-danger" : "btn-primary";
        okBtn.onclick = () => {
            if (onOk) onOk();
            close();
        };

        actions.appendChild(okBtn);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                overlay.classList.add("show");
                box.classList.add("show");
            });
        });
    }

    function close() {
        overlay.classList.remove("show");
        box.classList.remove("show");
        
        // GLOBAL SUCCESS REFRESH: If this was a success message, ALWAYS reload!
        if (currentType === "success") {
            setTimeout(() => {
                window.location.reload();
            }, 300);  // Wait for CSS animation to complete
        }
    }

    window.Popup = { open, close };

})();