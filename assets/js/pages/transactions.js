let txnTable = null;
let editTxnId = null;
let deleteTxnId = null;
let productsCache = [];

document.addEventListener("DOMContentLoaded", async () => {
    await Promise.all([loadTransactions(), populateSelects()]);
    bindEvents();
});

async function loadTransactions() {
    const data = await API.get("/db/transaction_db.php");
    if (!data.success && !data.transactions) {
        Toast.error("Error", "Failed to load transactions");
        return;
    }
    const rows = data.transactions || [];

    document.getElementById("txn-count").textContent = rows.length;

    let totalSales = 0,
        totalPurchases = 0,
        saleCount = 0,
        purchaseCount = 0;
    rows.forEach((r) => {
        const total = parseFloat(r.unit_price) * parseInt(r.quantity);
        if (parseInt(r.type_id) === 1) {
            totalSales += total;
            saleCount++;
        } else {
            totalPurchases += total;
            purchaseCount++;
        }
    });
    document.getElementById("txn-total-sales").textContent =
        IMS.formatCurrency(totalSales);
    document.getElementById("txn-total-purchases").textContent =
        IMS.formatCurrency(totalPurchases);
    document.getElementById("txn-sale-count").textContent =
        IMS.formatNumber(saleCount);
    document.getElementById("txn-purchase-count").textContent =
        IMS.formatNumber(purchaseCount);

    if (txnTable) {
        txnTable.destroy();
        txnTable = null;
    }

    const tbody = document.getElementById("transactions-tbody");
    tbody.innerHTML = "";

    rows.forEach((t, i) => {
        const total = parseFloat(t.unit_price) * parseInt(t.quantity);
        const isSale = parseInt(t.type_id) === 1;
        const typeBadge = isSale
            ? '<span class="badge badge-red">Sale</span>'
            : '<span class="badge badge-green">Purchase</span>';
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="text-gray-400">${i + 1}</td>
            <td><span class="font-mono text-xs text-gray-700">${esc(t.transaction_code)}</span></td>
            <td class="font-medium text-gray-900">${esc(t.product_name)}</td>
            <td>${typeBadge}</td>
            <td>${parseInt(t.quantity).toLocaleString()}</td>
            <td>${IMS.formatCurrency(t.unit_price)}</td>
            <td class="font-medium">${IMS.formatCurrency(total)}</td>
            <td class="text-gray-500 text-xs">${formatDate(t.created)}</td>
            <td>
                <div class="flex items-center gap-1.5">
                    <button onclick="openEdit(${t.transaction_id})"
                        class="btn-icon text-indigo-600 hover:bg-indigo-50 text-xs px-2.5 py-1.5 rounded-lg border border-indigo-200">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button onclick="confirmDelete(${t.transaction_id}, '${esc(t.transaction_code)}')"
                        class="btn-icon text-red-600 hover:bg-red-50 text-xs px-2.5 py-1.5 rounded-lg border border-red-200">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>`;
        tbody.appendChild(tr);
    });

    txnTable = IMS.initDataTable("transactions-table", {
        order: [[7, "desc"]],
        columnDefs: [{ orderable: false, targets: [8] }],
    });
}

async function populateSelects() {
    const [typeData, prodData] = await Promise.all([
        API.get("/db/transaction_type_db.php"),
        API.get("/db/product_db.php"),
    ]);
    productsCache = prodData.products || [];

    const typeSel = document.getElementById("form-txn-type");
    const prodSel = document.getElementById("form-txn-product");

    typeSel.innerHTML = '<option value="">Select type…</option>';
    (typeData.transaction_types || []).forEach((t) => {
        typeSel.insertAdjacentHTML(
            "beforeend",
            `<option value="${t.type_id}">${esc(t.type_name)}</option>`,
        );
    });

    prodSel.innerHTML = '<option value="">Select product…</option>';
    productsCache.forEach((p) => {
        prodSel.insertAdjacentHTML(
            "beforeend",
            `<option value="${p.product_id}" data-price="${p.sale_price}">${esc(p.product_name)} (Stock: ${p.quantity})</option>`,
        );
    });
}

function bindEvents() {
    document
        .getElementById("btn-add-transaction")
        .addEventListener("click", openAdd);
    document
        .getElementById("transaction-form-submit")
        .addEventListener("click", handleSave);
    document
        .getElementById("confirm-delete-txn-btn")
        .addEventListener("click", handleDelete);
    document
        .getElementById("btn-export-transactions")
        .addEventListener("click", handleExport);

    document
        .getElementById("form-txn-product")
        .addEventListener("change", function () {
            const opt = this.options[this.selectedIndex];
            const price = opt?.dataset?.price;
            document.getElementById("form-txn-unit-price").value = price
                ? IMS.formatCurrency(price)
                : "";
        });
}

function openAdd() {
    editTxnId = null;
    document.getElementById("transaction-modal-title").textContent =
        "New Transaction";
    document.getElementById("transaction-form").reset();
    document.getElementById("form-txn-id").value = "";
    document.getElementById("form-txn-unit-price").value = "";
    IMS.openModal("transaction-modal");
}

async function openEdit(txnId) {
    editTxnId = txnId;
    document.getElementById("transaction-modal-title").textContent =
        "Edit Transaction";

    const data = await API.get(
        `/db/transaction_db.php?transaction_id=${txnId}`,
    );
    if (!data.success) {
        Toast.error("Error", "Failed to load transaction");
        return;
    }
    const t = data.transaction;

    document.getElementById("form-txn-id").value = t.transaction_id;
    document.getElementById("form-txn-type").value = t.type_id;
    document.getElementById("form-txn-product").value = t.product_id;
    document.getElementById("form-txn-qty").value = t.quantity;
    document.getElementById("form-txn-unit-price").value = IMS.formatCurrency(
        t.unit_price,
    );

    IMS.openModal("transaction-modal");
}

async function handleSave() {
    const btn = document.getElementById("transaction-form-submit");
    const form = document.getElementById("transaction-form");
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    IMS.setLoading(btn, true, "Save Transaction");
    try {
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        let data;
        if (editTxnId) {
            payload.transaction_id = editTxnId;
            data = await API.put("/db/transaction_db.php", payload);
        } else {
            data = await API.post("/db/transaction_db.php", formData);
        }
        if (data.success) {
            Toast.success(
                "Success",
                editTxnId ? "Transaction updated" : "Transaction recorded",
            );
            IMS.closeModal("transaction-modal");
            await loadTransactions();
        } else {
            Toast.error("Error", data.error_msg || "Operation failed");
        }
    } catch (e) {
        Toast.error("Error", e.message);
    } finally {
        IMS.setLoading(btn, false, "Save Transaction");
    }
}

function confirmDelete(id, code) {
    deleteTxnId = id;
    document.getElementById("delete-txn-code").textContent = code;
    IMS.openModal("delete-txn-modal");
}

async function handleDelete() {
    if (!deleteTxnId) return;
    const btn = document.getElementById("confirm-delete-txn-btn");
    btn.disabled = true;
    btn.innerHTML =
        '<i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i>Deleting…';
    try {
        const data = await API.delete("/db/transaction_db.php", {
            transaction_id: deleteTxnId,
        });
        if (data.success) {
            Toast.success("Deleted", "Transaction removed and stock reversed");
            IMS.closeModal("delete-txn-modal");
            await loadTransactions();
        } else {
            Toast.error("Error", data.error_msg);
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-trash mr-1.5"></i>Delete';
        deleteTxnId = null;
    }
}

async function handleExport() {
    const data = await API.get("/db/transaction_db.php");
    if (!data.transactions) return;
    IMS.exportToCSV(
        data.transactions.map((t) => ({
            Code: t.transaction_code,
            Product: t.product_name,
            Type: t.type_name,
            Quantity: t.quantity,
            "Unit Price": t.unit_price,
            Total: (parseFloat(t.unit_price) * parseInt(t.quantity)).toFixed(2),
            Date: t.created,
        })),
        "transactions.csv",
    );
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
