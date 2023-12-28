<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        retrieveProducts();
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
        $stmt = $conn->prepare("SELECT product_id, product_name, category_id, supplier_id, product_code, `description`, price, quantity FROM product;");
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);

        if ($products) {
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

    $productName = $_POST['product_name'];
    $categoryId = $_POST['category_id'];
    $supplierId = $_POST['supplier_id'];
    $productCode = $_POST['product_code'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Handle file upload
    $uploadFile = $uploadDir . basename($_FILES['attachment']['name']);

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadFile)) {
        // File upload successful, continue with database insertion
        try {
            // Insert product data
            $stmt = $conn->prepare("INSERT INTO product (product_name, category_id, supplier_id, product_code, `description`, price, quantity) VALUES (?, ?, ?, ?, ?, ?, ?);");
            $stmt->bind_param("siissdi", $productName, $categoryId, $supplierId, $productCode, $description, $price, $quantity);
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
                    echo json_encode(array(
                        'success' => true,
                        'message' => 'Product and image created successfully'
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
    $price = $data['price'];
    $quantity = $data['quantity'];

    try {
        $stmt = $conn->prepare("UPDATE product SET product_name=?, category_id=?, supplier_id=?, product_code=?, `description`=?, price=?, quantity=? WHERE product_id=?;");
        $stmt->bind_param("sssssdii", $productName, $categoryId, $supplierId, $productCode, $description, $price, $quantity, $productId);

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Product updated successfully'
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
        $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?;");
        $stmt->bind_param("i", $productId);

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Product deleted successfully'
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

?>