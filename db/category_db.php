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
        if (isset($_GET['category_id'])) retrieveCategoryById();
        else retrieveCategories();
        break;
    case 'POST':
        createCategory();
        break;
    case 'PUT':
        updateCategory();
        break;
    case 'DELETE':
        deleteCategory();
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error_msg' => 'Method not allowed']);
}

function retrieveCategories()
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT c.category_id, c.category_name, c.created_at, COUNT(p.product_id) AS product_count
         FROM category c LEFT JOIN product p ON c.category_id = p.category_id
         GROUP BY c.category_id ORDER BY c.category_name"
    );
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'categories' => $categories]);
    $stmt->close();
}

function retrieveCategoryById()
{
    global $conn;
    $id   = (int)$_GET['category_id'];
    $stmt = $conn->prepare("SELECT category_id, category_name FROM category WHERE category_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();
    if ($category) echo json_encode(['success' => true, 'category' => $category]);
    else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error_msg' => 'Category not found']);
    }
    $stmt->close();
}

function createCategory()
{
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $name = htmlspecialchars(trim($data['category_name'] ?? ''), ENT_QUOTES);
    if (!$name) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Category name is required']);
        return;
    }

    $check = $conn->prepare("SELECT category_id FROM category WHERE category_name = ?");
    $check->bind_param('s', $name);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error_msg' => 'Category already exists']);
        return;
    }
    $check->close();

    $stmt = $conn->prepare("INSERT INTO category (category_name) VALUES (?)");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $id = $conn->insert_id;
        echo json_encode(['success' => true, 'message' => 'Category created', 'category' => ['category_id' => $id, 'category_name' => htmlspecialchars_decode($name), 'product_count' => 0, 'created_at' => date('Y-m-d H:i:s')]]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => 'Failed to create category']);
    }
    $stmt->close();
}

function updateCategory()
{
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($data['category_id'] ?? 0);
    $name = htmlspecialchars(trim($data['category_name'] ?? ''), ENT_QUOTES);
    if (!$id || !$name) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Invalid input']);
        return;
    }

    $stmt = $conn->prepare("UPDATE category SET category_name = ? WHERE category_id = ?");
    $stmt->bind_param('si', $name, $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Category updated', 'category' => ['category_id' => $id, 'category_name' => htmlspecialchars_decode($name)]]);
    } else {
        echo json_encode(['success' => false, 'error_msg' => 'No changes made']);
    }
    $stmt->close();
}

function deleteCategory()
{
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($data['category_id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Invalid input']);
        return;
    }

    $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM product WHERE category_id = ?");
    $check->bind_param('i', $id);
    $check->execute();
    $cnt = $check->get_result()->fetch_assoc()['cnt'];
    $check->close();
    if ($cnt > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error_msg' => "Cannot delete: $cnt product(s) are assigned to this category"]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM category WHERE category_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) echo json_encode(['success' => true, 'message' => 'Category deleted']);
    else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error_msg' => 'Category not found']);
    }
    $stmt->close();
}

?>