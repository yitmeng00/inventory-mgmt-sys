/**
 * Toast notification system.
 * Usage: Toast.success('Title', 'Message') / Toast.error(...) / Toast.warning(...) / Toast.info(...)
 */
const Toast = (() => {
    let container = null;

    function getContainer() {
        if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            document.body.appendChild(container);
        }
        return container;
    }

    const icons = {
        success:
            '<svg class="toast-icon text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        error: '<svg class="toast-icon text-red-500"    fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        warning:
            '<svg class="toast-icon text-amber-500"  fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
        info: '<svg class="toast-icon text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };

    function show(type, title, message, duration = 4000) {
        const c = getContainer();
        const el = document.createElement("div");
        el.className = `toast ${type}`;
        el.innerHTML = `
            ${icons[type] || icons.info}
            <div class="toast-body">
                <p class="toast-title">${title}</p>
                ${message ? `<p class="toast-msg">${message}</p>` : ""}
            </div>
            <button class="toast-close ml-2" onclick="this.closest('.toast').remove()">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>`;
        c.appendChild(el);
        if (duration > 0) setTimeout(() => el.remove(), duration);
        return el;
    }

    return {
        success: (title, msg, d) => show("success", title, msg, d),
        error: (title, msg, d) => show("error", title, msg, d),
        warning: (title, msg, d) => show("warning", title, msg, d),
        info: (title, msg, d) => show("info", title, msg, d),
    };
})();
