<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../lib/jwt_helper.php';
require_once 'mysql_conn.php';

$method = $_SERVER['REQUEST_METHOD'];

// Profile update and password change can be done by the authenticated user themselves;
// All other operations (create/delete/list) require admin.
$authUser = JWTHelper::authenticateAPI();

$conf = new DBConnection();
$conn = $conf->connect();

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        if ($action === 'me') {
            retrieveSelf($authUser);
            break;
        }
        if (isset($_GET['user_id'])) {
            retrieveUserById((int)$_GET['user_id'], $authUser);
            break;
        }
        if (($authUser->role ?? 'staff') !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Admin required']);
            exit;
        }
        retrieveUsers();
        break;
    case 'POST':
        $action = $_GET['action'] ?? 'create';
        if ($action === 'change_password') changePassword($authUser);
        elseif ($action === 'update_profile') updateProfile($authUser);
        else {
            if (($authUser->role ?? 'staff') !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Admin required']);
                exit;
            }
            createUser();
        }
        break;
    case 'PUT':
        if (($authUser->role ?? 'staff') !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Admin required']);
            exit;
        }
        updateUser();
        break;
    case 'DELETE':
        if (($authUser->role ?? 'staff') !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Admin required']);
            exit;
        }
        deleteUser($authUser);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error_msg' => 'Method not allowed']);
}

function retrieveSelf(object $authUser)
{
    global $conn;
    $userId = (int)$authUser->user_id;
    $stmt   = $conn->prepare("SELECT user_id, staff_id, username, first_name, last_name, email, designation, role, status, created_at FROM user WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    echo json_encode($user ? ['success' => true, 'user' => $user] : ['success' => false, 'error_msg' => 'Not found']);
}

function retrieveUserById(int $userId, object $authUser)
{
    global $conn;
    if (($authUser->role ?? 'staff') !== 'admin' && (int)$authUser->user_id !== $userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Forbidden']);
        return;
    }
    $stmt = $conn->prepare("SELECT user_id, staff_id, username, first_name, last_name, email, designation, role, status, created_at FROM user WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    echo json_encode($user ? ['success' => true, 'user' => $user] : ['success' => false, 'error_msg' => 'User not found']);
}

function retrieveUsers()
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT user_id, staff_id, username, first_name, last_name, email, designation, role, status, created_at, last_login
         FROM user ORDER BY first_name, last_name"
    );
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'users' => $users]);
    $stmt->close();
}

function createUser()
{
    global $conn;
    $data        = json_decode(file_get_contents('php://input'), true);
    $firstName   = htmlspecialchars(trim($data['first_name'] ?? ''), ENT_QUOTES);
    $lastName    = htmlspecialchars(trim($data['last_name'] ?? ''), ENT_QUOTES);
    $username    = trim($data['username'] ?? '');
    $staffId     = trim($data['staff_id'] ?? '');
    $email       = trim($data['email'] ?? '');
    $designation = trim($data['designation'] ?? '');
    $role        = in_array($data['role'] ?? '', ['admin', 'staff']) ? $data['role'] : 'staff';
    $password    = $data['password'] ?? '';

    if (!$firstName || !$lastName || !$username || !$staffId || !$password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Required fields missing']);
        return;
    }
    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Password must be at least 8 characters']);
        return;
    }

    // Check uniqueness
    $chk = $conn->prepare("SELECT user_id FROM user WHERE username = ? OR staff_id = ?");
    $chk->bind_param('ss', $username, $staffId);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error_msg' => 'Username or Staff ID already exists']);
        return;
    }
    $chk->close();

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare(
        "INSERT INTO user (staff_id, username, password, first_name, last_name, email, designation, role, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')"
    );
    $stmt->bind_param('ssssssss', $staffId, $username, $hash, $firstName, $lastName, $email, $designation, $role);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $id = $conn->insert_id;
        echo json_encode(['success' => true, 'message' => 'User created', 'user_id' => $id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error_msg' => 'Failed to create user']);
    }
    $stmt->close();
}

function updateUser()
{
    global $conn;
    $data   = json_decode(file_get_contents('php://input'), true);
    $userId = (int)($data['user_id'] ?? 0);

    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'User ID required']);
        return;
    }

    // Status-only toggle (deactivate/activate button) — do not touch other fields
    if (isset($data['status']) && !isset($data['first_name'])) {
        $status = in_array($data['status'], ['active', 'inactive']) ? $data['status'] : 'active';
        $stmt   = $conn->prepare("UPDATE user SET status=? WHERE user_id=?");
        $stmt->bind_param('si', $status, $userId);
        $stmt->execute();
        $stmt->close();
        $label = $status === 'active' ? 'activated' : 'deactivated';
        echo json_encode(['success' => true, 'message' => "Account $label"]);
        return;
    }

    $firstName   = htmlspecialchars(trim($data['first_name'] ?? ''), ENT_QUOTES);
    $lastName    = htmlspecialchars(trim($data['last_name'] ?? ''), ENT_QUOTES);
    $username    = trim($data['username'] ?? '');
    $staffId     = trim($data['staff_id'] ?? '');
    $email       = trim($data['email'] ?? '');
    $designation = trim($data['designation'] ?? '');
    $role        = in_array($data['role'] ?? '', ['admin', 'staff']) ? $data['role'] : 'staff';
    $status      = in_array($data['status'] ?? '', ['active', 'inactive']) ? $data['status'] : 'active';
    $password    = $data['password'] ?? '';

    if ($password) {
        if (strlen($password) < 8) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error_msg' => 'Password must be at least 8 characters']);
            return;
        }
        $hash  = password_hash($password, PASSWORD_BCRYPT);
        $stmt  = $conn->prepare("UPDATE user SET first_name=?, last_name=?, username=?, staff_id=?, email=?, designation=?, role=?, status=?, password=? WHERE user_id=?");
        $stmt->bind_param('sssssssssi', $firstName, $lastName, $username, $staffId, $email, $designation, $role, $status, $hash, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE user SET first_name=?, last_name=?, username=?, staff_id=?, email=?, designation=?, role=?, status=? WHERE user_id=?");
        $stmt->bind_param('ssssssssi', $firstName, $lastName, $username, $staffId, $email, $designation, $role, $status, $userId);
    }

    $stmt->execute();
    $stmt->close();

    $selStmt = $conn->prepare("SELECT user_id, staff_id, username, first_name, last_name, email, designation, role, status, created_at FROM user WHERE user_id = ?");
    $selStmt->bind_param('i', $userId);
    $selStmt->execute();
    $userData = $selStmt->get_result()->fetch_assoc();
    $selStmt->close();

    echo json_encode(['success' => true, 'message' => 'User updated', 'user' => $userData]);
}

function deleteUser(object $authUser)
{
    global $conn;
    $data   = json_decode(file_get_contents('php://input'), true);
    $userId = (int)($data['user_id'] ?? 0);
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'User ID required']);
        return;
    }
    if ($userId === (int)$authUser->user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'You cannot delete your own account']);
        return;
    }
    $stmt = $conn->prepare("UPDATE user SET status = 'inactive' WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) echo json_encode(['success' => true, 'message' => 'Account deactivated']);
    else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error_msg' => 'User not found']);
    }
    $stmt->close();
}

function changePassword(object $authUser)
{
    global $conn;
    $data        = json_decode(file_get_contents('php://input'), true);
    $currentPw   = $data['current_password'] ?? '';
    $newPw       = $data['new_password'] ?? '';
    $userId      = (int)$authUser->user_id;

    if (!$currentPw || !$newPw) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Both passwords are required']);
        return;
    }
    if (strlen($newPw) < 8) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'New password must be at least 8 characters']);
        return;
    }

    $stmt = $conn->prepare("SELECT password FROM user WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($currentPw, $row['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error_msg' => 'Current password is incorrect']);
        return;
    }

    $hash   = password_hash($newPw, PASSWORD_BCRYPT);
    $updStmt = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
    $updStmt->bind_param('si', $hash, $userId);
    $updStmt->execute();
    $updStmt->close();

    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
}

function updateProfile(object $authUser)
{
    global $conn;
    $data        = json_decode(file_get_contents('php://input'), true);
    $userId      = (int)$authUser->user_id;
    $firstName   = htmlspecialchars(trim($data['first_name'] ?? ''), ENT_QUOTES);
    $lastName    = htmlspecialchars(trim($data['last_name'] ?? ''), ENT_QUOTES);
    $email       = trim($data['email'] ?? '');
    $designation = trim($data['designation'] ?? '');

    if (!$firstName || !$lastName) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error_msg' => 'Name fields are required']);
        return;
    }

    $stmt = $conn->prepare("UPDATE user SET first_name=?, last_name=?, email=?, designation=? WHERE user_id=?");
    $stmt->bind_param('ssssi', $firstName, $lastName, $email, $designation, $userId);
    $stmt->execute();
    $stmt->close();

    // Re-issue JWT so the updated name/designation is reflected on next page load
    $token = JWTHelper::generate([
        'user_id'     => $userId,
        'staff_id'    => $authUser->staff_id,
        'username'    => $authUser->username,
        'first_name'  => $firstName,
        'last_name'   => $lastName,
        'designation' => $designation,
        'role'        => $authUser->role,
    ]);
    JWTHelper::setTokenCookie($token);

    echo json_encode(['success' => true, 'message' => 'Profile updated']);
}

?>