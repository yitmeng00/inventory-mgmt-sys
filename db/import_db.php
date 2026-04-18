<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../lib/jwt_helper.php';
require_once 'mysql_conn.php';

JWTHelper::authenticateAPI();

$conf = new DBConnection();
$conn = $conf->connect();

$type = $_GET['type'] ?? '';

switch ($type) {
    case 'products':
        importProducts();
        break;
    case 'suppliers':
        importSuppliers();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Unknown import type']);
}

function importProducts()
{
    global $conn;

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'CSV file required']);
        return;
    }

    $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
    if (!$handle) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => 'Cannot read file']);
        return;
    }

    $headers = array_map('trim', fgetcsv($handle));
    $required = ['product_code', 'product_name', 'category_name', 'supplier_name', 'cost_price', 'sale_price'];
    foreach ($required as $col) {
        if (!in_array($col, $headers)) {
            fclose($handle);
            http_response_code(400);
            echo json_encode(['success' => false, 'error_msg' => "Missing column: $col"]);
            return;
        }
    }

    $results = ['imported' => 0, 'skipped' => 0, 'errors' => []];
    $row = 1;

    while (($fields = fgetcsv($handle)) !== false) {
        $row++;
        if (count($fields) < count($headers)) {
            $results['errors'][] = "Row $row: insufficient columns";
            $results['skipped']++;
            continue;
        }

        $data = array_combine($headers, array_map('trim', $fields));

        // Lookup category
        $catStmt = $conn->prepare("SELECT category_id FROM category WHERE category_name = ?");
        $catStmt->bind_param('s', $data['category_name']);
        $catStmt->execute();
        $cat = $catStmt->get_result()->fetch_assoc();
        $catStmt->close();

        if (!$cat) {
            // Auto-create category
            $insC = $conn->prepare("INSERT INTO category (category_name) VALUES (?)");
            $insC->bind_param('s', $data['category_name']);
            $insC->execute();
            $categoryId = $conn->insert_id;
            $insC->close();
        } else {
            $categoryId = $cat['category_id'];
        }

        // Lookup supplier
        $supStmt = $conn->prepare("SELECT supplier_id FROM supplier WHERE supplier_name = ?");
        $supStmt->bind_param('s', $data['supplier_name']);
        $supStmt->execute();
        $sup = $supStmt->get_result()->fetch_assoc();
        $supStmt->close();

        if (!$sup) {
            $results['errors'][] = "Row $row: Supplier '{$data['supplier_name']}' not found";
            $results['skipped']++;
            continue;
        }
        $supplierId = $sup['supplier_id'];

        // Check duplicate product_code
        $dupChk = $conn->prepare("SELECT product_id FROM product WHERE product_code = ?");
        $dupChk->bind_param('s', $data['product_code']);
        $dupChk->execute();
        if ($dupChk->get_result()->num_rows > 0) {
            $results['errors'][] = "Row $row: SKU '{$data['product_code']}' already exists";
            $results['skipped']++;
            $dupChk->close();
            continue;
        }
        $dupChk->close();

        $productName = htmlspecialchars($data['product_name'], ENT_QUOTES);
        $productCode = htmlspecialchars($data['product_code'], ENT_QUOTES);
        $description = htmlspecialchars($data['description'] ?? '', ENT_QUOTES);
        $costPrice   = (float)$data['cost_price'];
        $salePrice   = (float)$data['sale_price'];

        try {
            $conn->begin_transaction();

            $insP = $conn->prepare("INSERT INTO product (product_name, category_id, supplier_id, product_code, description, cost_price, sale_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insP->bind_param('siissdd', $productName, $categoryId, $supplierId, $productCode, $description, $costPrice, $salePrice);
            $insP->execute();
            $productId = $conn->insert_id;
            $insP->close();

            $insPH = $conn->prepare("INSERT INTO price_history (product_id, cost_price, sale_price, start_date) VALUES (?, ?, ?, NOW())");
            $insPH->bind_param('idd', $productId, $costPrice, $salePrice);
            $insPH->execute();
            $insPH->close();

            $conn->commit();
            $results['imported']++;
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            $results['errors'][] = "Row $row: " . $e->getMessage();
            $results['skipped']++;
        }
    }

    fclose($handle);
    echo json_encode(['success' => true, 'results' => $results]);
}

function importSuppliers()
{
    global $conn;

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'CSV file required']);
        return;
    }

    $handle  = fopen($_FILES['csv_file']['tmp_name'], 'r');
    $headers = array_map('trim', fgetcsv($handle));
    $required = ['supplier_name', 'contact_person', 'contact_no', 'email'];
    foreach ($required as $col) {
        if (!in_array($col, $headers)) {
            fclose($handle);
            http_response_code(400);
            echo json_encode(['success' => false, 'error_msg' => "Missing column: $col"]);
            return;
        }
    }

    $results = ['imported' => 0, 'skipped' => 0, 'errors' => []];
    $row = 1;

    while (($fields = fgetcsv($handle)) !== false) {
        $row++;
        $data         = array_combine($headers, array_map('trim', $fields));
        $supplierName = htmlspecialchars($data['supplier_name'] ?? '', ENT_QUOTES);
        $contactPerson = htmlspecialchars($data['contact_person'] ?? '', ENT_QUOTES);
        $contactNo    = $data['contact_no'] ?? '';
        $email        = $data['email'] ?? '';
        $location     = htmlspecialchars($data['location'] ?? '', ENT_QUOTES);

        if (!$supplierName || !$contactNo || !$email) {
            $results['errors'][] = "Row $row: missing required fields";
            $results['skipped']++;
            continue;
        }

        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code  = 'S';
        for ($i = 0; $i < 6; $i++) $code .= $chars[random_int(0, strlen($chars) - 1)];

        $stmt = $conn->prepare("INSERT INTO supplier (supplier_code, supplier_name, contact_person, contact_no, email, location) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $code, $supplierName, $contactPerson, $contactNo, $email, $location);
        try {
            $stmt->execute();
            $results['imported']++;
        } catch (mysqli_sql_exception $e) {
            $results['errors'][] = "Row $row: " . $e->getMessage();
            $results['skipped']++;
        }
        $stmt->close();
    }

    fclose($handle);
    echo json_encode(['success' => true, 'results' => $results]);
}

?>