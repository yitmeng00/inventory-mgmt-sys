<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        retrieveSuppliers();
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

function createSupplier()
{
    global $conn;

    $supplierName = $_POST['supplier_name'];
    $contactPerson = $_POST['contact_person'];
    $contactNo = $_POST['contact_no'];
    $email = $_POST['email'];
    $location = $_POST['location'];

    try {
        // Insert supplier data
        $stmt = $conn->prepare("INSERT INTO supplier (supplier_name, contact_person, contact_no, email, `location`) VALUES (?, ?, ?, ?, ?);");
        $stmt->bind_param("sssss", $supplierName, $contactPerson, $contactNo, $email, $location);
        $stmt->execute();

        $lastInsertedSupplierId = $conn->insert_id;

        if ($stmt->affected_rows > 0) {
            // Retrieve the newly added supplier data
            $selectStmt = $conn->prepare("SELECT supplier_id, supplier_name, contact_person, contact_no, email, `location` FROM supplier WHERE supplier_id = ?;");
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
            $selectStmt = $conn->prepare("SELECT supplier_id, supplier_name, contact_person, contact_no, email, `location` FROM supplier WHERE supplier_id = ?;");
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
        // Delete from supplier table
        $stmt = $conn->prepare("DELETE FROM supplier WHERE supplier_id = ?;");
        $stmt->bind_param("i", $supplierId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Supplier and associated images deleted successfully'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'error_msg' => 'No rows affected. Supplier may not exist.'
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
