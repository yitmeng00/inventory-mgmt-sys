<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        retrieveTransactions();
        break;

    case 'POST':
        createTransaction();
        break;

    case 'PUT':
        updateTransaction();
        break;

    case 'DELETE':
        deleteTransaction();
        break;

    default:
        // Unsupported method
        http_response_code(405); // Method Not Allowed
        echo json_encode(array('error_msg' => 'Unsupported HTTP method'));
}

function retrieveTransactions()
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT transaction_id, txn.product_id, p.product_code, p.product_name, t.type_id, t.type_name, txn.quantity, unit_price, txn.created FROM transaction txn INNER JOIN product p ON txn.product_id = p.product_id INNER JOIN transaction_type t ON txn.type_id = t.type_id;");
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);

        if ($transactions) {
            echo json_encode(array(
                'success' => true,
                'transactions' => $transactions
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => "Transaction does not exist"
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

function createTransaction()
{
    global $conn;

    $productId = $_POST['product_id'];
    $typeId = $_POST['type_id'];
    $quantity = $_POST['quantity'];

    try {
        // Fetch unit price from the product table based on product_id
        $fetchPriceStmt = $conn->prepare("SELECT price FROM product WHERE product_id = ?;");
        $fetchPriceStmt->bind_param("i", $productId);
        $fetchPriceStmt->execute();
        $fetchPriceResult = $fetchPriceStmt->get_result();

        // Check if the product_id is valid
        if ($fetchPriceResult->num_rows > 0) {
            $row = $fetchPriceResult->fetch_assoc();
            $unitPrice = $row['price'];

            // Insert transaction data
            $insertStmt = $conn->prepare("INSERT INTO transaction (product_id, type_id, quantity, unit_price) VALUES (?, ?, ?, ?);");
            $insertStmt->bind_param("iiid", $productId, $typeId, $quantity, $unitPrice);
            $insertStmt->execute();

            $lastInsertedTransactionId = $conn->insert_id;

            if ($insertStmt->affected_rows > 0) {
                // Update product table based on type_id
                if ($typeId == 1) {
                    // If type_id is 1, subtract the quantity from the product table
                    $updateStmt = $conn->prepare("UPDATE product SET quantity = quantity - ? WHERE product_id = ?;");
                    $updateStmt->bind_param("ii", $quantity, $productId);
                    $updateStmt->execute();
                    $updateStmt->close();
                } elseif ($typeId == 2) {
                    // If type_id is 2, add the quantity to the product table
                    $updateStmt = $conn->prepare("UPDATE product SET quantity = quantity + ? WHERE product_id = ?;");
                    $updateStmt->bind_param("ii", $quantity, $productId);
                    $updateStmt->execute();
                    $updateStmt->close();
                }

                // Retrieve the newly added transaction data
                $selectStmt = $conn->prepare("SELECT transaction_id, txn.product_id, p.product_code, p.product_name, t.type_id, t.type_name, txn.quantity, unit_price, txn.created FROM transaction txn INNER JOIN product p ON txn.product_id = p.product_id INNER JOIN transaction_type t ON txn.type_id = t.type_id WHERE transaction_id = ?;");
                $selectStmt->bind_param("i", $lastInsertedTransactionId);
                $selectStmt->execute();
                $result = $selectStmt->get_result();
                $transactionData = $result->fetch_assoc();

                echo json_encode(array(
                    'success' => true,
                    'message' => 'Transaction created successfully',
                    'transaction_data' => $transactionData
                ));
            } else {
                echo json_encode(array(
                    'success' => false,
                    'error_msg' => 'Failed to insert transaction data'
                ));
            }
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'Invalid product_id'
            ));
        }
    } catch (mysqli_sql_exception $exception) {
        echo json_encode(array(
            'success' => false,
            'error_msg' => $exception->getMessage()
        ));
    }

    $fetchPriceStmt->close();
    $insertStmt->close();
    $selectStmt->close();
    $conn->close();
}

function updateTransaction()
{
    global $conn;

    // Get data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        http_response_code(400); // Bad Request
        echo json_encode(array('success' => false, 'error_msg' => 'Invalid input data'));
        return;
    }

    $transactionId = $data['transaction_id'];
    $typeId = $data['type_id'];
    $productId = $data['product_id'];
    $quantity = $data['quantity'];

    try {
        $stmt = $conn->prepare("UPDATE `transaction` SET product_id=?, type_id=?, quantity=? WHERE transaction_id=?;");
        $stmt->bind_param("iiii", $productId, $typeId, $quantity, $transactionId);

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $selectStmt = $conn->prepare("SELECT transaction_id, txn.product_id, p.product_code, p.product_name, t.type_id, t.type_name, txn.quantity, unit_price, txn.created FROM transaction txn INNER JOIN product p ON txn.product_id = p.product_id INNER JOIN transaction_type t ON txn.type_id = t.type_id WHERE transaction_id = ?;");
            $selectStmt->bind_param("i", $transactionId);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            $transactionData = $result->fetch_assoc();

            echo json_encode(array(
                'success' => true,
                'message' => 'Product updated successfully',
                'transaction_data' => $transactionData
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'No rows affected. Transaction may not exist or no changes made.'
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

function deleteTransaction()
{
    global $conn;

    // Get data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['transaction_id']) || !isset($data['product_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(array(
            'success' => false,
            'error_msg' => 'Invalid input data'
        ));
        return;
    }

    $transactionId = $data['transaction_id'];
    $productId = $data['product_id'];

    try {
        $typeId = "";
        $quantity = "";
        // Get type_id and quantity from the transaction before deleting it
        $stmt = $conn->prepare("SELECT type_id, quantity FROM `transaction` WHERE transaction_id = ?;");
        $stmt->bind_param("i", $transactionId);
        $stmt->execute();
        $stmt->bind_result($typeId, $quantity);

        if ($stmt->fetch()) {
            // Delete from transaction table
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM `transaction` WHERE transaction_id = ?;");
            $stmt->bind_param("i", $transactionId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Transaction deleted successfully'
                ));

                // Update product table based on type_id
                if ($typeId == 1) {
                    // If type_id is 1, add the quantity to the product table
                    $updateStmt = $conn->prepare("UPDATE `product` SET quantity = quantity + ? WHERE product_id = ?;");
                    $updateStmt->bind_param("ii", $quantity, $productId);
                    $updateStmt->execute();
                } elseif ($typeId == 2) {
                    // If type_id is 2, subtract the quantity from the product table
                    $updateStmt = $conn->prepare("UPDATE `product` SET quantity = quantity - ? WHERE product_id = ?;");
                    $updateStmt->bind_param("ii", $quantity, $productId);
                    $updateStmt->execute();
                }

                $updateStmt->close();
            } else {
                echo json_encode(array(
                    'success' => false,
                    'error_msg' => 'No rows affected. Transaction may not exist.'
                ));
            }
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'Transaction not found'
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
