<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../lib/jwt_helper.php';
require_once 'mysql_conn.php';

JWTHelper::authenticateAPI();

$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['transaction_id'])) retrieveTransactionById();
        else retrieveTransactions();
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
        http_response_code(405);
        echo json_encode(['success' => false, 'error_msg' => 'Method not allowed']);
}

function retrieveTransactions()
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT txn.transaction_id, txn.transaction_code, txn.product_id, p.product_code,
                p.product_name, t.type_id, t.type_name, txn.quantity, txn.unit_price, txn.created
         FROM `transaction` txn
         INNER JOIN product p ON txn.product_id = p.product_id
         INNER JOIN transaction_type t ON txn.type_id = t.type_id
         ORDER BY txn.created DESC"
    );
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as &$r) {
        $r['product_code'] = htmlspecialchars_decode($r['product_code']);
        $r['product_name'] = htmlspecialchars_decode($r['product_name']);
    }
    echo json_encode(['success' => true, 'transactions' => $rows]);
    $stmt->close();
}

function retrieveTransactionById()
{
    global $conn;
    $id   = (int)$_GET['transaction_id'];
    $stmt = $conn->prepare(
        "SELECT txn.transaction_id, txn.transaction_code, txn.product_id, p.product_code,
                p.product_name, t.type_id, t.type_name, txn.quantity, txn.unit_price, txn.created
         FROM `transaction` txn
         INNER JOIN product p ON txn.product_id = p.product_id
         INNER JOIN transaction_type t ON txn.type_id = t.type_id
         WHERE txn.transaction_id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $row['product_code'] = htmlspecialchars_decode($row['product_code']);
        $row['product_name'] = htmlspecialchars_decode($row['product_name']);
        echo json_encode(['success' => true, 'transaction' => $row]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error_msg' => 'Transaction not found']);
    }
    $stmt->close();
}

function createTransaction()
{
    global $conn;
    $productId = (int)($_POST['product_id'] ?? 0);
    $typeId    = (int)($_POST['type_id'] ?? 0);
    $quantity  = (int)($_POST['quantity'] ?? 0);

    if (!$productId || !$typeId || $quantity < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Invalid input data']);
        return;
    }

    // Check sufficient stock for sales
    if ($typeId === 1) {
        $chk = $conn->prepare("SELECT quantity FROM product WHERE product_id = ?");
        $chk->bind_param('i', $productId);
        $chk->execute();
        $stock = (int)($chk->get_result()->fetch_assoc()['quantity'] ?? 0);
        $chk->close();
        if ($stock < $quantity) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error_msg' => "Insufficient stock. Available: $stock"]);
            return;
        }
    }

    try {
        $conn->begin_transaction();

        // Fetch sale price
        $priceStmt = $conn->prepare("SELECT sale_price FROM product WHERE product_id = ?");
        $priceStmt->bind_param('i', $productId);
        $priceStmt->execute();
        $unitPrice = (float)($priceStmt->get_result()->fetch_assoc()['sale_price'] ?? 0);
        $priceStmt->close();

        $code = generateTxnCode($conn);

        $insStmt = $conn->prepare("INSERT INTO `transaction` (transaction_code, product_id, type_id, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
        $insStmt->bind_param('siiid', $code, $productId, $typeId, $quantity, $unitPrice);
        $insStmt->execute();
        $txnId = $conn->insert_id;
        $insStmt->close();

        // Update stock: type 1 = sale (subtract), type 2 = purchase (add)
        $delta = $typeId === 1 ? -$quantity : $quantity;
        $upd   = $conn->prepare("UPDATE product SET quantity = quantity + ? WHERE product_id = ?");
        $upd->bind_param('ii', $delta, $productId);
        $upd->execute();
        $upd->close();

        $conn->commit();

        $selStmt = $conn->prepare(
            "SELECT txn.transaction_id, txn.transaction_code, txn.product_id, p.product_code,
                    p.product_name, t.type_id, t.type_name, txn.quantity, txn.unit_price, txn.created
             FROM `transaction` txn
             INNER JOIN product p ON txn.product_id = p.product_id
             INNER JOIN transaction_type t ON txn.type_id = t.type_id
             WHERE txn.transaction_id = ?"
        );
        $selStmt->bind_param('i', $txnId);
        $selStmt->execute();
        $txnData = $selStmt->get_result()->fetch_assoc();
        $selStmt->close();

        echo json_encode(['success' => true, 'message' => 'Transaction created', 'transaction_data' => $txnData]);
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => $e->getMessage()]);
    }
}

function updateTransaction()
{
    global $conn;
    $data  = json_decode(file_get_contents('php://input'), true);
    $txnId = (int)($data['transaction_id'] ?? 0);
    $typeId    = (int)($data['type_id'] ?? 0);
    $productId = (int)($data['product_id'] ?? 0);
    $quantity  = (int)($data['quantity'] ?? 0);

    if (!$txnId || !$typeId || !$productId || $quantity < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Invalid input']);
        return;
    }

    try {
        $conn->begin_transaction();

        // Reverse old transaction's stock effect
        $oldStmt = $conn->prepare("SELECT product_id, type_id, quantity FROM `transaction` WHERE transaction_id = ?");
        $oldStmt->bind_param('i', $txnId);
        $oldStmt->execute();
        $old = $oldStmt->get_result()->fetch_assoc();
        $oldStmt->close();

        if ($old) {
            $reverseDelta = $old['type_id'] === 1 ? $old['quantity'] : -$old['quantity'];
            $rev = $conn->prepare("UPDATE product SET quantity = quantity + ? WHERE product_id = ?");
            $rev->bind_param('ii', $reverseDelta, $old['product_id']);
            $rev->execute();
            $rev->close();
        }

        // Fetch new unit price
        $priceStmt = $conn->prepare("SELECT sale_price FROM product WHERE product_id = ?");
        $priceStmt->bind_param('i', $productId);
        $priceStmt->execute();
        $unitPrice = (float)($priceStmt->get_result()->fetch_assoc()['sale_price'] ?? 0);
        $priceStmt->close();

        $updStmt = $conn->prepare("UPDATE `transaction` SET product_id=?, type_id=?, quantity=?, unit_price=? WHERE transaction_id=?");
        $updStmt->bind_param('iiidi', $productId, $typeId, $quantity, $unitPrice, $txnId);
        $updStmt->execute();
        $updStmt->close();

        // Apply new stock effect
        $delta = $typeId === 1 ? -$quantity : $quantity;
        $upd   = $conn->prepare("UPDATE product SET quantity = quantity + ? WHERE product_id = ?");
        $upd->bind_param('ii', $delta, $productId);
        $upd->execute();
        $upd->close();

        $conn->commit();

        $selStmt = $conn->prepare(
            "SELECT txn.transaction_id, txn.transaction_code, txn.product_id, p.product_code,
                    p.product_name, t.type_id, t.type_name, txn.quantity, txn.unit_price, txn.created
             FROM `transaction` txn
             INNER JOIN product p ON txn.product_id = p.product_id
             INNER JOIN transaction_type t ON txn.type_id = t.type_id
             WHERE txn.transaction_id = ?"
        );
        $selStmt->bind_param('i', $txnId);
        $selStmt->execute();
        $txnData = $selStmt->get_result()->fetch_assoc();
        $selStmt->close();

        echo json_encode(['success' => true, 'message' => 'Transaction updated', 'transaction_data' => $txnData]);
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => $e->getMessage()]);
    }
}

function deleteTransaction()
{
    global $conn;
    $data  = json_decode(file_get_contents('php://input'), true);
    $txnId = (int)($data['transaction_id'] ?? 0);
    if (!$txnId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Transaction ID required']);
        return;
    }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("SELECT product_id, type_id, quantity FROM `transaction` WHERE transaction_id = ?");
        $stmt->bind_param('i', $txnId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error_msg' => 'Transaction not found']);
            return;
        }

        $del = $conn->prepare("DELETE FROM `transaction` WHERE transaction_id = ?");
        $del->bind_param('i', $txnId);
        $del->execute();
        $del->close();

        // Reverse stock effect
        $delta = $row['type_id'] === 1 ? $row['quantity'] : -$row['quantity'];
        $upd   = $conn->prepare("UPDATE product SET quantity = quantity + ? WHERE product_id = ?");
        $upd->bind_param('ii', $delta, $row['product_id']);
        $upd->execute();
        $upd->close();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Transaction deleted']);
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => $e->getMessage()]);
    }
}

function generateTxnCode(mysqli $conn): string
{
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $code = 'T';
        for ($i = 0; $i < 6; $i++) $code .= $chars[random_int(0, strlen($chars) - 1)];
        $chk = $conn->prepare("SELECT 1 FROM `transaction` WHERE transaction_code = ?");
        $chk->bind_param('s', $code);
        $chk->execute();
        $exists = $chk->get_result()->num_rows > 0;
        $chk->close();
    } while ($exists);
    return $code;
}

?>