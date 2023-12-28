<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        retrieveProducts();
        break;
    default:
        // Unsupported method
        http_response_code(405); // Method Not Allowed
        echo json_encode(array('error_msg' => 'Unsupported HTTP method'));
}

function retrieveProducts()
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT product_id, product_name, category_id, supplier_id, product_code, `description`, price, quantity FROM product");
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);

        if ($products) {
            echo json_encode(array(
                'success' => true,
                'products' => $products
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => "Product does not exist"
            ));
        }
    } catch (mysqli_sql_exception $exception) {
        echo json_encode(array(
            'success' => false,
            'error_msg' => $exception->getMessage()
        ));
    }

    $stmt->close();
    $conn->close();
}

?>