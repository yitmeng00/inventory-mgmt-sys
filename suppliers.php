<?php
require_once 'lib/jwt_helper.php';
$auth_user  = JWTHelper::authenticate();
$page_title = 'Suppliers';
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/header.php'; ?>

<body class="bg-gray-50 font-sans" data-page="suppliers">
    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/navbar.php'; ?>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-6 h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button id="sidebar-toggle" class="btn-icon">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h1 class="page-title">Suppliers</h1>
                </div>
                <div class="flex items-center gap-2">
                    <button id="btn-import-suppliers" class="btn-secondary btn-sm">
                        <i class="fa-solid fa-file-import mr-1.5 hidden! md:inline-block!"></i>Bulk Import
                    </button>
                    <button id="btn-add-supplier" class="btn-primary">
                        <i class="fa-solid fa-plus mr-1.5 hidden! md:inline-block!"></i>Add Supplier
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <div class="card">
                    <div class="dataTables_top">
                        <div class="flex items-center gap-2">
                            <span class="section-title">Supplier List</span>
                            <span id="supplier-count" class="badge badge-indigo">—</span>
                        </div>
                    </div>
                    <div class="table-wrapper">
                        <table id="suppliers-table" class="data-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Supplier Name</th>
                                    <th>Contact Person</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Location</th>
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="suppliers-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Supplier Modal -->
    <div id="supplier-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-lg w-full">
            <div class="modal-header">
                <h2 id="supplier-modal-title" class="text-lg font-semibold text-gray-900">Add Supplier</h2>
                <button onclick="IMS.closeModal('supplier-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="supplier-form">
                    <input type="hidden" name="supplier_id" id="form-supplier-id">
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Supplier Name <span class="text-red-500">*</span></label>
                            <input name="supplier_name" id="form-supplier-name" type="text" class="form-input" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Contact Person <span class="text-red-500">*</span></label>
                                <input name="contact_person" id="form-contact-person" type="text" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
                                <input name="contact_no" id="form-contact-no" type="tel" class="form-input" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                            <input name="email" id="form-email" type="email" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Location / Address</label>
                            <input name="location" id="form-location" type="text" class="form-input" placeholder="City, Country">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('supplier-modal')" class="btn-secondary">Cancel</button>
                <button id="supplier-form-submit" class="btn-primary">
                    <i class="fa-solid fa-check mr-1.5"></i><span>Save Supplier</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Import Suppliers Modal -->
    <div id="import-supplier-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-lg w-full">
            <div class="modal-header">
                <h2 class="text-lg font-semibold text-gray-900">Bulk Import Suppliers</h2>
                <button onclick="IMS.closeModal('import-supplier-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body space-y-4">
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                    <p class="font-semibold mb-1"><i class="fa-solid fa-info-circle mr-1"></i>CSV Format Required</p>
                    <p class="font-mono text-xs">supplier_name, contact_person, contact_no, email, location</p>
                </div>
                <div>
                    <label class="form-label">Upload CSV File</label>
                    <input type="file" id="import-supplier-file" accept=".csv" class="form-input">
                </div>
                <div id="import-supplier-result" class="hidden"></div>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('import-supplier-modal')" class="btn-secondary">Close</button>
                <button id="import-supplier-submit" class="btn-primary">
                    <i class="fa-solid fa-upload mr-1.5"></i>Import
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirm -->
    <div id="delete-supplier-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-sm w-full">
            <div class="modal-header">
                <h2 class="text-lg font-semibold text-gray-900">Delete Supplier</h2>
                <button onclick="IMS.closeModal('delete-supplier-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-gray-600">Are you sure you want to delete <strong id="delete-supplier-name"></strong>? This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('delete-supplier-modal')" class="btn-secondary">Cancel</button>
                <button id="confirm-delete-supplier-btn" class="btn-danger">
                    <i class="fa-solid fa-trash mr-1.5"></i>Delete
                </button>
            </div>
        </div>
    </div>

    <script src="/assets/js/pages/suppliers.js?v=2.0" defer></script>

</body>

</html>