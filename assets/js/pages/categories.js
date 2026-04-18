let categoriesTable = null;
let editCategoryId = null;
let deleteCategoryId = null;

document.addEventListener("DOMContentLoaded", async () => {
    await loadCategories();
    bindEvents();
});

async function loadCategories() {
    const data = await API.get("/db/category_db.php");
    if (!data.success && !data.categories) {
        Toast.error("Error", "Failed to load categories");
        return;
    }
    const rows = data.categories || [];
    document.getElementById("category-count").textContent = rows.length;

    if (categoriesTable) {
        categoriesTable.destroy();
        categoriesTable = null;
    }

    const tbody = document.getElementById("categories-tbody");
    tbody.innerHTML = "";

    rows.forEach((c, i) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="text-gray-400">${i + 1}</td>
            <td class="font-medium text-gray-900">${esc(c.category_name)}</td>
            <td><span class="badge badge-indigo">${c.product_count ?? 0} products</span></td>
            <td class="text-gray-500 text-xs">${formatDate(c.created_at)}</td>
            <td>
                <div class="flex items-center gap-1.5">
                    <button onclick="openEdit(${c.category_id})"
                        class="btn-icon text-indigo-600 hover:bg-indigo-50 text-xs px-2.5 py-1.5 rounded-lg border border-indigo-200">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button onclick="confirmDelete(${c.category_id}, '${esc(c.category_name)}', ${c.product_count ?? 0})"
                        class="btn-icon text-red-600 hover:bg-red-50 text-xs px-2.5 py-1.5 rounded-lg border border-red-200 ${(c.product_count ?? 0) > 0 ? "opacity-40 cursor-not-allowed" : ""}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>`;
        tbody.appendChild(tr);
    });

    categoriesTable = IMS.initDataTable("categories-table", {
        order: [[1, "asc"]],
        columnDefs: [{ orderable: false, targets: [4] }],
    });
}

function bindEvents() {
    document
        .getElementById("btn-add-category")
        .addEventListener("click", openAdd);
    document
        .getElementById("category-form-submit")
        .addEventListener("click", handleSave);
    document
        .getElementById("confirm-delete-category-btn")
        .addEventListener("click", handleDelete);
}

function openAdd() {
    editCategoryId = null;
    document.getElementById("category-modal-title").textContent =
        "Add Category";
    document.getElementById("category-form").reset();
    document.getElementById("form-category-id").value = "";
    IMS.openModal("category-modal");
}

function openEdit(categoryId) {
    editCategoryId = categoryId;
    document.getElementById("category-modal-title").textContent =
        "Edit Category";

    const row = [...document.querySelectorAll("#categories-tbody tr")].find(
        (tr) => {
            return tr.querySelector(
                `button[onclick="openEdit(${categoryId})"]`,
            );
        },
    );
    const name = row?.cells[1]?.textContent?.trim() ?? "";
    document.getElementById("form-category-id").value = categoryId;
    document.getElementById("form-category-name").value = name;

    IMS.openModal("category-modal");
}

async function handleSave() {
    const btn = document.getElementById("category-form-submit");
    const form = document.getElementById("category-form");
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    IMS.setLoading(btn, true, "Save");
    try {
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        let data;
        if (editCategoryId) {
            payload.category_id = editCategoryId;
            data = await API.put("/db/category_db.php", payload);
        } else {
            data = await API.post("/db/category_db.php", payload);
        }
        if (data.success) {
            Toast.success(
                "Success",
                editCategoryId ? "Category updated" : "Category created",
            );
            IMS.closeModal("category-modal");
            await loadCategories();
        } else {
            Toast.error("Error", data.error_msg || "Operation failed");
        }
    } catch (e) {
        Toast.error("Error", e.message);
    } finally {
        IMS.setLoading(btn, false, "Save");
    }
}

function confirmDelete(id, name, productCount) {
    if (productCount > 0) {
        Toast.warning(
            "Cannot Delete",
            `"${name}" has ${productCount} product(s). Reassign them first.`,
        );
        return;
    }
    deleteCategoryId = id;
    document.getElementById("delete-category-name").textContent = name;
    IMS.openModal("delete-category-modal");
}

async function handleDelete() {
    if (!deleteCategoryId) return;
    const btn = document.getElementById("confirm-delete-category-btn");
    btn.disabled = true;
    btn.innerHTML =
        '<i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i>Deleting…';
    try {
        const data = await API.delete("/db/category_db.php", {
            category_id: deleteCategoryId,
        });
        if (data.success) {
            Toast.success("Deleted", "Category removed");
            IMS.closeModal("delete-category-modal");
            await loadCategories();
        } else {
            Toast.error("Error", data.error_msg);
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-trash mr-1.5"></i>Delete';
        deleteCategoryId = null;
    }
}

function formatDate(dateStr) {
    if (!dateStr) return "—";
    return new Date(dateStr).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

function esc(str) {
    return String(str ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}
