<?php
require_once 'lib/jwt_helper.php';
$auth_user  = JWTHelper::authenticate();
$page_title = 'Categories';
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/header.php'; ?>

<body class="bg-gray-50 font-sans" data-page="categories">
    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/navbar.php'; ?>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-6 h-14 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <button id="sidebar-toggle" class="btn-icon">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h1 class="page-title">Categories</h1>
                </div>
                <button id="btn-add-category" class="btn-primary">
                    <i class="fa-solid fa-plus mr-1.5"></i>Add Category
                </button>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <div class="card">
                    <div class="dataTables_top">
                        <div class="flex items-center gap-2">
                            <span class="section-title">Category List</span>
                            <span id="category-count" class="badge badge-indigo">—</span>
                        </div>
                    </div>
                    <div class="table-wrapper">
                        <table id="categories-table" class="data-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category Name</th>
                                    <th>Products</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categories-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div id="category-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-md w-full">
            <div class="modal-header">
                <h2 id="category-modal-title" class="text-lg font-semibold text-gray-900">Add Category</h2>
                <button onclick="IMS.closeModal('category-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="category-form">
                    <input type="hidden" name="category_id" id="form-category-id">
                    <div>
                        <label class="form-label">Category Name <span class="text-red-500">*</span></label>
                        <input name="category_name" id="form-category-name" type="text" class="form-input" placeholder="e.g. Electronics" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('category-modal')" class="btn-secondary">Cancel</button>
                <button id="category-form-submit" class="btn-primary">
                    <i class="fa-solid fa-check mr-1.5"></i><span>Save</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirm -->
    <div id="delete-category-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-sm w-full">
            <div class="modal-header">
                <h2 class="text-lg font-semibold text-gray-900">Delete Category</h2>
                <button onclick="IMS.closeModal('delete-category-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-gray-600">Delete <strong id="delete-category-name"></strong>? Products in this category must be reassigned first.</p>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('delete-category-modal')" class="btn-secondary">Cancel</button>
                <button id="confirm-delete-category-btn" class="btn-danger">
                    <i class="fa-solid fa-trash mr-1.5"></i>Delete
                </button>
            </div>
        </div>
    </div>

    <script src="/assets/js/pages/categories.js?v=2.0" defer></script>

</body>

</html>