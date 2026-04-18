/**
 * IMS global utilities: modal management, CSV export, sidebar toggle.
 */
const IMS = {
    openModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove("hidden");
    },

    closeModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.add("hidden");
    },

    // Close modal when clicking overlay background
    initModalClose() {
        document.querySelectorAll(".modal-overlay").forEach((overlay) => {
            overlay.addEventListener("click", (e) => {
                if (e.target === overlay) overlay.classList.add("hidden");
            });
        });
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                document
                    .querySelectorAll(".modal-overlay:not(.hidden)")
                    .forEach((m) => m.classList.add("hidden"));
            }
        });
    },

    formatCurrency(val, symbol = "RM ") {
        return (
            symbol +
            parseFloat(val || 0).toLocaleString("en-US", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
        );
    },

    formatNumber(val) {
        return parseInt(val || 0).toLocaleString("en-US");
    },

    animateCounter(el, target, duration = 1500) {
        const start = Date.now();
        const startVal = 0;
        const isFloat = String(target).includes(".");
        function tick() {
            const elapsed = Date.now() - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            const current = startVal + (target - startVal) * eased;
            el.textContent = isFloat
                ? IMS.formatCurrency(current)
                : IMS.formatNumber(current);
            if (progress < 1) requestAnimationFrame(tick);
            else
                el.textContent = isFloat
                    ? IMS.formatCurrency(target)
                    : IMS.formatNumber(target);
        }
        requestAnimationFrame(tick);
    },

    exportToCSV(data, filename = "export.csv") {
        if (!data || !data.length) {
            Toast.warning("No data", "Nothing to export");
            return;
        }
        const headers = Object.keys(data[0]);
        const rows = data.map((row) =>
            headers
                .map((h) => `"${String(row[h] ?? "").replace(/"/g, '""')}"`)
                .join(","),
        );
        const csv = [headers.join(","), ...rows].join("\n");
        const blob = new Blob([csv], { type: "text/csv" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    },

    exportChart(chartId) {
        const canvas = document.getElementById(chartId);
        if (!canvas) return;
        const a = document.createElement("a");
        a.href = canvas.toDataURL("image/png");
        a.download = `${chartId}.png`;
        a.click();
    },

    setLoading(btn, loading, label = "Save") {
        if (!btn) return;
        if (loading) {
            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i>Saving…`;
        } else {
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-check mr-1.5"></i>${label}`;
        }
    },

    stockBadge(qty) {
        qty = parseInt(qty);
        if (qty === 0)
            return '<span class="badge badge-red">Out of Stock</span>';
        if (qty < 20)
            return `<span class="badge badge-yellow">Low (${qty})</span>`;
        return `<span class="badge badge-green">${qty}</span>`;
    },

    initDataTable(id, options = {}) {
        const defaults = {
            pageLength: 15,
            lengthMenu: [10, 15, 25, 50, 100],
            language: {
                search: "",
                searchPlaceholder: "Search…",
                emptyTable: "No records found",
                zeroRecords: "No matching records",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries",
                paginate: { previous: "‹", next: "›" },
            },
            dom: "<'dataTables_top'lf>t<'dataTables_bottom'ip>",
        };
        return $(`#${id}`).DataTable({ ...defaults, ...options });
    },
};

document.addEventListener("DOMContentLoaded", () => {
    IMS.initModalClose();

    // Sidebar toggle — single handler for all pages
    const toggle = document.getElementById("sidebar-toggle");
    const sidebar = document.getElementById("sidebar");
    if (toggle && sidebar) {
        toggle.addEventListener("click", () =>
            sidebar.classList.toggle("collapsed"),
        );
    }
});
