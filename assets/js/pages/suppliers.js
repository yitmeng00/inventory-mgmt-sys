let suppliersTable = null;
let editSupplierId = null;
let deleteSupplierId = null;

document.addEventListener("DOMContentLoaded", async () => {
    await loadSuppliers();
    bindEvents();
});

async function loadSuppliers() {
    const data = await API.get("/db/supplier_db.php");
    if (!data.success && !data.suppliers) {
        Toast.error("Error", "Failed to load suppliers");
        return;
    }
    const rows = data.suppliers || [];
    document.getElementById("supplier-count").textContent = rows.length;

    if (suppliersTable) {
        suppliersTable.destroy();
        suppliersTable = null;
    }

    const tbody = document.getElementById("suppliers-tbody");
    tbody.innerHTML = "";

    rows.forEach((s, i) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="text-gray-400">${i + 1}</td>
            <td><span class="font-mono text-xs text-gray-700">${esc(s.supplier_code)}</span></td>
            <td class="font-medium text-gray-900">${esc(s.supplier_name)}</td>
            <td>${esc(s.contact_person)}</td>
            <td>${esc(s.contact_no)}</td>
            <td>${esc(s.email)}</td>
            <td>${esc(s.location || "—")}</td>
            <td><span class="badge badge-indigo">${s.product_count ?? 0}</span></td>
            <td>
                <div class="flex items-center gap-1.5">
                    <button onclick="openEdit(${s.supplier_id})"
                        class="btn-icon text-indigo-600 hover:bg-indigo-50 text-xs px-2.5 py-1.5 rounded-lg border border-indigo-200">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button onclick="confirmDelete(${s.supplier_id}, '${esc(s.supplier_name)}')"
                        class="btn-icon text-red-600 hover:bg-red-50 text-xs px-2.5 py-1.5 rounded-lg border border-red-200">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>`;
        tbody.appendChild(tr);
    });

    suppliersTable = IMS.initDataTable("suppliers-table", {
        order: [[2, "asc"]],
        columnDefs: [{ orderable: false, targets: [8] }],
    });
}

function bindEvents() {
    document
        .getElementById("btn-add-supplier")
        .addEventListener("click", openAdd);
    document
        .getElementById("supplier-form-submit")
        .addEventListener("click", handleSave);
    document
        .getElementById("btn-import-suppliers")
        .addEventListener("click", () =>
            IMS.openModal("import-supplier-modal"),
        );
    document
        .getElementById("import-supplier-submit")
        .addEventListener("click", handleImport);
    document
        .getElementById("confirm-delete-supplier-btn")
        .addEventListener("click", handleDelete);
}

function openAdd() {
    editSupplierId = null;
    document.getElementById("supplier-modal-title").textContent =
        "Add Supplier";
    document.getElementById("supplier-form").reset();
    document.getElementById("form-supplier-id").value = "";
    IMS.openModal("supplier-modal");
}

async function openEdit(supplierId) {
    editSupplierId = supplierId;
    document.getElementById("supplier-modal-title").textContent =
        "Edit Supplier";

    const data = await API.get(`/db/supplier_db.php?supplier_id=${supplierId}`);
    if (!data.success) {
        Toast.error("Error", "Failed to load supplier");
        return;
    }
    const s = data.supplier;

    document.getElementById("form-supplier-id").value = s.supplier_id;
    document.getElementById("form-supplier-name").value = s.supplier_name;
    document.getElementById("form-contact-person").value = s.contact_person;
    document.getElementById("form-contact-no").value = s.contact_no;
    document.getElementById("form-email").value = s.email;
    document.getElementById("form-location").value = s.location || "";

    IMS.openModal("supplier-modal");
}

async function handleSave() {
    const btn = document.getElementById("supplier-form-submit");
    const form = document.getElementById("supplier-form");
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    IMS.setLoading(btn, true, "Save Supplier");
    try {
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        let data;
        if (editSupplierId) {
            payload.supplier_id = editSupplierId;
            data = await API.put("/db/supplier_db.php", payload);
        } else {
            data = await API.post("/db/supplier_db.php", payload);
        }
        if (data.success) {
            Toast.success(
                "Success",
                editSupplierId ? "Supplier updated" : "Supplier created",
            );
            IMS.closeModal("supplier-modal");
            await loadSuppliers();
        } else {
            Toast.error("Error", data.error_msg || "Operation failed");
        }
    } catch (e) {
        Toast.error("Error", e.message);
    } finally {
        IMS.setLoading(btn, false, "Save Supplier");
    }
}

function confirmDelete(id, name) {
    deleteSupplierId = id;
    document.getElementById("delete-supplier-name").textContent = name;
    IMS.openModal("delete-supplier-modal");
}

async function handleDelete() {
    if (!deleteSupplierId) return;
    const btn = document.getElementById("confirm-delete-supplier-btn");
    btn.disabled = true;
    btn.innerHTML =
        '<i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i>Deleting…';
    try {
        const data = await API.delete("/db/supplier_db.php", {
            supplier_id: deleteSupplierId,
        });
        if (data.success) {
            Toast.success("Deleted", "Supplier removed successfully");
            IMS.closeModal("delete-supplier-modal");
            await loadSuppliers();
        } else {
            Toast.error("Error", data.error_msg);
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-trash mr-1.5"></i>Delete';
        deleteSupplierId = null;
    }
}

async function handleImport() {
    const file = document.getElementById("import-supplier-file").files[0];
    if (!file) {
        Toast.warning("No file", "Please select a CSV file");
        return;
    }
    const btn = document.getElementById("import-supplier-submit");
    IMS.setLoading(btn, true, "Import");
    const form = new FormData();
    form.append("csv_file", file);
    try {
        const data = await API.post("/db/import_db.php?type=suppliers", form);
        const res = data.results;
        const resultEl = document.getElementById("import-supplier-result");
        resultEl.classList.remove("hidden");
        resultEl.innerHTML = `
            <div class="p-3 rounded-lg ${res.skipped > 0 ? "bg-amber-50 border border-amber-200" : "bg-emerald-50 border border-emerald-200"} text-sm">
                <p class="font-semibold">Import complete</p>
                <p>Imported: <strong>${res.imported}</strong> &nbsp; Skipped: <strong>${res.skipped}</strong></p>
                ${
                    res.errors.length
                        ? '<ul class="mt-2 text-xs text-red-700 list-disc pl-4">' +
                          res.errors
                              .slice(0, 5)
                              .map((e) => `<li>${e}</li>`)
                              .join("") +
                          "</ul>"
                        : ""
                }
            </div>`;
        if (res.imported > 0) await loadSuppliers();
    } catch (e) {
        Toast.error("Import failed", e.message);
    } finally {
        IMS.setLoading(btn, false, "Import");
    }
}

function esc(str) {
    return String(str ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}
