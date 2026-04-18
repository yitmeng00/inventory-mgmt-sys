<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../lib/jwt_helper.php';
require_once 'mysql_conn.php';

JWTHelper::authenticateAPI();

$conf = new DBConnection();
$conn = $conf->connect();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_overview':
        getOverview();
        break;
    case 'get_product_chart':
        getProductChart();
        break;
    case 'get_purchase_chart':
        getPurchaseChart();
        break;
    case 'get_sale_chart':
        getSaleChart();
        break;
    case 'get_revenue_profit_chart':
        getRevenueProfitChart();
        break;
    case 'get_low_stock_chart':
        getLowStockChart();
        break;
    case 'get_best_selling_chart':
        getBestSellingChart();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Unknown action']);
}

function getOverview()
{
    global $conn;
    $data = [];

    $queries = [
        'total_stock'    => "SELECT COALESCE(SUM(quantity), 0) AS v FROM product",
        'low_stock'      => "SELECT COUNT(*) AS v FROM product WHERE quantity > 0 AND quantity < 20",
        'out_of_stock'   => "SELECT COUNT(*) AS v FROM product WHERE quantity = 0",
        'total_sales'    => "SELECT COALESCE(SUM(quantity * unit_price), 0) AS v FROM `transaction` WHERE type_id = 1",
        'total_purchases' => "SELECT COALESCE(SUM(quantity * unit_price), 0) AS v FROM `transaction` WHERE type_id = 2",
    ];

    foreach ($queries as $key => $sql) {
        $r = $conn->query($sql);
        $data[$key] = $r->fetch_assoc()['v'];
    }

    echo json_encode(['success' => true, 'data' => $data]);
}

function getProductChart()
{
    global $conn;
    $result = $conn->query(
        "SELECT c.category_name, COUNT(p.product_id) AS total
         FROM product p INNER JOIN category c ON p.category_id = c.category_id
         GROUP BY c.category_id ORDER BY total DESC"
    );
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success' => true, 'data' => $rows]);
}

function getPurchaseChart()
{
    global $conn;
    $result = $conn->query(
        "SELECT MONTH(created) AS month_num, MONTHNAME(created) AS month,
                SUM(quantity * unit_price) AS total
         FROM `transaction` WHERE type_id = 2 AND YEAR(created) = YEAR(CURDATE())
         GROUP BY MONTH(created) ORDER BY month_num"
    );
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = ['month' => $row['month'], 'value' => (float)$row['total']];
    echo json_encode(['success' => true, 'data' => $rows]);
}

function getSaleChart()
{
    global $conn;
    $result = $conn->query(
        "SELECT MONTH(created) AS month_num, MONTHNAME(created) AS month,
                SUM(quantity * unit_price) AS total
         FROM `transaction` WHERE type_id = 1 AND YEAR(created) = YEAR(CURDATE())
         GROUP BY MONTH(created) ORDER BY month_num"
    );
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = ['month' => $row['month'], 'value' => (float)$row['total']];
    echo json_encode(['success' => true, 'data' => $rows]);
}

function getRevenueProfitChart()
{
    global $conn;
    $result = $conn->query(
        "SELECT MONTH(t.created) AS month_num, MONTHNAME(t.created) AS month,
                SUM(t.unit_price * t.quantity) AS revenue,
                SUM((t.unit_price - p.cost_price) * t.quantity) AS profit
         FROM `transaction` t
         INNER JOIN product p ON t.product_id = p.product_id
         WHERE t.type_id = 1
           AND YEAR(t.created) = YEAR(CURRENT_DATE)
         GROUP BY MONTH(t.created) ORDER BY month_num"
    );
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = ['month' => $row['month'], 'revenue' => (float)$row['revenue'], 'profit' => (float)$row['profit']];
    }
    echo json_encode(['success' => true, 'data' => $rows]);
}

function getLowStockChart()
{
    global $conn;
    $result = $conn->query("SELECT product_name, quantity FROM product WHERE quantity > 0 AND quantity < 20 ORDER BY quantity ASC LIMIT 10");
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = ['label' => htmlspecialchars_decode($row['product_name']), 'value' => (int)$row['quantity']];
    }
    echo json_encode(['success' => true, 'data' => $rows]);
}

function getBestSellingChart()
{
    global $conn;
    $result = $conn->query(
        "SELECT p.product_name, SUM(t.quantity) AS total_sold
         FROM product p INNER JOIN `transaction` t ON p.product_id = t.product_id
         WHERE t.type_id = 1
         GROUP BY p.product_id ORDER BY total_sold DESC LIMIT 8"
    );
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = ['label' => htmlspecialchars_decode($row['product_name']), 'value' => (int)$row['total_sold']];
    }
    echo json_encode(['success' => true, 'data' => $rows]);
}

?>