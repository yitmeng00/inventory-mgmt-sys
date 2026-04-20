<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../lib/jwt_helper.php';
require_once __DIR__ . '/../lib/Formatter.php';
require_once __DIR__ . '/../lib/StockHelper.php';
require_once 'mysql_conn.php';

JWTHelper::authenticateAPI();

$conf = new DBConnection();
$conn = $conf->connect();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error_msg' => 'Method not allowed']);
    exit;
}

$type = $_GET['type'] ?? 'overview';

switch ($type) {
    case 'overview':        reportOverview();       break;
    case 'stock':           reportStock();          break;
    case 'sales':           reportSales();          break;
    case 'purchases':       reportPurchases();      break;
    case 'top_products':    reportTopProducts();    break;
    case 'low_stock':       reportLowStock();       break;
    case 'category_value':  reportCategoryValue();  break;
    case 'supplier_spend':  reportSupplierSpend();  break;
    case 'monthly_trend':   reportMonthlyTrend();   break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Unknown report type']);
}

// ---------------------------------------------------------------------------
// Report: overall KPI overview
// ---------------------------------------------------------------------------
function reportOverview(): void
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT
            COUNT(*) AS total_products,
            SUM(quantity) AS total_units,
            SUM(price * quantity) AS inventory_value,
            COUNT(CASE WHEN quantity = 0 THEN 1 END) AS out_of_stock_count,
            COUNT(CASE WHEN quantity > 0 AND quantity <= low_stock_threshold THEN 1 END) AS low_stock_count
         FROM product"
    );
    $stmt->execute();
    $inv = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT
            SUM(CASE WHEN tt.type_name = 'sale'     THEN t.quantity * t.unit_price ELSE 0 END) AS total_sales,
            SUM(CASE WHEN tt.type_name = 'purchase' THEN t.quantity * t.unit_price ELSE 0 END) AS total_purchases,
            COUNT(*) AS total_transactions
         FROM transaction t
         JOIN transaction_type tt ON t.transaction_type_id = tt.transaction_type_id"
    );
    $stmt->execute();
    $txn = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo json_encode([
        'success' => true,
        'report'  => [
            'total_products'          => (int)$inv['total_products'],
            'total_units'             => (int)($inv['total_units'] ?? 0),
            'inventory_value'         => (float)($inv['inventory_value'] ?? 0),
            'inventory_value_formatted' => Formatter::currency($inv['inventory_value'] ?? 0),
            'out_of_stock_count'      => (int)$inv['out_of_stock_count'],
            'low_stock_count'         => (int)$inv['low_stock_count'],
            'total_sales'             => (float)($txn['total_sales'] ?? 0),
            'total_sales_formatted'   => Formatter::currency($txn['total_sales'] ?? 0),
            'total_purchases'         => (float)($txn['total_purchases'] ?? 0),
            'total_purchases_formatted' => Formatter::currency($txn['total_purchases'] ?? 0),
            'total_transactions'      => (int)$txn['total_transactions'],
            'net_revenue'             => (float)($txn['total_sales'] ?? 0) - (float)($txn['total_purchases'] ?? 0),
            'net_revenue_formatted'   => Formatter::currency(
                (float)($txn['total_sales'] ?? 0) - (float)($txn['total_purchases'] ?? 0)
            ),
        ],
    ]);
}

// ---------------------------------------------------------------------------
// Report: full stock list with status
// ---------------------------------------------------------------------------
function reportStock(): void
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT p.product_id, p.product_name, p.quantity, p.low_stock_threshold,
                p.price, c.category_name, s.supplier_name
         FROM product p
         JOIN category c  ON p.category_id  = c.category_id
         JOIN supplier s  ON p.supplier_id  = s.supplier_id
         ORDER BY p.quantity ASC"
    );
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $rows = array_map(function (array $p): array {
        $threshold = (int)($p['low_stock_threshold'] ?? 10);
        return [
            'product_id'          => (int)$p['product_id'],
            'product_name'        => $p['product_name'],
            'quantity'            => (int)$p['quantity'],
            'low_stock_threshold' => $threshold,
            'price'               => (float)$p['price'],
            'price_formatted'     => Formatter::currency($p['price']),
            'stock_value'         => (float)$p['price'] * (int)$p['quantity'],
            'stock_value_formatted' => Formatter::currency((float)$p['price'] * (int)$p['quantity']),
            'status'              => StockHelper::status((int)$p['quantity'], $threshold),
            'status_label'        => Formatter::stockLabel((int)$p['quantity'], $threshold),
            'category_name'       => $p['category_name'],
            'supplier_name'       => $p['supplier_name'],
        ];
    }, $products);

    $summary = StockHelper::summary($products);

    echo json_encode(['success' => true, 'products' => $rows, 'summary' => $summary]);
}

// ---------------------------------------------------------------------------
// Report: sales transactions with optional date range
// ---------------------------------------------------------------------------
function reportSales(): void
{
    global $conn;

    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo   = $_GET['date_to']   ?? null;

    $where  = "WHERE tt.type_name = 'sale'";
    $params = [];
    $types  = '';

    if ($dateFrom) { $where .= ' AND t.transaction_date >= ?'; $types .= 's'; $params[] = $dateFrom; }
    if ($dateTo)   { $where .= ' AND t.transaction_date <= ?'; $types .= 's'; $params[] = $dateTo; }

    $sql = "SELECT t.transaction_id, t.transaction_date, t.quantity, t.unit_price,
                   (t.quantity * t.unit_price) AS total_amount,
                   p.product_name, p.product_id
            FROM transaction t
            JOIN transaction_type tt ON t.transaction_type_id = tt.transaction_type_id
            JOIN product p           ON t.product_id          = p.product_id
            $where
            ORDER BY t.transaction_date DESC";

    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $total = array_sum(array_column($rows, 'total_amount'));

    $formatted = array_map(fn($r) => array_merge($r, [
        'unit_price_formatted'  => Formatter::currency($r['unit_price']),
        'total_amount_formatted'=> Formatter::currency($r['total_amount']),
        'date_formatted'        => Formatter::date($r['transaction_date']),
    ]), $rows);

    echo json_encode([
        'success'         => true,
        'transactions'    => $formatted,
        'total_amount'    => (float)$total,
        'total_formatted' => Formatter::currency($total),
        'count'           => count($rows),
    ]);
}

// ---------------------------------------------------------------------------
// Report: purchase transactions with optional date range
// ---------------------------------------------------------------------------
function reportPurchases(): void
{
    global $conn;

    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo   = $_GET['date_to']   ?? null;

    $where  = "WHERE tt.type_name = 'purchase'";
    $params = [];
    $types  = '';

    if ($dateFrom) { $where .= ' AND t.transaction_date >= ?'; $types .= 's'; $params[] = $dateFrom; }
    if ($dateTo)   { $where .= ' AND t.transaction_date <= ?'; $types .= 's'; $params[] = $dateTo; }

    $sql = "SELECT t.transaction_id, t.transaction_date, t.quantity, t.unit_price,
                   (t.quantity * t.unit_price) AS total_amount,
                   p.product_name, p.product_id, s.supplier_name
            FROM transaction t
            JOIN transaction_type tt ON t.transaction_type_id = tt.transaction_type_id
            JOIN product p           ON t.product_id          = p.product_id
            JOIN supplier s          ON p.supplier_id         = s.supplier_id
            $where
            ORDER BY t.transaction_date DESC";

    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $total = array_sum(array_column($rows, 'total_amount'));

    $formatted = array_map(fn($r) => array_merge($r, [
        'unit_price_formatted'  => Formatter::currency($r['unit_price']),
        'total_amount_formatted'=> Formatter::currency($r['total_amount']),
        'date_formatted'        => Formatter::date($r['transaction_date']),
    ]), $rows);

    echo json_encode([
        'success'         => true,
        'transactions'    => $formatted,
        'total_amount'    => (float)$total,
        'total_formatted' => Formatter::currency($total),
        'count'           => count($rows),
    ]);
}

// ---------------------------------------------------------------------------
// Report: top N best-selling products by units sold
// ---------------------------------------------------------------------------
function reportTopProducts(): void
{
    global $conn;

    $limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));

    $stmt = $conn->prepare(
        "SELECT p.product_id, p.product_name, c.category_name,
                SUM(CASE WHEN tt.type_name = 'sale' THEN t.quantity ELSE 0 END) AS units_sold,
                SUM(CASE WHEN tt.type_name = 'sale' THEN t.quantity * t.unit_price ELSE 0 END) AS revenue
         FROM product p
         JOIN category c ON p.category_id = c.category_id
         LEFT JOIN transaction t       ON p.product_id          = t.product_id
         LEFT JOIN transaction_type tt ON t.transaction_type_id = tt.transaction_type_id
         GROUP BY p.product_id
         ORDER BY units_sold DESC
         LIMIT ?"
    );
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $formatted = array_map(fn($r) => array_merge($r, [
        'units_sold'        => (int)$r['units_sold'],
        'revenue'           => (float)$r['revenue'],
        'revenue_formatted' => Formatter::currency($r['revenue']),
    ]), $rows);

    echo json_encode(['success' => true, 'products' => $formatted]);
}

// ---------------------------------------------------------------------------
// Report: products at or below their low-stock threshold
// ---------------------------------------------------------------------------
function reportLowStock(): void
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT p.product_id, p.product_name, p.quantity, p.low_stock_threshold,
                c.category_name, s.supplier_name, s.email AS supplier_email, s.phone AS supplier_phone
         FROM product p
         JOIN category c ON p.category_id = c.category_id
         JOIN supplier s ON p.supplier_id = s.supplier_id
         WHERE p.quantity <= p.low_stock_threshold
         ORDER BY p.quantity ASC"
    );
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $formatted = array_map(fn($r) => array_merge($r, [
        'quantity'            => (int)$r['quantity'],
        'low_stock_threshold' => (int)$r['low_stock_threshold'],
        'restock_needed'      => StockHelper::restockQuantity((int)$r['quantity'], (int)$r['low_stock_threshold'] * 2),
        'status'              => StockHelper::status((int)$r['quantity'], (int)$r['low_stock_threshold']),
        'status_label'        => Formatter::stockLabel((int)$r['quantity'], (int)$r['low_stock_threshold']),
    ]), $rows);

    echo json_encode(['success' => true, 'products' => $formatted, 'count' => count($rows)]);
}

// ---------------------------------------------------------------------------
// Report: inventory value broken down by category
// ---------------------------------------------------------------------------
function reportCategoryValue(): void
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT c.category_id, c.category_name,
                COUNT(p.product_id)       AS product_count,
                SUM(p.quantity)           AS total_units,
                SUM(p.price * p.quantity) AS inventory_value
         FROM category c
         LEFT JOIN product p ON c.category_id = p.category_id
         GROUP BY c.category_id
         ORDER BY inventory_value DESC"
    );
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $grandTotal = array_sum(array_column($rows, 'inventory_value'));

    $formatted = array_map(function (array $r) use ($grandTotal): array {
        $value = (float)$r['inventory_value'];
        return [
            'category_id'              => (int)$r['category_id'],
            'category_name'            => $r['category_name'],
            'product_count'            => (int)$r['product_count'],
            'total_units'              => (int)($r['total_units'] ?? 0),
            'inventory_value'          => $value,
            'inventory_value_formatted'=> Formatter::currency($value),
            'share_percent'            => $grandTotal > 0 ? round(($value / $grandTotal) * 100, 2) : 0,
        ];
    }, $rows);

    echo json_encode([
        'success'            => true,
        'categories'         => $formatted,
        'grand_total'        => (float)$grandTotal,
        'grand_total_formatted' => Formatter::currency($grandTotal),
    ]);
}

// ---------------------------------------------------------------------------
// Report: spend per supplier (purchase totals)
// ---------------------------------------------------------------------------
function reportSupplierSpend(): void
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT s.supplier_id, s.supplier_name, s.email,
                COUNT(DISTINCT p.product_id)  AS product_count,
                SUM(t.quantity * t.unit_price) AS total_spend
         FROM supplier s
         LEFT JOIN product p           ON s.supplier_id = p.supplier_id
         LEFT JOIN transaction t       ON p.product_id  = t.product_id
         LEFT JOIN transaction_type tt ON t.transaction_type_id = tt.transaction_type_id
                                      AND tt.type_name = 'purchase'
         GROUP BY s.supplier_id
         ORDER BY total_spend DESC"
    );
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $formatted = array_map(fn($r) => array_merge($r, [
        'product_count'      => (int)$r['product_count'],
        'total_spend'        => (float)($r['total_spend'] ?? 0),
        'spend_formatted'    => Formatter::currency($r['total_spend'] ?? 0),
    ]), $rows);

    echo json_encode(['success' => true, 'suppliers' => $formatted]);
}

// ---------------------------------------------------------------------------
// Report: monthly transaction trend for the last N months
// ---------------------------------------------------------------------------
function reportMonthlyTrend(): void
{
    global $conn;

    $months = min(24, max(1, (int)($_GET['months'] ?? 12)));

    $stmt = $conn->prepare(
        "SELECT DATE_FORMAT(t.transaction_date, '%Y-%m') AS month,
                SUM(CASE WHEN tt.type_name = 'sale'     THEN t.quantity * t.unit_price ELSE 0 END) AS sales,
                SUM(CASE WHEN tt.type_name = 'purchase' THEN t.quantity * t.unit_price ELSE 0 END) AS purchases,
                COUNT(*) AS transaction_count
         FROM transaction t
         JOIN transaction_type tt ON t.transaction_type_id = tt.transaction_type_id
         WHERE t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
         GROUP BY month
         ORDER BY month ASC"
    );
    $stmt->bind_param('i', $months);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $formatted = array_map(fn($r) => [
        'month'               => $r['month'],
        'sales'               => (float)$r['sales'],
        'purchases'           => (float)$r['purchases'],
        'transaction_count'   => (int)$r['transaction_count'],
        'net'                 => (float)$r['sales'] - (float)$r['purchases'],
        'sales_formatted'     => Formatter::currency($r['sales']),
        'purchases_formatted' => Formatter::currency($r['purchases']),
    ], $rows);

    echo json_encode(['success' => true, 'trend' => $formatted, 'months' => $months]);
}
