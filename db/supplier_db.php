<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        retrieveSuppliers();
        break;

    default:
        // Unsupported method
        http_response_code(405); // Method Not Allowed
        echo json_encode(array('error_msg' => 'Unsupported HTTP method'));
}

function retrieveSuppliers()
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT supplier_id, supplier_name, contact_person, contact_no, email, `location` FROM supplier;");
        $stmt->execute();
        $result = $stmt->get_result();
        $suppliers = $result->fetch_all(MYSQLI_ASSOC);

        if ($suppliers) {
            echo json_encode(array(
                'success' => true,
                'suppliers' => $suppliers
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => "Supplier does not exist"
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
