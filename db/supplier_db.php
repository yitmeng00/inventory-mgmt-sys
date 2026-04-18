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
        if (isset($_GET['supplier_id'])) retrieveSupplierById();
        else retrieveSuppliers();
        break;
    case 'POST':
        createSupplier();
        break;
    case 'PUT':
        updateSupplier();
        break;
    case 'DELETE':
        deleteSupplier();
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error_msg' => 'Method not allowed']);
}

function retrieveSuppliers()
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT s.supplier_id, s.supplier_code, s.supplier_name, s.contact_person, s.contact_no,
                s.email, s.location, COUNT(p.product_id) AS product_count
         FROM supplier s LEFT JOIN product p ON s.supplier_id = p.supplier_id
         GROUP BY s.supplier_id ORDER BY s.supplier_name"
    );
    $stmt->execute();
    $suppliers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($suppliers as &$s) {
        $s['supplier_name']   = htmlspecialchars_decode($s['supplier_name']);
        $s['contact_person']  = htmlspecialchars_decode($s['contact_person']);
        $s['email']           = htmlspecialchars_decode($s['email']);
        $s['location']        = htmlspecialchars_decode($s['location']);
    }
    echo json_encode(['success' => true, 'suppliers' => $suppliers]);
    $stmt->close();
}

function retrieveSupplierById()
{
    global $conn;
    $id   = (int)$_GET['supplier_id'];
    $stmt = $conn->prepare("SELECT supplier_id, supplier_code, supplier_name, contact_person, contact_no, email, location FROM supplier WHERE supplier_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $supplier = $stmt->get_result()->fetch_assoc();
    if ($supplier) {
        $supplier['supplier_name']  = htmlspecialchars_decode($supplier['supplier_name']);
        $supplier['contact_person'] = htmlspecialchars_decode($supplier['contact_person']);
        $supplier['email']          = htmlspecialchars_decode($supplier['email']);
        $supplier['location']       = htmlspecialchars_decode($supplier['location']);
        echo json_encode(['success' => true, 'supplier' => $supplier]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error_msg' => 'Supplier not found']);
    }
    $stmt->close();
}

function createSupplier()
{
    global $conn;
    $data           = json_decode(file_get_contents('php://input'), true) ?? [];
    $supplierName   = htmlspecialchars(trim($data['supplier_name'] ?? ''), ENT_QUOTES);
    $contactPerson  = htmlspecialchars(trim($data['contact_person'] ?? ''), ENT_QUOTES);
    $contactNo      = trim($data['contact_no'] ?? '');
    $email          = htmlspecialchars(trim($data['email'] ?? ''), ENT_QUOTES);
    $location       = htmlspecialchars(trim($data['location'] ?? ''), ENT_QUOTES);

    if (!$supplierName || !$contactPerson || !$contactNo || !$email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Required fields missing']);
        return;
    }

    $supplierCode = generateUniqueCode($conn, 'supplier', 'supplier_code', 'S', 6);

    $stmt = $conn->prepare("INSERT INTO supplier (supplier_code, supplier_name, contact_person, contact_no, email, location) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssss', $supplierCode, $supplierName, $contactPerson, $contactNo, $email, $location);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $id      = $conn->insert_id;
        $selStmt = $conn->prepare("SELECT supplier_id, supplier_code, supplier_name, contact_person, contact_no, email, location FROM supplier WHERE supplier_id = ?");
        $selStmt->bind_param('i', $id);
        $selStmt->execute();
        $supplierData = $selStmt->get_result()->fetch_assoc();
        $selStmt->close();
        echo json_encode(['success' => true, 'message' => 'Supplier created', 'supplier_data' => $supplierData]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => 'Failed to create supplier']);
    }
    $stmt->close();
}

function updateSupplier()
{
    global $conn;
    $data          = json_decode(file_get_contents('php://input'), true);
    $supplierId    = (int)($data['supplier_id'] ?? 0);
    $supplierName  = htmlspecialchars(trim($data['supplier_name'] ?? ''), ENT_QUOTES);
    $contactPerson = htmlspecialchars(trim($data['contact_person'] ?? ''), ENT_QUOTES);
    $contactNo     = trim($data['contact_no'] ?? '');
    $email         = htmlspecialchars(trim($data['email'] ?? ''), ENT_QUOTES);
    $location      = htmlspecialchars(trim($data['location'] ?? ''), ENT_QUOTES);

    if (!$supplierId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Supplier ID required']);
        return;
    }

    $stmt = $conn->prepare("UPDATE supplier SET supplier_name=?, contact_person=?, contact_no=?, email=?, location=? WHERE supplier_id=?");
    $stmt->bind_param('sssssi', $supplierName, $contactPerson, $contactNo, $email, $location, $supplierId);
    $stmt->execute();
    $stmt->close();

    $selStmt = $conn->prepare("SELECT supplier_id, supplier_code, supplier_name, contact_person, contact_no, email, location FROM supplier WHERE supplier_id = ?");
    $selStmt->bind_param('i', $supplierId);
    $selStmt->execute();
    $supplierData = $selStmt->get_result()->fetch_assoc();
    $selStmt->close();

    echo json_encode(['success' => true, 'message' => 'Supplier updated', 'supplier_data' => $supplierData]);
}

function deleteSupplier()
{
    global $conn;
    $data       = json_decode(file_get_contents('php://input'), true);
    $supplierId = (int)($data['supplier_id'] ?? 0);
    if (!$supplierId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Supplier ID required']);
        return;
    }

    $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM product WHERE supplier_id = ?");
    $check->bind_param('i', $supplierId);
    $check->execute();
    $cnt = $check->get_result()->fetch_assoc()['cnt'];
    $check->close();

    if ($cnt > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error_msg' => "Cannot delete: $cnt product(s) linked to this supplier"]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM supplier WHERE supplier_id = ?");
    $stmt->bind_param('i', $supplierId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) echo json_encode(['success' => true, 'message' => 'Supplier deleted']);
    else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error_msg' => 'Supplier not found']);
    }
    $stmt->close();
}

function generateUniqueCode(mysqli $conn, string $table, string $col, string $prefix, int $len): string
{
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $code = $prefix;
        for ($i = 0; $i < $len; $i++) $code .= $chars[random_int(0, strlen($chars) - 1)];
        $check = $conn->prepare("SELECT 1 FROM `$table` WHERE `$col` = ?");
        $check->bind_param('s', $code);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();
    } while ($exists);
    return $code;
}

?>