let usersTable = null;
let editUserId = null;
let pendingAction = null;

document.addEventListener("DOMContentLoaded", async () => {
    await loadUsers();
    bindEvents();
});

async function loadUsers() {
    const data = await API.get("/db/user_db.php");
    if (!data.success && !data.users) {
        Toast.error("Error", "Failed to load users");
        return;
    }
    const rows = data.users || [];
    document.getElementById("user-count").textContent = rows.length;

    const tbody = document.getElementById("users-tbody");
    tbody.innerHTML = "";

    rows.forEach((u, i) => {
        const roleBadge =
            u.role === "admin"
                ? '<span class="badge badge-indigo">Admin</span>'
                : '<span class="badge">Staff</span>';
        const statusBadge =
            u.status === "active"
                ? '<span class="badge badge-green">Active</span>'
                : '<span class="badge badge-red">Inactive</span>';
        const isActive = u.status === "active";
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="text-gray-400">${i + 1}</td>
            <td><span class="font-mono text-xs text-gray-700">${esc(u.staff_id)}</span></td>
            <td class="font-medium text-gray-900">${esc(u.first_name)} ${esc(u.last_name)}</td>
            <td>${esc(u.username)}</td>
            <td>${esc(u.email || "—")}</td>
            <td>${esc(u.designation || "—")}</td>
            <td>${roleBadge}</td>
            <td>${statusBadge}</td>
            <td class="text-gray-500 text-xs">${formatDate(u.created_at)}</td>
            <td>
                <div class="flex items-center gap-1.5">
                    <button onclick="openEdit(${u.user_id})"
                        class="btn-icon text-indigo-600 hover:bg-indigo-50 text-xs px-2.5 py-1.5 rounded-lg border border-indigo-200">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button onclick="confirmToggle(${u.user_id}, '${esc(u.first_name)} ${esc(u.last_name)}', '${u.status}')"
                        class="btn-icon ${isActive ? "text-amber-600 hover:bg-amber-50 border-amber-200" : "text-green-600 hover:bg-green-50 border-green-200"} text-xs px-2.5 py-1.5 rounded-lg border"
                        title="${isActive ? "Deactivate" : "Activate"}">
                        <i class="fa-solid ${isActive ? "fa-user-slash" : "fa-user-check"}"></i>
                    </button>
                </div>
            </td>`;
        tbody.appendChild(tr);
    });

    if (usersTable) {
        usersTable.destroy();
    }
    usersTable = IMS.initDataTable("users-table", {
        order: [[2, "asc"]],
        columnDefs: [{ orderable: false, targets: [9] }],
    });
}

function bindEvents() {
    document.getElementById("btn-add-user").addEventListener("click", openAdd);
    document
        .getElementById("user-form-submit")
        .addEventListener("click", handleSave);
    document
        .getElementById("confirm-user-action-btn")
        .addEventListener("click", executeAction);
}

function openAdd() {
    editUserId = null;
    document.getElementById("user-modal-title").textContent =
        "Add Staff Account";
    document.getElementById("user-form").reset();
    document.getElementById("form-user-id").value = "";
    document.getElementById("form-password").required = true;
    document.getElementById("pw-required").classList.remove("hidden");
    document.getElementById("pw-hint").classList.add("hidden");
    IMS.openModal("user-modal");
}

async function openEdit(userId) {
    editUserId = userId;
    document.getElementById("user-modal-title").textContent =
        "Edit Staff Account";
    document.getElementById("form-password").required = false;
    document.getElementById("pw-required").classList.add("hidden");
    document.getElementById("pw-hint").classList.remove("hidden");

    const data = await API.get(`/db/user_db.php?user_id=${userId}`);
    if (!data.success) {
        Toast.error("Error", "Failed to load user");
        return;
    }
    const u = data.user;

    document.getElementById("form-user-id").value = u.user_id;
    document.getElementById("form-first-name").value = u.first_name;
    document.getElementById("form-last-name").value = u.last_name;
    document.getElementById("form-username").value = u.username;
    document.getElementById("form-staff-id").value = u.staff_id;
    document.getElementById("form-user-email").value = u.email || "";
    document.getElementById("form-designation").value = u.designation || "";
    document.getElementById("form-role").value = u.role;
    document.getElementById("form-password").value = "";

    IMS.openModal("user-modal");
}

async function handleSave() {
    const btn = document.getElementById("user-form-submit");
    const form = document.getElementById("user-form");
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const password = document.getElementById("form-password").value;
    if (!editUserId && password.length < 8) {
        Toast.warning(
            "Weak Password",
            "Password must be at least 8 characters",
        );
        return;
    }

    IMS.setLoading(btn, true, "Save Account");
    try {
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        if (!payload.password) delete payload.password;

        let data;
        if (editUserId) {
            payload.user_id = editUserId;
            data = await API.put("/db/user_db.php", payload);
        } else {
            data = await API.post("/db/user_db.php?action=create", payload);
        }
        if (data.success) {
            Toast.success(
                "Success",
                editUserId ? "Account updated" : "Account created",
            );
            IMS.closeModal("user-modal");
            await loadUsers();
        } else {
            Toast.error("Error", data.error_msg || "Operation failed");
        }
    } catch (e) {
        Toast.error("Error", e.message);
    } finally {
        IMS.setLoading(btn, false, "Save Account");
    }
}

function confirmToggle(userId, name, currentStatus) {
    const isActive = currentStatus === "active";
    pendingAction = {
        type: "toggle",
        userId,
        newStatus: isActive ? "inactive" : "active",
    };
    document.getElementById("user-action-title").textContent = isActive
        ? "Deactivate Account"
        : "Activate Account";
    document.getElementById("user-action-msg").innerHTML = isActive
        ? `Deactivate <strong>${name}</strong>? They will be unable to log in.`
        : `Activate <strong>${name}</strong>? They will regain access.`;
    const btn = document.getElementById("confirm-user-action-btn");
    btn.className = isActive ? "btn-danger" : "btn-success";
    btn.innerHTML = isActive ? "Deactivate" : "Activate";
    IMS.openModal("delete-user-modal");
}

async function executeAction() {
    if (!pendingAction) return;
    const btn = document.getElementById("confirm-user-action-btn");
    btn.disabled = true;

    if (pendingAction.type === "toggle") {
        try {
            const data = await API.put("/db/user_db.php", {
                user_id: pendingAction.userId,
                status: pendingAction.newStatus,
            });
            if (data.success) {
                const label =
                    pendingAction.newStatus === "active"
                        ? "Activated"
                        : "Deactivated";
                Toast.success(label, "Account status updated");
                IMS.closeModal("delete-user-modal");
                await loadUsers();
            } else {
                Toast.error("Error", data.error_msg);
            }
        } catch (e) {
            Toast.error("Error", e.message);
        }
    }

    btn.disabled = false;
    pendingAction = null;
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
