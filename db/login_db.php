<?php

require_once "mysql_conn.php";
$conf = new DBConnection();
$conn = $conf->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        login();
        break;

    default:
        // Unsupported method
        http_response_code(405); // Method Not Allowed
        echo json_encode(array('error_msg' => 'Unsupported HTTP method'));
}

function login()
{
    global $conn;

    // Check if username and password are provided
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        http_response_code(400); // Bad Request
        echo json_encode(array('error_msg' => 'Username and password are required.'));
        return;
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $user_id = "";
    $staff_id = "";
    $db_username = "";
    $db_password = "";
    $first_name = "";
    $last_name = "";
    $designation = "";

    // Query the database to retrieve user information
    $stmt = $conn->prepare("SELECT user_id, staff_id, username, `password`, first_name, last_name, designation FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if the user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $staff_id, $db_username, $db_password, $first_name, $last_name, $designation);
        $stmt->fetch();

        // Verify the hashed password using password_verify
        if (password_verify($password, $db_password)) {
            // Authentication successful

            // Set session variables
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['staff_id'] = $staff_id;

            http_response_code(200); // OK
            echo json_encode(array(
                'success' => true,
                'user_id' => $user_id,
                'username' => $db_username,
                'staff_id' => $staff_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'designation' => $designation
            ));
        } else {
            // Invalid password
            http_response_code(401); // Unauthorized
            echo json_encode(array(
                'error_msg' => 'Invalid password.'
            ));
        }
    } else {
        // User not found
        http_response_code(404); // Not Found
        echo json_encode(array(
            'error_msg' => 'User not found.'
        ));
    }

    $stmt->close();
}
