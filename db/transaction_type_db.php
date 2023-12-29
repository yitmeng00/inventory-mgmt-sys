<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        retrieveTransactionTypes();
        break;

    default:
        // Unsupported method
        http_response_code(405); // Method Not Allowed
        echo json_encode(array('error_msg' => 'Unsupported HTTP method'));
}

function retrieveTransactionTypes()
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT type_id, type_name FROM transaction_type;");
        $stmt->execute();
        $result = $stmt->get_result();
        $txn_types = $result->fetch_all(MYSQLI_ASSOC);

        if ($txn_types) {
            echo json_encode(array(
                'success' => true,
                'txn_types' => $txn_types
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => "Transaction type does not exist"
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
