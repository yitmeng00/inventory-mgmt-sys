<?php
/**
 * Data export endpoint — streams CSV downloads for products, suppliers,
 * transactions, and categories.
 *
 * Mirrors the client-side IMS.exportCSV() helper in assets/js/app.js but
 * runs server-side so that large datasets are not loaded into the browser.
 *
 * GET /db/export_db.php?type=products
 * GET /db/export_db.php?type=suppliers
 * GET /db/export_db.php?type=transactions[&date_from=YYYY-MM-DD&date_to=YYYY-MM-DD]
 * GET /db/export_db.php?type=categories
 * GET /db/export_db.php?type=low_stock
 */

require_once __DIR__ . '/../lib/jwt_helper.php';
require_once __DIR__ . '/../lib/CSVHelper.php';
require_once __DIR__ . '/../lib/Formatter.php';
require_once 'mysql_conn.php';

JWTHelper::authenticateAPI();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['success' => false, 'error_msg' => 'Method not allowed']);
    exit;
}

$conf = new DBConnection();
$conn = $conf->connect();

$type = $_GET['type'] ?? '';

switch ($type) {
    case 'products':     exportProducts();     break;
    case 'suppliers':    exportSuppliers();    break;
    case 'transactions': exportTransactions(); break;
    case 'categories':   exportCategories();   break;
    case 'low_stock':    exportLowStock();     break;
    default:
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Unknown export type. Use: products, suppliers, transactions, categories, low_stock']);
        exit;
}

// ---------------------------------------------------------------------------

function exportProducts(): void
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT p.product_id, p.product_name, p.description, p.price, p.quantity,
                p.low_stock_threshold, c.category_name, s.supplier_name,
                p.created_at
         FROM product p
         JOIN category c ON p.category_id = c.category_id
         JOIN supplier s ON p.supplier_id = s.supplier_id
         ORDER BY p.product_name"
    );
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Add derived columns
    $rows = array_map(function (array $r): array {
        $threshold = (int)($r['low_stock_threshold'] ?? 10);
        $r['stock_status']        = Formatter::stockLabel((int)$r['quantity'], $threshold);
        $r['inventory_value']     = number_format((float)$r['price'] * (int)$r['quantity'], 2, '.', '');
        $r['created_at_formatted']= Formatter::datetime($r['created_at'] ?? '');
        return $r;
    }, $rows);

    CSVHelper::download(
        $rows,
        ['product_id', 'product_name', 'description', 'price', 'quantity',
         'low_stock_threshold', 'stock_status', 'inventory_value',
         'category_name', 'supplier_name', 'created_at_formatted'],
        ['ID', 'Product Name', 'Description', 'Price (RM)', 'Quantity',
         'Low Stock Threshold', 'Stock Status', 'Inventory Value (RM)',
         'Category', 'Supplier', 'Created At'],
        'products_export_' . date('Ymd')
    );
}

// ---------------------------------------------------------------------------

function exportSuppliers(): void
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT s.supplier_id, s.supplier_name, s.contact_person, s.email, s.phone, s.address,
                COUNT(p.product_id) AS product_count, s.created_at
         FROM supplier s
         LEFT JOIN product p ON s.supplier_id = p.supplier_id
         GROUP BY s.supplier_id
         ORDER BY s.supplier_name"
    );
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $rows = array_map(function (array $r): array {
        $r['created_at_formatted'] = Formatter::datetime($r['created_at'] ?? '');
        return $r;
    }, $rows);

    CSVHelper::download(
        $rows,
        ['supplier_id', 'supplier_name', 'contact_person', 'email', 'phone', 'address', 'product_count', 'created_at_formatted'],
        ['ID', 'Supplier Name', 'Contact Person', 'Email', 'Phone', 'Address', 'Products', 'Created At'],
        'suppliers_export_' . date('Ymd')
    );
}

// ---------------------------------------------------------------------------

function exportTransactions(): void
{
    global $conn;

    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo   = $_GET['date_to']   ?? null;

    $where  = '';
    $params = [];
    $types  = '';

    if ($dateFrom || $dateTo) {
        $clauses = [];
        if ($dateFrom) { $clauses[] = 't.transaction_date >= ?'; $types .= 's'; $params[] = $dateFrom; }
        if ($dateTo)   { $clauses[] = 't.transaction_date <= ?'; $types .= 's'; $params[] = $dateTo; }
        $where = 'WHERE ' . implode(' AND ', $clauses);
    }

    $sql = "SELECT t.transaction_id, t.transaction_date, tt.type_name AS transaction_type,
                   p.product_name, t.quantity, t.unit_price,
                   (t.quantity * t.unit_price) AS total_amount, t.notes, t.created_at
            FROM transaction t
            JOIN transaction_type tt ON t.transaction_type_id = tt.transaction_type_id
            JOIN product p           ON t.product_id          = p.product_id
            $where
            ORDER BY t.transaction_date DESC, t.transaction_id DESC";

    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $rows = array_map(function (array $r): array {
        $r['date_formatted']    = Formatter::date($r['transaction_date'] ?? '');
        $r['created_at_formatted'] = Formatter::datetime($r['created_at'] ?? '');
        return $r;
    }, $rows);

    $suffix = $dateFrom || $dateTo
        ? '_' . ($dateFrom ?? 'start') . '_to_' . ($dateTo ?? 'end')
        : '_' . date('Ymd');

    CSVHelper::download(
        $rows,
        ['transaction_id', 'date_formatted', 'transaction_type', 'product_name',
         'quantity', 'unit_price', 'total_amount', 'notes', 'created_at_formatted'],
        ['ID', 'Date', 'Type', 'Product', 'Quantity', 'Unit Price (RM)', 'Total (RM)', 'Notes', 'Created At'],
        'transactions_export' . $suffix
    );
}

// ---------------------------------------------------------------------------

function exportCategories(): void
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT c.category_id, c.category_name,
                COUNT(p.product_id)       AS product_count,
                SUM(p.quantity)           AS total_units,
                SUM(p.price * p.quantity) AS inventory_value,
                c.created_at
         FROM category c
         LEFT JOIN product p ON c.category_id = p.category_id
         GROUP BY c.category_id
         ORDER BY c.category_name"
    );
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $rows = array_map(function (array $r): array {
        $r['inventory_value']      = number_format((float)($r['inventory_value'] ?? 0), 2, '.', '');
        $r['created_at_formatted'] = Formatter::datetime($r['created_at'] ?? '');
        return $r;
    }, $rows);

    CSVHelper::download(
        $rows,
        ['category_id', 'category_name', 'product_count', 'total_units', 'inventory_value', 'created_at_formatted'],
        ['ID', 'Category Name', 'Products', 'Total Units', 'Inventory Value (RM)', 'Created At'],
        'categories_export_' . date('Ymd')
    );
}

// ---------------------------------------------------------------------------

function exportLowStock(): void
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT p.product_id, p.product_name, p.quantity, p.low_stock_threshold,
                p.price, c.category_name, s.supplier_name, s.email AS supplier_email, s.phone AS supplier_phone
         FROM product p
         JOIN category c ON p.category_id = c.category_id
         JOIN supplier s ON p.supplier_id = s.supplier_id
         WHERE p.quantity <= p.low_stock_threshold
         ORDER BY p.quantity ASC"
    );
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $rows = array_map(function (array $r): array {
        $qty       = (int)$r['quantity'];
        $threshold = (int)$r['low_stock_threshold'];
        $r['stock_status']    = Formatter::stockLabel($qty, $threshold);
        $r['restock_qty']     = max(0, $threshold * 2 - $qty);
        return $r;
    }, $rows);

    CSVHelper::download(
        $rows,
        ['product_id', 'product_name', 'quantity', 'low_stock_threshold', 'restock_qty',
         'stock_status', 'price', 'category_name', 'supplier_name', 'supplier_email', 'supplier_phone'],
        ['ID', 'Product Name', 'Current Stock', 'Threshold', 'Restock Qty Needed',
         'Status', 'Unit Price (RM)', 'Category', 'Supplier', 'Supplier Email', 'Supplier Phone'],
        'low_stock_report_' . date('Ymd')
    );
}
