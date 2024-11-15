<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
function db_connect() {
    $host = 'localhost';
    $user = 'root';
    $password = 'dct-ccs-finals'; // Default for Laragon
    $database = 'dct-ccs-finals';

    $connection = new mysqli($host, $user, $password, $database);

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    return $connection;
}

// Authenticate User from Database
function authenticate_user($email, $password) {
    $connection = db_connect();
    $password_hash = md5($password); // MD5 hash for password

    $query = "SELECT * FROM users WHERE email = ? AND password = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ss', $email, $password_hash);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc(); // Return user data
    }

    return false; // Login failed
}

// Login User (Session Management)
function login_user($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
}


// Check if User is Logged In
function guard() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /dct-ccs-finals/index.php"); // Redirect to root login page
        exit();
    }
}


// Logout Function
function logout_user() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Start the session if not already started
    }
    session_destroy(); // Destroy the session
    header("Location:../index.php"); // Redirect to root login page
    exit();
}


// Reusable Function for Dismissible Alert Messages
function renderAlert($message, $type = 'danger') {
    if (empty($message)) {
        return '';
    }
    return '
        <div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($message) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    ';
}

// Validate Email and Password Inputs
function validateLoginCredentials($email, $password) {
    $errors = [];
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    return $errors;
}

// Display Multiple Errors as Alerts
function displayErrors($errors) {
    if (empty($errors)) {
        return '';
    }
    $html = '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    $html .= '<strong>Validation Errors:</strong><ul>';
    foreach ($errors as $error) {
        $html .= '<li>' . htmlspecialchars($error) . '</li>';
    }
    $html .= '</ul>';
    $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    $html .= '</div>';
    return $html;
}

function checkUserSessionIsActive() {
    if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
        header("Location: admin/dashboard.php");
        exit;
    }
}
?>
