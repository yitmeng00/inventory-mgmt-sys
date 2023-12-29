<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        retrieveImgs();
        break;

    default:
        // Unsupported method
        http_response_code(405); // Method Not Allowed
        echo json_encode(array('error_msg' => 'Unsupported HTTP method'));
}

function retrieveImgs()
{
    global $conn;

    $product_id = isset($_GET['product_id']) ? $_GET['product_id'] : null;

    if ($product_id === null) {
        echo json_encode(array(
            'success' => false,
            'error_msg' => 'Missing product_id parameter'
        ));
        return;
    }

    try {
        $stmt = $conn->prepare("SELECT img_id, product_id, img_name, img_path FROM product_img WHERE product_id = ?;");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product_imgs = $result->fetch_all(MYSQLI_ASSOC);

        if ($product_imgs) {
            echo json_encode(array(
                'success' => true,
                'product_imgs' => $product_imgs
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'No images found for the given product_id'
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