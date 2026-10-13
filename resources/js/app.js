import "@tabler/core/dist/js/tabler-theme.min.js";
import { Modal, Tooltip } from "@tabler/core/dist/js/tabler.esm.min.js";

window.Modal = Modal;

document.addEventListener("click", (event) => {
    const trigger = event.target.closest("[data-bs-theme-value]");

    if (!trigger) {
        return;
    }

    event.preventDefault();

    const theme = trigger.getAttribute("data-bs-theme-value");

    if (!theme) {
        return;
    }

    try {
        localStorage.setItem("tabler-theme", theme);
    } catch (e) {}

    const resolved =
        theme === "auto"
            ? window.matchMedia("(prefers-color-scheme: dark)").matches
                ? "dark"
                : "light"
            : theme;

    document.documentElement.setAttribute("data-bs-theme", resolved);

    Tooltip.getInstance(trigger)?.hide();
});
