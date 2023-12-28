<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        retrieveCategories();
        break;

    default:
        // Unsupported method
        http_response_code(405); // Method Not Allowed
        echo json_encode(array('error_msg' => 'Unsupported HTTP method'));
}

function retrieveCategories()
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT category_id, category_name FROM category;");
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);

        if ($categories) {
            echo json_encode(array(
                'success' => true,
                'categories' => $categories
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => "Category does not exist"
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
