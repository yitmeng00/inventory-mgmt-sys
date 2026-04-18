<?php
require_once 'lib/jwt_helper.php';
$auth_user  = JWTHelper::requireAdmin();
$page_title = 'Staff Accounts';
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/header.php'; ?>

<body class="bg-gray-50 font-sans" data-page="users">
    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/navbar.php'; ?>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-6 h-14 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <button id="sidebar-toggle" class="btn-icon">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h1 class="page-title">Staff Accounts</h1>
                </div>
                <button id="btn-add-user" class="btn-primary">
                    <i class="fa-solid fa-user-plus mr-1.5"></i>Add Staff
                </button>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <div class="card">
                    <div class="dataTables_top">
                        <div class="flex items-center gap-2">
                            <span class="section-title">User Directory</span>
                            <span id="user-count" class="badge badge-indigo">—</span>
                        </div>
                    </div>
                    <div class="table-wrapper">
                        <table id="users-table" class="data-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Staff ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Designation</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="user-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-lg w-full">
            <div class="modal-header">
                <h2 id="user-modal-title" class="text-lg font-semibold text-gray-900">Add Staff Account</h2>
                <button onclick="IMS.closeModal('user-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="user-form">
                    <input type="hidden" name="user_id" id="form-user-id">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">First Name <span class="text-red-500">*</span></label>
                                <input name="first_name" id="form-first-name" type="text" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                                <input name="last_name" id="form-last-name" type="text" class="form-input" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Username <span class="text-red-500">*</span></label>
                                <input name="username" id="form-username" type="text" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Staff ID <span class="text-red-500">*</span></label>
                                <input name="staff_id" id="form-staff-id" type="text" class="form-input" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Email Address</label>
                            <input name="email" id="form-user-email" type="email" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Designation</label>
                            <input name="designation" id="form-designation" type="text" class="form-input" placeholder="e.g. Warehouse Staff">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Role <span class="text-red-500">*</span></label>
                                <select name="role" id="form-role" class="form-select" required>
                                    <option value="staff">Staff</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div id="password-field">
                                <label class="form-label">Password <span class="text-red-500" id="pw-required">*</span></label>
                                <input name="password" id="form-password" type="password" class="form-input" placeholder="Min 8 characters">
                                <p id="pw-hint" class="form-error hidden">Leave blank to keep current password</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('user-modal')" class="btn-secondary">Cancel</button>
                <button id="user-form-submit" class="btn-primary">
                    <i class="fa-solid fa-check mr-1.5"></i><span>Save Account</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Deactivate / Delete Confirm -->
    <div id="delete-user-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-sm w-full">
            <div class="modal-header">
                <h2 id="user-action-title" class="text-lg font-semibold text-gray-900">Confirm Action</h2>
                <button onclick="IMS.closeModal('delete-user-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="user-action-msg" class="text-sm text-gray-600"></p>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('delete-user-modal')" class="btn-secondary">Cancel</button>
                <button id="confirm-user-action-btn" class="btn-danger">Confirm</button>
            </div>
        </div>
    </div>

    <script src="/assets/js/pages/users.js?v=2.0" defer></script>

</body>

</html>