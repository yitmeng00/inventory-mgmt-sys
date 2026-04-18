<?php
require_once 'lib/jwt_helper.php';
$auth_user  = JWTHelper::authenticate();
$page_title = 'Products';
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/header.php'; ?>

<body class="bg-gray-50 font-sans" data-page="products">
    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/navbar.php'; ?>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-6 h-14 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <button id="sidebar-toggle" class="btn-icon">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h1 class="page-title">Products</h1>
                </div>
                <div class="flex items-center gap-2">
                    <button id="btn-import" class="btn-secondary btn-sm">
                        <i class="fa-solid fa-file-import mr-1.5 hidden! md:inline-block!"></i>Bulk Import
                    </button>
                    <button id="btn-export-csv" class="btn-secondary btn-sm">
                        <i class="fa-solid fa-file-csv mr-1.5 hidden! md:inline-block!"></i>Export CSV
                    </button>
                    <button id="btn-add-product" class="btn-primary">
                        <i class="fa-solid fa-plus mr-1.5 hidden! md:inline-block!"></i>Add Product
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <div class="card">
                    <div class="dataTables_top">
                        <div class="flex items-center gap-2">
                            <span class="section-title">Product List</span>
                            <span id="product-count" class="badge badge-indigo">—</span>
                        </div>
                    </div>
                    <div class="table-wrapper">
                        <table id="products-table" class="data-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>SKU</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Supplier</th>
                                    <th>Cost Price</th>
                                    <th>Sale Price</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="products-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="product-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-2xl w-full">
            <div class="modal-header">
                <h2 id="product-modal-title" class="text-lg font-semibold text-gray-900">Add Product</h2>
                <button onclick="IMS.closeModal('product-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="product-form" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="form-product-id">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Category <span class="text-red-500">*</span></label>
                            <select name="category_id" id="form-category" class="form-select" required></select>
                        </div>
                        <div>
                            <label class="form-label">Supplier <span class="text-red-500">*</span></label>
                            <select name="supplier_id" id="form-supplier" class="form-select" required></select>
                        </div>
                        <div>
                            <label class="form-label">SKU / Product Code <span class="text-red-500">*</span></label>
                            <input name="product_code" id="form-product-code" type="text" class="form-input" placeholder="e.g. SKU-001" required>
                        </div>
                        <div>
                            <label class="form-label">Product Name <span class="text-red-500">*</span></label>
                            <input name="product_name" id="form-product-name" type="text" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Cost Price <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-sm pointer-events-none">RM</span>
                                <input name="cost_price" id="form-cost-price" type="number" step="0.01" min="0" class="form-input pl-9" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Sale Price <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-sm pointer-events-none">RM</span>
                                <input name="sale_price" id="form-sale-price" type="number" step="0.01" min="0" class="form-input pl-9" required>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Description</label>
                            <input name="description" id="form-description" type="text" class="form-input" placeholder="Short product description">
                        </div>
                        <div class="sm:col-span-2" id="img-upload-row">
                            <label class="form-label">Product Image <span class="text-red-500">*</span></label>
                            <input name="attachment" id="form-attachment" type="file" accept="image/*" class="form-input">
                            <div id="current-img-wrapper" class="hidden mt-2">
                                <img id="current-img" src="" alt="Current" class="h-24 w-auto rounded-lg border border-gray-200 object-cover">
                                <p class="text-xs text-gray-400 mt-1">Leave empty to keep current image</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('product-modal')" class="btn-secondary">Cancel</button>
                <button id="product-form-submit" class="btn-primary">
                    <i class="fa-solid fa-check mr-1.5"></i><span>Save Product</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Import Modal -->
    <div id="import-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-lg w-full">
            <div class="modal-header">
                <h2 class="text-lg font-semibold text-gray-900">Bulk Import Products</h2>
                <button onclick="IMS.closeModal('import-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body space-y-4">
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                    <p class="font-semibold mb-1"><i class="fa-solid fa-info-circle mr-1"></i>CSV Format Required</p>
                    <p class="font-mono text-xs">product_code, product_name, category_name, supplier_name, cost_price, sale_price, description</p>
                </div>
                <div>
                    <label class="form-label">Upload CSV File</label>
                    <input type="file" id="import-file" accept=".csv" class="form-input">
                </div>
                <div id="import-result" class="hidden"></div>
                <a href="/assets/templates/products_template.csv" download
                    class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    <i class="fa-solid fa-download text-xs"></i>Download Template
                </a>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('import-modal')" class="btn-secondary">Close</button>
                <button id="import-submit" class="btn-primary">
                    <i class="fa-solid fa-upload mr-1.5"></i>Import
                </button>
            </div>
        </div>
    </div>

    <!-- View/Delete confirm modal -->
    <div id="delete-confirm-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-sm w-full">
            <div class="modal-header">
                <h2 class="text-lg font-semibold text-gray-900">Delete Product</h2>
                <button onclick="IMS.closeModal('delete-confirm-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-gray-600">Are you sure you want to delete <strong id="delete-product-name"></strong>? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('delete-confirm-modal')" class="btn-secondary">Cancel</button>
                <button id="confirm-delete-btn" class="btn-danger">
                    <i class="fa-solid fa-trash mr-1.5"></i>Delete
                </button>
            </div>
        </div>
    </div>

    <script src="/assets/js/pages/products.js?v=2.0" defer></script>

</body>

</html>