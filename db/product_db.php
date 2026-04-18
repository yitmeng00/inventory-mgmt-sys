<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../lib/jwt_helper.php';
require_once 'mysql_conn.php';

$authUser = JWTHelper::authenticateAPI();

$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['product_id'])) retrieveProductById();
        else retrieveProducts();
        break;
    case 'POST':
        createProduct();
        break;
    case 'PUT':
        updateProduct();
        break;
    case 'DELETE':
        deleteProduct();
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error_msg' => 'Method not allowed']);
}

function retrieveProducts()
{
    global $conn;
    try {
        $stmt = $conn->prepare(
            "SELECT p.product_id, p.product_code, p.product_name, p.description,
                    p.cost_price, p.sale_price, p.quantity, p.img_path,
                    c.category_id, c.category_name,
                    s.supplier_id, s.supplier_name
             FROM product p
             INNER JOIN category c ON p.category_id = c.category_id
             INNER JOIN supplier s ON p.supplier_id = s.supplier_id
             ORDER BY p.product_name"
        );
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($products as &$p) {
            $p['product_name'] = htmlspecialchars_decode($p['product_name']);
            $p['product_code'] = htmlspecialchars_decode($p['product_code']);
            $p['description']  = htmlspecialchars_decode($p['description'] ?? '');
        }
        echo json_encode(['success' => true, 'products' => $products]);
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => $e->getMessage()]);
    }
}

function retrieveProductById()
{
    global $conn;
    $id   = (int)$_GET['product_id'];
    $stmt = $conn->prepare(
        "SELECT p.product_id, p.product_code, p.product_name, p.description,
                p.cost_price, p.sale_price, p.quantity, p.img_path,
                c.category_id, c.category_name,
                s.supplier_id, s.supplier_name
         FROM product p
         INNER JOIN category c ON p.category_id = c.category_id
         INNER JOIN supplier s ON p.supplier_id = s.supplier_id
         WHERE p.product_id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if ($product) {
        $product['product_name'] = htmlspecialchars_decode($product['product_name']);
        $product['product_code'] = htmlspecialchars_decode($product['product_code']);
        $product['description']  = htmlspecialchars_decode($product['description'] ?? '');
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error_msg' => 'Product not found']);
    }
    $stmt->close();
}

function createProduct()
{
    global $conn;

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Product image is required']);
        return;
    }

    $productName = htmlspecialchars(trim($_POST['product_name'] ?? ''), ENT_QUOTES);
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $supplierId  = (int)($_POST['supplier_id'] ?? 0);
    $productCode = htmlspecialchars(trim($_POST['product_code'] ?? ''), ENT_QUOTES);
    $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES);
    $costPrice   = (float)($_POST['cost_price'] ?? 0);
    $salePrice   = (float)($_POST['sale_price'] ?? 0);

    if (!$productName || !$categoryId || !$supplierId || !$productCode) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Required fields missing']);
        return;
    }

    $ext     = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Invalid image type. Allowed: jpg, png, gif, webp']);
        return;
    }

    $newFilename = uniqid('img_', true) . '.' . $ext;
    $uploadPath  = $uploadDir . $newFilename;
    $imgPath     = 'uploads/' . $newFilename;

    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => 'File upload failed']);
        return;
    }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare(
            "INSERT INTO product (product_name, category_id, supplier_id, product_code, description, cost_price, sale_price, img_path)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('siissdds', $productName, $categoryId, $supplierId, $productCode, $description, $costPrice, $salePrice, $imgPath);
        $stmt->execute();
        $productId = $conn->insert_id;
        $stmt->close();

        $conn->commit();

        $selStmt = $conn->prepare(
            "SELECT p.product_id, p.product_code, p.product_name, p.description,
                    p.cost_price, p.sale_price, p.quantity, p.img_path,
                    c.category_id, c.category_name, s.supplier_id, s.supplier_name
             FROM product p
             INNER JOIN category c ON p.category_id = c.category_id
             INNER JOIN supplier s ON p.supplier_id = s.supplier_id
             WHERE p.product_id = ?"
        );
        $selStmt->bind_param('i', $productId);
        $selStmt->execute();
        $productData = $selStmt->get_result()->fetch_assoc();
        $selStmt->close();

        echo json_encode(['success' => true, 'message' => 'Product created', 'product_data' => $productData]);
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        @unlink($uploadPath);
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => $e->getMessage()]);
    }
}

function updateProduct()
{
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Invalid input']);
        return;
    }

    $productId   = (int)($data['product_id'] ?? 0);
    $productName = htmlspecialchars(trim($data['product_name'] ?? ''), ENT_QUOTES);
    $categoryId  = (int)($data['category_id'] ?? 0);
    $supplierId  = (int)($data['supplier_id'] ?? 0);
    $productCode = htmlspecialchars(trim($data['product_code'] ?? ''), ENT_QUOTES);
    $description = htmlspecialchars(trim($data['description'] ?? ''), ENT_QUOTES);
    $costPrice   = (float)($data['cost_price'] ?? 0);
    $salePrice   = (float)($data['sale_price'] ?? 0);

    if (!$productId || !$productName || !$categoryId || !$supplierId || !$productCode) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Required fields missing']);
        return;
    }

    try {
        $stmt = $conn->prepare(
            "UPDATE product SET product_name=?, category_id=?, supplier_id=?, product_code=?, description=?, cost_price=?, sale_price=?
             WHERE product_id=?"
        );
        $stmt->bind_param('siissddi', $productName, $categoryId, $supplierId, $productCode, $description, $costPrice, $salePrice, $productId);
        $stmt->execute();
        $stmt->close();

        $selStmt = $conn->prepare(
            "SELECT p.product_id, p.product_code, p.product_name, p.description,
                    p.cost_price, p.sale_price, p.quantity, p.img_path,
                    c.category_id, c.category_name, s.supplier_id, s.supplier_name
             FROM product p
             INNER JOIN category c ON p.category_id = c.category_id
             INNER JOIN supplier s ON p.supplier_id = s.supplier_id
             WHERE p.product_id = ?"
        );
        $selStmt->bind_param('i', $productId);
        $selStmt->execute();
        $productData = $selStmt->get_result()->fetch_assoc();
        $selStmt->close();

        echo json_encode(['success' => true, 'message' => 'Product updated', 'product_data' => $productData]);
    } catch (mysqli_sql_exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => $e->getMessage()]);
    }
}

function deleteProduct()
{
    global $conn;
    $data      = json_decode(file_get_contents('php://input'), true);
    $productId = (int)($data['product_id'] ?? 0);
    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Product ID required']);
        return;
    }

    try {
        $conn->begin_transaction();

        // Get image path to delete file
        $imgStmt = $conn->prepare("SELECT img_path FROM product WHERE product_id = ?");
        $imgStmt->bind_param('i', $productId);
        $imgStmt->execute();
        $imgRow = $imgStmt->get_result()->fetch_assoc();
        $imgStmt->close();

        $conn->query("DELETE FROM `transaction` WHERE product_id = $productId");

        $del = $conn->prepare("DELETE FROM product WHERE product_id = ?");
        $del->bind_param('i', $productId);
        $del->execute();
        $del->close();

        $conn->commit();

        // Remove uploaded image file if it exists
        if (!empty($imgRow['img_path'])) {
            @unlink(__DIR__ . '/../' . $imgRow['img_path']);
        }

        echo json_encode(['success' => true, 'message' => 'Product deleted']);
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => $e->getMessage()]);
    }
}

?>