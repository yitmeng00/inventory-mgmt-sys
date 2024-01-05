<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['product_id'])) {
            retrieveProductById();
        } else {
            retrieveProducts();
        }
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
        // Unsupported method
        http_response_code(405); // Method Not Allowed
        echo json_encode(array('error_msg' => 'Unsupported HTTP method'));
}

function retrieveProducts()
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT product_id, product_name, c.category_id, c.category_name, s.supplier_id, s.supplier_name, product_code, `description`, cost_price, sale_price, quantity FROM product p INNER JOIN category c ON p.category_id = c.category_id INNER JOIN supplier s ON p.supplier_id = s.supplier_id;");
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);

        if ($products) {
            // Decode HTML special characters for specific fields
            foreach ($products as &$product) {
                $product['product_name'] = htmlspecialchars_decode($product['product_name']);
                $product['product_code'] = htmlspecialchars_decode($product['product_code']);
                $product['description'] = htmlspecialchars_decode($product['description']);
            }

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

function retrieveProductById()
{
    global $conn;

    $productId = $_GET['product_id'];

    try {
        $stmt = $conn->prepare("SELECT p.product_id, product_name, c.category_id, c.category_name, s.supplier_id, s.supplier_name, product_code, `description`, cost_price, sale_price, quantity, i.img_path FROM product p INNER JOIN category c ON p.category_id = c.category_id INNER JOIN supplier s ON p.supplier_id = s.supplier_id INNER JOIN product_img i ON p.product_id = i.product_id WHERE p.product_id = ?;");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if ($product) {
            // Decode HTML special characters for specific fields
            $product['product_name'] = htmlspecialchars_decode($product['product_name']);
            $product['product_code'] = htmlspecialchars_decode($product['product_code']);
            $product['description'] = htmlspecialchars_decode($product['description']);

            echo json_encode(array(
                'success' => true,
                'product' => $product
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'Product not found'
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

function createProduct()
{
    global $conn;
    // Check if the "uploads" directory exists, if not, create it
    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir) && !is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Check if file is uploaded
    if (!isset($_FILES['attachment'])) {
        http_response_code(400); // Bad Request
        echo json_encode(array('success' => false, 'error_msg' => 'File not provided'));
        return;
    }

    $productName = htmlspecialchars($_POST['product_name'], ENT_QUOTES);
    $categoryId = $_POST['category_id'];
    $supplierId = $_POST['supplier_id'];
    $productCode = htmlspecialchars($_POST['product_code'], ENT_QUOTES);
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES);
    $costPrice = $_POST['cost_price'];
    $salePrice = $_POST['sale_price'];

    // Handle file upload
    $uploadFile = $uploadDir . basename($_FILES['attachment']['name']);

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadFile)) {
        // File upload successful, continue with database insertion
        try {
            // Insert product data
            $stmt = $conn->prepare("INSERT INTO product (product_name, category_id, supplier_id, product_code, `description`, cost_price, sale_price) VALUES (?, ?, ?, ?, ?, ?, ?);");
            $stmt->bind_param("siissdd", $productName, $categoryId, $supplierId, $productCode, $description, $costPrice, $salePrice);
            $stmt->execute();

            $lastInsertedProductId = $conn->insert_id;

            if ($stmt->affected_rows > 0) {
                // Insert image data
                $imgName = $_FILES['attachment']['name'];
                $imgPath = substr($uploadFile, 3);
                $imgStmt = $conn->prepare("INSERT INTO product_img (product_id, img_name, img_path) VALUES (?, ?, ?);");
                $imgStmt->bind_param("iss", $lastInsertedProductId, $imgName, $imgPath);
                $imgStmt->execute();

                if ($imgStmt->affected_rows > 0) {
                    // Retrieve the newly added product data
                    $selectStmt = $conn->prepare("SELECT product_id, product_name, c.category_id, c.category_name, s.supplier_id, s.supplier_name, product_code, `description`, cost_price, sale_price, quantity FROM product p INNER JOIN category c ON p.category_id = c.category_id INNER JOIN supplier s ON p.supplier_id = s.supplier_id WHERE product_id = ?;");
                    $selectStmt->bind_param("i", $lastInsertedProductId);
                    $selectStmt->execute();
                    $result = $selectStmt->get_result();
                    $productData = $result->fetch_assoc();

                    echo json_encode(array(
                        'success' => true,
                        'message' => 'Product and image created successfully',
                        'product_data' => $productData
                    ));
                } else {
                    echo json_encode(array(
                        'success' => false,
                        'error_msg' => 'Failed to insert image data'
                    ));
                }
            } else {
                echo json_encode(array(
                    'success' => false,
                    'error_msg' => 'Failed to create a new product'
                ));
            }
        } catch (mysqli_sql_exception $exception) {
            echo json_encode(array(
                'success' => false,
                'error_msg' => $exception->getMessage()
            ));
        }
    } else {
        // File upload failed
        echo json_encode(array(
            'success' => false,
            'error_msg' => 'Failed to upload file'
        ));
    }

    $stmt->close();
    $imgStmt->close();
    $conn->close();
}

function updateProduct()
{
    global $conn;

    // Get data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        http_response_code(400); // Bad Request
        echo json_encode(array('success' => false, 'error_msg' => 'Invalid input data'));
        return;
    }

    $productId = $data['product_id'];
    $productName = $data['product_name'];
    $categoryId = $data['category_id'];
    $supplierId = $data['supplier_id'];
    $productCode = $data['product_code'];
    $description = $data['description'];
    $costPrice = $data['cost_price'];
    $salePrice = $data['sale_price'];

    try {
        $stmt = $conn->prepare("UPDATE product SET product_name=?, category_id=?, supplier_id=?, product_code=?, `description`=?, cost_price=?, sale_price=? WHERE product_id=?;");
        $stmt->bind_param("sssssddi", $productName, $categoryId, $supplierId, $productCode, $description, $costPrice, $salePrice, $productId);

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $selectStmt = $conn->prepare("SELECT product_id, product_name, c.category_id, c.category_name, s.supplier_id, s.supplier_name, product_code, `description`, cost_price, sale_price, quantity FROM product p INNER JOIN category c ON p.category_id = c.category_id INNER JOIN supplier s ON p.supplier_id = s.supplier_id WHERE product_id = ?;");
            $selectStmt->bind_param("i", $productId);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            $productData = $result->fetch_assoc();

            echo json_encode(array(
                'success' => true,
                'message' => 'Product updated successfully',
                'product_data' => $productData
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'No rows affected. Product may not exist or no changes made.'
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

function deleteProduct()
{
    global $conn;

    // Get data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['product_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(array(
            'success' => false,
            'error_msg' => 'Invalid input data'
        ));
        return;
    }

    $productId = $data['product_id'];

    try {
        // Delete from product_img table
        $stmtImg = $conn->prepare("DELETE FROM product_img WHERE product_id = ?;");
        $stmtImg->bind_param("i", $productId);
        $stmtImg->execute();
        $stmtImg->close();

        // Delete from transaction table
        $stmtTransaction = $conn->prepare("DELETE FROM `transaction` WHERE product_id = ?;");
        $stmtTransaction->bind_param("i", $productId);
        $stmtTransaction->execute();
        $stmtTransaction->close();

        // Delete from product table
        $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?;");
        $stmt->bind_param("i", $productId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Product, associated images and associated transactions deleted successfully'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'No rows affected. Product may not exist.'
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
