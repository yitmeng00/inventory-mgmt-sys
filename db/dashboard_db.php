<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($action) {
            case 'get_overview':
                retrieveOverviewData();
                break;
            case 'get_product_chart':
                retrieveProductChartData();
                break;
            case 'get_purchase_chart':
                retrievePurchaseChartData();
                break;
            case 'get_sale_chart':
                retrieveSaleChartData();
                break;
            case 'get_revenue_profit_chart':
                retrieveRevenueProfitChartData();
                break;
            case 'get_low_stock_chart':
                retrieveLowStockChartData();
                break;
            case 'get_best_selling_chart':
                retrieveBestSellingChartData();
                break;
            default:
                // Unsupported action
                http_response_code(400); // Bad Request
                echo json_encode(array('error_msg' => 'Unsupported action'));
        }
        break;

    default:
        // Unsupported method
        http_response_code(405); // Method Not Allowed
        echo json_encode(array('error_msg' => 'Unsupported HTTP method'));
}

function retrieveOverviewData()
{
    global $conn;

    try {
        // Total available stock
        $totalAvailableStockQuery = "SELECT SUM(quantity) AS total_quantity FROM product;";
        $totalAvailableStockResult = mysqli_query($conn, $totalAvailableStockQuery);
        $totalAvailableStock = mysqli_fetch_assoc($totalAvailableStockResult)['total_quantity'];

        // Low stock product
        $lowStockProductQuery = "SELECT COUNT(product_id) AS low_stock_product FROM product WHERE quantity < 20;";
        $lowStockProductResult = mysqli_query($conn, $lowStockProductQuery);
        $lowStockProductData = mysqli_fetch_assoc($lowStockProductResult);
        $lowStockProductCount = $lowStockProductData['low_stock_product'];

        // Out of stock
        $outOfStockQuery = "SELECT COUNT(product_id) AS out_of_stock_product FROM product WHERE quantity = 0;";
        $outOfStockResult = mysqli_query($conn, $outOfStockQuery);
        $outOfStockProductCount = mysqli_fetch_assoc($outOfStockResult)['out_of_stock_product'];

        // Total Sales
        $totalSalesQuery = "SELECT SUM(quantity * unit_price) AS total_sales FROM `transaction` WHERE type_id = 1;";
        $totalSalesResult = mysqli_query($conn, $totalSalesQuery);
        $totalSales = mysqli_fetch_assoc($totalSalesResult)['total_sales'];

        // Total Purchases
        $totalPurchasesQuery = "SELECT SUM(quantity * unit_price) AS total_purchases FROM `transaction` WHERE type_id = 2;";
        $totalPurchasesResult = mysqli_query($conn, $totalPurchasesQuery);
        $totalPurchases = mysqli_fetch_assoc($totalPurchasesResult)['total_purchases'];

        // Now create the overviewContents array with the fetched data
        $overviewContents = [
            [
                'title' => 'TOTAL AVAILABLE STOCKS',
                'value' => $totalAvailableStock,
            ],
            [
                'title' => 'LOW IN STOCK',
                'value' => $lowStockProductCount,
            ],
            [
                'title' => 'OUT OF STOCK',
                'value' => $outOfStockProductCount,
            ],
            [
                'title' => 'TOTAL PURCHASES',
                'value' => $totalPurchases,
            ],
            [
                'title' => 'TOTAL SALES',
                'value' => $totalSales,
            ],
        ];

        echo json_encode(array(
            'success' => true,
            'overview_contents' => $overviewContents
        ));
    } catch (mysqli_sql_exception $exception) {
        echo json_encode(array(
            'success' => false,
            'error_msg' => $exception->getMessage()
        ));
    }
}

function retrieveProductChartData()
{
    global $conn;

    try {
        $productDataQuery = "SELECT c.category_name, COUNT(p.product_id) AS total_products FROM product p INNER JOIN category c ON p.category_id = c.category_id GROUP BY p.category_id;";

        $productDataResult = mysqli_query($conn, $productDataQuery);

        $productData = [];
        while ($row = mysqli_fetch_assoc($productDataResult)) {
            $productData[] = [
                'category_name' => $row['category_name'],
                'value' => $row['total_products'],
            ];
        }

        echo json_encode(array(
            'success' => true,
            'product_data' => $productData,
        ));
    } catch (mysqli_sql_exception $exception) {
        echo json_encode(array(
            'success' => false,
            'error_msg' => $exception->getMessage(),
        ));
    }
}

function retrievePurchaseChartData()
{
    global $conn;

    try {
        $purchaseDataQuery = "SELECT MONTHNAME(created) AS month, SUM(quantity * unit_price) AS total_purchase FROM `transaction` WHERE type_id = 2 AND YEAR(created) = YEAR(CURDATE()) GROUP BY month ORDER BY month;";

        $purchaseDataResult = mysqli_query($conn, $purchaseDataQuery);

        $purchaseData = [];
        while ($row = mysqli_fetch_assoc($purchaseDataResult)) {
            $purchaseData[] = [
                'month' => $row['month'],
                'purchase' => $row['total_purchase'],
            ];
        }

        echo json_encode(array(
            'success' => true,
            'purchase_data' => $purchaseData,
        ));
    } catch (mysqli_sql_exception $exception) {
        echo json_encode(array(
            'success' => false,
            'error_msg' => $exception->getMessage(),
        ));
    }
}

function retrieveSaleChartData()
{
    global $conn;

    try {
        $saleDataQuery = "SELECT MONTHNAME(created) AS month, SUM(quantity * unit_price) AS total_sale FROM `transaction` WHERE type_id = 1 AND YEAR(created) = YEAR(CURDATE()) GROUP BY month ORDER BY month;";

        $saleDataResult = mysqli_query($conn, $saleDataQuery);

        $saleData = [];
        while ($row = mysqli_fetch_assoc($saleDataResult)) {
            $saleData[] = [
                'month' => $row['month'],
                'sale' => $row['total_sale'],
            ];
        }

        echo json_encode(array(
            'success' => true,
            'sale_data' => $saleData,
        ));
    } catch (mysqli_sql_exception $exception) {
        echo json_encode(array(
            'success' => false,
            'error_msg' => $exception->getMessage(),
        ));
    }
}

function retrieveRevenueProfitChartData()
{
    global $conn;

    try {
        $revenueProfitDataQuery = "SELECT MONTHNAME(t.created) AS month, SUM(CASE WHEN t.type_id = 1 THEN t.unit_price * t.quantity ELSE 0 END) AS total_revenue, SUM(CASE WHEN t.type_id = 1 THEN (t.unit_price - p.cost_price) * t.quantity ELSE 0 END) AS total_profit FROM `transaction` t INNER JOIN product p ON t.product_id = p.product_id WHERE t.type_id = 1 AND YEAR(t.created) = YEAR(CURRENT_DATE) GROUP BY month ORDER BY month;";

        $revenueProfitDataResult = mysqli_query($conn, $revenueProfitDataQuery);

        $revenueProfitData = [];
        while ($row = mysqli_fetch_assoc($revenueProfitDataResult)) {
            $revenueProfitData[] = [
                'month' => $row['month'],
                'revenue' => $row['total_revenue'],
                'profit' => $row['total_profit'],
            ];
        }

        echo json_encode(array(
            'success' => true,
            'revenue_profit_data' => $revenueProfitData,
        ));
    } catch (mysqli_sql_exception $exception) {
        echo json_encode(array(
            'success' => false,
            'error_msg' => $exception->getMessage(),
        ));
    }
}

function retrieveLowStockChartData()
{
    global $conn;

    try {
        $lowStockDataQuery = "SELECT product_name, quantity FROM product WHERE quantity < 20;";

        $lowStockDataResult = mysqli_query($conn, $lowStockDataQuery);

        $lowStockData = [];
        while ($row = mysqli_fetch_assoc($lowStockDataResult)) {
            $lowStockData[] = [
                'product_name' => $row['product_name'],
                'quantity' => $row['quantity'],
            ];
        }

        echo json_encode(array(
            'success' => true,
            'low_stock_data' => $lowStockData,
        ));
    } catch (mysqli_sql_exception $exception) {
        echo json_encode(array(
            'success' => false,
            'error_msg' => $exception->getMessage(),
        ));
    }
}

function retrieveBestSellingChartData()
{
    global $conn;

    try {
        $bestSellingDataQuery = "SELECT p.product_name, SUM(t.quantity) AS total_quantity_sold FROM product p INNER JOIN transaction t ON p.product_id = t.product_id WHERE t.type_id = 1 GROUP BY p.product_id, p.product_name, p.category_id, p.supplier_id, p.product_code, p.description ORDER BY total_quantity_sold DESC LIMIT 6;";

        $bestSellingDataResult = mysqli_query($conn, $bestSellingDataQuery);

        $bestSellingData = [];
        while ($row = mysqli_fetch_assoc($bestSellingDataResult)) {
            $bestSellingData[] = [
                'product_name' => $row['product_name'],
                'quantity_sold' => $row['total_quantity_sold'],
            ];
        }

        echo json_encode(array(
            'success' => true,
            'best_selling_data' => $bestSellingData,
        ));
    } catch (mysqli_sql_exception $exception) {
        echo json_encode(array(
            'success' => false,
            'error_msg' => $exception->getMessage(),
        ));
    }
}

?>