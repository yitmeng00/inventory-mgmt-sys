<?php
require_once 'lib/jwt_helper.php';
$auth_user  = JWTHelper::authenticate();
$page_title = 'Transactions';
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/header.php'; ?>

<body class="bg-gray-50 font-sans" data-page="transactions">
    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/navbar.php'; ?>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-6 h-14 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <button id="sidebar-toggle" class="btn-icon">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h1 class="page-title">Transactions</h1>
                </div>
                <div class="flex items-center gap-2">
                    <button id="btn-export-transactions" class="btn-secondary btn-sm">
                        <i class="fa-solid fa-file-csv mr-1.5 hidden! md:inline-block!"></i>Export
                    </button>
                    <button id="btn-add-transaction" class="btn-primary">
                        <i class="fa-solid fa-plus mr-1.5 hidden! md:inline-block!"></i>New Transaction
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">

                <!-- Summary info -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="card p-4 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-arrow-down text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Total Sales</p>
                            <p id="txn-total-sales" class="text-base font-bold text-gray-900">—</p>
                        </div>
                    </div>
                    <div class="card p-4 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-arrow-up text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Total Purchases</p>
                            <p id="txn-total-purchases" class="text-base font-bold text-gray-900">—</p>
                        </div>
                    </div>
                    <div class="card p-4 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-hashtag text-indigo-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Sale Transactions</p>
                            <p id="txn-sale-count" class="text-base font-bold text-gray-900">—</p>
                        </div>
                    </div>
                    <div class="card p-4 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-hashtag text-purple-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Purchase Transactions</p>
                            <p id="txn-purchase-count" class="text-base font-bold text-gray-900">—</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="dataTables_top">
                        <div class="flex items-center gap-2">
                            <span class="section-title">Transaction History</span>
                            <span id="txn-count" class="badge badge-indigo">—</span>
                        </div>
                    </div>
                    <div class="table-wrapper">
                        <table id="transactions-table" class="data-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>TXN Code</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="transactions-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Transaction Modal -->
    <div id="transaction-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-lg w-full">
            <div class="modal-header">
                <h2 id="transaction-modal-title" class="text-lg font-semibold text-gray-900">New Transaction</h2>
                <button onclick="IMS.closeModal('transaction-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="transaction-form">
                    <input type="hidden" name="transaction_id" id="form-txn-id">
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Transaction Type <span class="text-red-500">*</span></label>
                            <select name="type_id" id="form-txn-type" class="form-select" required></select>
                        </div>
                        <div>
                            <label class="form-label">Product <span class="text-red-500">*</span></label>
                            <select name="product_id" id="form-txn-product" class="form-select" required></select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Quantity <span class="text-red-500">*</span></label>
                                <input name="quantity" id="form-txn-qty" type="number" min="1" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Unit Price</label>
                                <input id="form-txn-unit-price" type="text" class="form-input bg-gray-50" disabled placeholder="Auto-fetched">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('transaction-modal')" class="btn-secondary">Cancel</button>
                <button id="transaction-form-submit" class="btn-primary">
                    <i class="fa-solid fa-check mr-1.5"></i><span>Save Transaction</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirm -->
    <div id="delete-txn-modal" class="modal-overlay hidden">
        <div class="modal-container max-w-sm w-full">
            <div class="modal-header">
                <h2 class="text-lg font-semibold text-gray-900">Delete Transaction</h2>
                <button onclick="IMS.closeModal('delete-txn-modal')" class="text-gray-400 hover:text-gray-600 btn-icon">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-gray-600">Delete transaction <strong id="delete-txn-code"></strong>? This will reverse the stock adjustment.</p>
            </div>
            <div class="modal-footer">
                <button onclick="IMS.closeModal('delete-txn-modal')" class="btn-secondary">Cancel</button>
                <button id="confirm-delete-txn-btn" class="btn-danger">
                    <i class="fa-solid fa-trash mr-1.5"></i>Delete
                </button>
            </div>
        </div>
    </div>

    <script src="/assets/js/pages/transactions.js?v=2.0" defer></script>

</body>

</html>