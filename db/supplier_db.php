<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['supplier_id'])) {
            retrieveSupplierById();
        } else {
            retrieveSuppliers();
        }
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
        // Unsupported method
        http_response_code(405); // Method Not Allowed
        echo json_encode(array('error_msg' => 'Unsupported HTTP method'));
}

function retrieveSuppliers()
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT supplier_id, supplier_code, supplier_name, contact_person, contact_no, email, `location` FROM supplier;");
        $stmt->execute();
        $result = $stmt->get_result();
        $suppliers = $result->fetch_all(MYSQLI_ASSOC);

        if ($suppliers) {
            // Decode HTML special characters for specific fields
            foreach ($suppliers as &$supplier) {
                $supplier['supplier_name'] = htmlspecialchars_decode($supplier['supplier_name']);
                $supplier['contact_person'] = htmlspecialchars_decode($supplier['contact_person']);
                $supplier['email'] = htmlspecialchars_decode($supplier['email']);
                $supplier['location'] = htmlspecialchars_decode($supplier['location']);
            }

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

function retrieveSupplierById()
{
    global $conn;

    $supplierId = $_GET['supplier_id'];

    try {
        $stmt = $conn->prepare("SELECT supplier_id, supplier_code, supplier_name, contact_person, contact_no, email, `location` FROM supplier WHERE supplier_id = ?;");
        $stmt->bind_param("i", $supplierId);
        $stmt->execute();
        $result = $stmt->get_result();
        $suppliers = $result->fetch_all(MYSQLI_ASSOC);

        if ($suppliers) {
            // Decode HTML special characters for specific fields
            foreach ($suppliers as &$supplier) {
                $supplier['supplier_name'] = htmlspecialchars_decode($supplier['supplier_name']);
                $supplier['contact_person'] = htmlspecialchars_decode($supplier['contact_person']);
                $supplier['email'] = htmlspecialchars_decode($supplier['email']);
                $supplier['location'] = htmlspecialchars_decode($supplier['location']);
            }

            echo json_encode(array(
                'success' => true,
                'supplier' => $supplier
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

function createSupplier()
{
    global $conn;

    $supplierName = htmlspecialchars($_POST['supplier_name'], ENT_QUOTES);
    $contactPerson = htmlspecialchars($_POST['contact_person'], ENT_QUOTES);
    $contactNo = $_POST['contact_no'];
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES);
    $location = htmlspecialchars($_POST['location'], ENT_QUOTES);

    try {
        $supplierCode = generateSupplierCode();

        // Check if the generated supplier code already exists in the database
        $checkCodeStmt = $conn->prepare("SELECT COUNT(*) FROM `supplier` WHERE supplier_code = ?;");
        $checkCodeStmt->bind_param("s", $supplierCode);
        $checkCodeStmt->execute();
        $checkCodeResult = $checkCodeStmt->get_result();
        $codeExists = ($checkCodeResult->fetch_row()[0] > 0);
        $checkCodeStmt->close();

        // Regenerate code if it already exists
        while ($codeExists) {
            $supplierCode = generateSupplierCode();
            $checkCodeStmt->bind_param("s", $supplierCode);
            $checkCodeStmt->execute();
            $checkCodeResult = $checkCodeStmt->get_result();
            $codeExists = ($checkCodeResult->fetch_row()[0] > 0);
        }

        // Insert supplier data
        $stmt = $conn->prepare("INSERT INTO supplier (supplier_code, supplier_name, contact_person, contact_no, email, `location`) VALUES (?, ?, ?, ?, ?, ?);");
        $stmt->bind_param("ssssss", $supplierCode, $supplierName, $contactPerson, $contactNo, $email, $location);
        $stmt->execute();

        $lastInsertedSupplierId = $conn->insert_id;

        if ($stmt->affected_rows > 0) {
            // Retrieve the newly added supplier data
            $selectStmt = $conn->prepare("SELECT supplier_id, supplier_code, supplier_name, contact_person, contact_no, email, `location` FROM supplier WHERE supplier_id = ?;");
            $selectStmt->bind_param("i", $lastInsertedSupplierId);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            $supplierData = $result->fetch_assoc();

            echo json_encode(array(
                'success' => true,
                'message' => 'Supplier created successfully',
                'supplier_data' => $supplierData
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'Failed to create a new supplier'
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

function updateSupplier()
{
    global $conn;

    // Get data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        http_response_code(400); // Bad Request
        echo json_encode(array('success' => false, 'error_msg' => 'Invalid input data'));
        return;
    }

    $supplierId = $data['supplier_id'];
    $supplierName = $data['supplier_name'];
    $contactPerson = $data['contact_person'];
    $contactNo = $data['contact_no'];
    $email = $data['email'];
    $location = $data['location'];

    try {
        $stmt = $conn->prepare("UPDATE supplier SET supplier_name=?, contact_person=?, contact_no=?, email=?, `location`=? WHERE supplier_id=?;");
        $stmt->bind_param("sssssi", $supplierName, $contactPerson, $contactNo, $email, $location, $supplierId);

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $selectStmt = $conn->prepare("SELECT supplier_id, supplier_code, supplier_name, contact_person, contact_no, email, `location` FROM supplier WHERE supplier_id = ?;");
            $selectStmt->bind_param("i", $supplierId);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            $supplierData = $result->fetch_assoc();

            echo json_encode(array(
                'success' => true,
                'message' => 'Supplier updated successfully',
                'supplier_data' => $supplierData
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'No rows affected. Supplier may not exist or no changes made.'
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

function deleteSupplier()
{
    global $conn;

    // Get data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['supplier_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(array(
            'success' => false,
            'error_msg' => 'Invalid input data'
        ));
        return;
    }

    $supplierId = $data['supplier_id'];

    try {
        $productCount = "";

        // Check if there are linked products
        $stmt_check_products = $conn->prepare("SELECT COUNT(product_id) FROM product WHERE supplier_id = ?");
        $stmt_check_products->bind_param("i", $supplierId);
        $stmt_check_products->execute();
        $stmt_check_products->bind_result($productCount);
        $stmt_check_products->fetch();
        $stmt_check_products->close();

        if ($productCount > 0) {
            // Linked products found, cannot delete supplier
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'Cannot delete supplier. There is product linked with the supplier.'
            ));
            return;
        }

        $stmt_delete_supplier = $conn->prepare("DELETE FROM supplier WHERE supplier_id = ?");
        $stmt_delete_supplier->bind_param("i", $supplierId);
        $stmt_delete_supplier->execute();

        if ($stmt_delete_supplier->affected_rows > 0) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Supplier deleted successfully'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'No rows affected. Supplier may not exist.'
            ));
        }

        $stmt_delete_supplier->close();
    } catch (mysqli_sql_exception $exception) {
        echo json_encode(array(
            'success' => false,
            'error_msg' => $exception->getMessage()
        ));
    }

    $conn->close();
}

function generateSupplierCode()
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $code = 'S';

    for ($i = 0; $i < 6; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $code;
}
