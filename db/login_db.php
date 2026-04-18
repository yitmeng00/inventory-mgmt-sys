<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../lib/jwt_helper.php';
require_once 'mysql_conn.php';

$conf = new DBConnection();
$conn = $conf->connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error_msg' => 'Method not allowed']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error_msg' => 'Username and password are required.']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT user_id, staff_id, username, password, first_name, last_name, designation, role, status
     FROM user WHERE username = ? LIMIT 1"
);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error_msg' => 'Invalid username or password.']);
    exit;
}

$user = $result->fetch_assoc();

if ($user['status'] === 'inactive') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error_msg' => 'Your account has been deactivated. Contact an administrator.']);
    exit;
}

if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error_msg' => 'Invalid username or password.']);
    exit;
}

// Update last_login
$conn->query("UPDATE user SET last_login = NOW() WHERE user_id = " . (int)$user['user_id']);

// Generate JWT
$token = JWTHelper::generate([
    'user_id'     => (int)$user['user_id'],
    'staff_id'    => $user['staff_id'],
    'username'    => $user['username'],
    'first_name'  => $user['first_name'],
    'last_name'   => $user['last_name'],
    'designation' => $user['designation'],
    'role'        => $user['role'] ?? 'staff',
]);

JWTHelper::setTokenCookie($token);

echo json_encode([
    'success'    => true,
    'user_id'    => $user['user_id'],
    'username'   => $user['username'],
    'staff_id'   => $user['staff_id'],
    'first_name' => $user['first_name'],
    'last_name'  => $user['last_name'],
    'role'       => $user['role'] ?? 'staff',
]);

$stmt->close();
$conn->close();

?>