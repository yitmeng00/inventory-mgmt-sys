let productsTable = null;
let editProductId = null;

document.addEventListener("DOMContentLoaded", async () => {
    await loadProducts();
    await populateSelects();
    bindEvents();
});

async function loadProducts() {
    const data = await API.get("/db/product_db.php");
    if (!data.success && !data.products) {
        Toast.error("Error", "Failed to load products");
        return;
    }
    const rows = data.products || [];
    document.getElementById("product-count").textContent = rows.length;

    if (productsTable) {
        productsTable.destroy();
        productsTable = null;
    }

    const tbody = document.getElementById("products-tbody");
    tbody.innerHTML = "";

    rows.forEach((p, i) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="text-gray-400">${i + 1}</td>
            <td><span class="font-mono text-xs text-gray-700">${esc(p.product_code)}</span></td>
            <td class="font-medium text-gray-900">${esc(p.product_name)}</td>
            <td>${esc(p.category_name)}</td>
            <td>${esc(p.supplier_name)}</td>
            <td>${IMS.formatCurrency(p.cost_price)}</td>
            <td>${IMS.formatCurrency(p.sale_price)}</td>
            <td>${IMS.stockBadge(p.quantity)}</td>
            <td>${stockStatusBadge(p.quantity)}</td>
            <td>
                <div class="flex items-center gap-1.5">
                    <button onclick="openEdit(${p.product_id})"
                        class="btn-icon text-indigo-600 hover:bg-indigo-50 text-xs px-2.5 py-1.5 rounded-lg border border-indigo-200">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button onclick="confirmDelete(${p.product_id}, '${esc(p.product_name)}')"
                        class="btn-icon text-red-600 hover:bg-red-50 text-xs px-2.5 py-1.5 rounded-lg border border-red-200">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>`;
        tbody.appendChild(tr);
    });

    productsTable = IMS.initDataTable("products-table", {
        order: [[2, "asc"]],
        columnDefs: [{ orderable: false, targets: [9] }],
    });
}

function stockStatusBadge(qty) {
    qty = parseInt(qty);
    if (qty === 0) return '<span class="badge badge-red">Out of Stock</span>';
    if (qty < 20) return '<span class="badge badge-yellow">Low Stock</span>';
    return '<span class="badge badge-green">In Stock</span>';
}

async function populateSelects() {
    const [catData, supData] = await Promise.all([
        API.get("/db/category_db.php"),
        API.get("/db/supplier_db.php"),
    ]);
    const catSel = document.getElementById("form-category");
    const supSel = document.getElementById("form-supplier");
    catSel.innerHTML = '<option value="">Select category…</option>';
    supSel.innerHTML = '<option value="">Select supplier…</option>';
    (catData.categories || []).forEach((c) => {
        catSel.insertAdjacentHTML(
            "beforeend",
            `<option value="${c.category_id}">${esc(c.category_name)}</option>`,
        );
    });
    (supData.suppliers || []).forEach((s) => {
        supSel.insertAdjacentHTML(
            "beforeend",
            `<option value="${s.supplier_id}">${esc(s.supplier_name)}</option>`,
        );
    });
}

function bindEvents() {
    document
        .getElementById("btn-add-product")
        .addEventListener("click", () => openAdd());
    document
        .getElementById("product-form-submit")
        .addEventListener("click", () => handleSave());
    document
        .getElementById("btn-import")
        .addEventListener("click", () => IMS.openModal("import-modal"));
    document
        .getElementById("import-submit")
        .addEventListener("click", handleImport);
    document
        .getElementById("btn-export-csv")
        .addEventListener("click", handleExport);
    document
        .getElementById("confirm-delete-btn")
        .addEventListener("click", handleDelete);
}

function openAdd() {
    editProductId = null;
    document.getElementById("product-modal-title").textContent = "Add Product";
    document.getElementById("product-form").reset();
    document.getElementById("form-product-id").value = "";
    document.getElementById("current-img-wrapper").classList.add("hidden");
    document.getElementById("form-attachment").required = true;
    IMS.openModal("product-modal");
}

async function openEdit(productId) {
    editProductId = productId;
    document.getElementById("product-modal-title").textContent = "Edit Product";
    document.getElementById("form-attachment").required = false;

    const data = await API.get(`/db/product_db.php?product_id=${productId}`);
    if (!data.success) {
        Toast.error("Error", "Failed to load product");
        return;
    }
    const p = data.product;

    document.getElementById("form-product-id").value = p.product_id;
    document.getElementById("form-product-code").value = p.product_code;
    document.getElementById("form-product-name").value = p.product_name;
    document.getElementById("form-description").value = p.description;
    document.getElementById("form-cost-price").value = p.cost_price;
    document.getElementById("form-sale-price").value = p.sale_price;
    document.getElementById("form-category").value = p.category_id;
    document.getElementById("form-supplier").value = p.supplier_id;

    if (p.img_path) {
        document.getElementById("current-img").src = "/" + p.img_path;
        document
            .getElementById("current-img-wrapper")
            .classList.remove("hidden");
    }

    IMS.openModal("product-modal");
}

async function handleSave() {
    const btn = document.getElementById("product-form-submit");
    const form = document.getElementById("product-form");

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    IMS.setLoading(btn, true, "Save Product");

    try {
        let data;
        if (editProductId) {
            const formData = new FormData(form);
            formData.set("product_id", editProductId);
            data = await API.postForm("/db/product_db.php", formData);
        } else {
            data = await API.postForm("/db/product_db.php", form);
        }

        if (data.success) {
            Toast.success(
                "Success",
                editProductId ? "Product updated" : "Product created",
            );
            IMS.closeModal("product-modal");
            await loadProducts();
        } else {
            Toast.error("Error", data.error_msg || "Operation failed");
        }
    } catch (e) {
        Toast.error("Error", e.message);
    } finally {
        IMS.setLoading(btn, false, "Save Product");
    }
}

let deleteProductId = null;
function confirmDelete(id, name) {
    deleteProductId = id;
    document.getElementById("delete-product-name").textContent = name;
    IMS.openModal("delete-confirm-modal");
}

async function handleDelete() {
    if (!deleteProductId) return;
    const btn = document.getElementById("confirm-delete-btn");
    btn.disabled = true;
    btn.innerHTML =
        '<i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i>Deleting…';
    try {
        const data = await API.delete("/db/product_db.php", {
            product_id: deleteProductId,
        });
        if (data.success) {
            Toast.success("Deleted", "Product removed successfully");
            IMS.closeModal("delete-confirm-modal");
            await loadProducts();
        } else {
            Toast.error("Error", data.error_msg);
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-trash mr-1.5"></i>Delete';
        deleteProductId = null;
    }
}

async function handleImport() {
    const file = document.getElementById("import-file").files[0];
    if (!file) {
        Toast.warning("No file", "Please select a CSV file");
        return;
    }
    const btn = document.getElementById("import-submit");
    IMS.setLoading(btn, true, "Import");
    const form = new FormData();
    form.append("csv_file", file);
    try {
        const data = await API.post("/db/import_db.php?type=products", form);
        const res = data.results;
        const resultEl = document.getElementById("import-result");
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
        if (res.imported > 0) await loadProducts();
    } catch (e) {
        Toast.error("Import failed", e.message);
    } finally {
        IMS.setLoading(btn, false, "Import");
    }
}

async function handleExport() {
    const data = await API.get("/db/product_db.php");
    if (!data.products) return;
    IMS.exportToCSV(
        data.products.map((p) => ({
            SKU: p.product_code,
            Name: p.product_name,
            Category: p.category_name,
            Supplier: p.supplier_name,
            "Cost Price": p.cost_price,
            "Sale Price": p.sale_price,
            Quantity: p.quantity,
            Description: p.description,
        })),
        "products.csv",
    );
}

function esc(str) {
    return String(str ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}
