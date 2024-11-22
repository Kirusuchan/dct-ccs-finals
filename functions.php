<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
function db_connect() {
    $host = 'localhost';
    $user = 'root';
    $password = ''; // Default for Laragon
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


// Get the base URL dynamically
function getBaseURL() {
    // Check if HTTPS is enabled
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    // Build the base URL using host and server name
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . $host . '/'; // Ensure it points to the root
}

// Check if the user is logged in
function guard() {
    if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
        // Use the base URL to redirect
        $baseURL = getBaseURL();
        header("Location: " . $baseURL); // Redirect to the base URL
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
function renderAlert($messages, $type = 'danger') {
    if (empty($messages)) {
        return '';
    }
    // Ensure messages is an array
    if (!is_array($messages)) {
        $messages = [$messages];
    }

    $html = '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
    $html .= '<ul>';
    foreach ($messages as $message) {
        $html .= '<li>' . htmlspecialchars($message) . '</li>';
    }
    $html .= '</ul>';
    $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    $html .= '</div>';

    return $html;
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

// Function to validate subject data (subject code and subject name)
function validateSubjectData($subject_data) {
    $errors = [];

    // Check if subject code is provided and has a valid length
    if (empty($subject_data['subject_code'])) {
        $errors[] = "Subject code is required.";
    } elseif (strlen($subject_data['subject_code']) > 4) { // Limiting subject code length to 4 characters
        $errors[] = "Subject code cannot be longer than 4 characters.";
    }

    // Check if subject name is provided and is not too long
    if (empty($subject_data['subject_name'])) {
        $errors[] = "Subject name is required.";
    } elseif (strlen($subject_data['subject_name']) > 100) { // Limiting subject name length to 100 characters
        $errors[] = "Subject name cannot be longer than 100 characters.";
    }

    return $errors; // Return the list of errors
}

// Function to check for duplicate subject data in the database
function checkDuplicateSubjectData($subject_data) {
    $connection = db_connect();
    $query = "SELECT * FROM subjects WHERE subject_code = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('s', $subject_data['subject_code']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return "Subject code already exists. Please choose another."; // Return the error message for duplicates
    }

    return ''; // No duplicate found
}

// Function to check for duplicate subject name in the database
function checkDuplicateSubjectName($subject_name) {
    $connection = db_connect();
    $query = "SELECT * FROM subjects WHERE subject_name = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('s', $subject_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return "Subject name already exists. Please choose another."; // Return the error message for duplicates
    }

    return ''; // No duplicate found
}


// Validate student data
function validateStudentData($student_data) {
    $errors = [];
    if (empty($student_data['student_id'])) {
        $errors[] = "Student ID is required.";
    }
    if (empty($student_data['first_name'])) {
        $errors[] = "First Name is required.";
    }
    if (empty($student_data['last_name'])) {
        $errors[] = "Last Name is required.";
    }

    // Removed the var_dump debug
    return $errors;
}

// Check for duplicate student data in the database
function checkDuplicateStudentData($student_data) {
    $connection = db_connect();
    $query = "SELECT * FROM students WHERE student_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('s', $student_data['student_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return "Student ID already exists.";
    }

    // Removed the var_dump debug
    return '';
}


// Function to generate unique ID for students
function generateUniqueIdForStudents() {
    $connection = db_connect();

    // Find the maximum current ID and add 1 to it
    $query = "SELECT MAX(id) AS max_id FROM students";
    $result = $connection->query($query);
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'];

    $connection->close();

    return $max_id + 1; // Generate the next unique ID
}

// Generate a valid 4-character student_id
function generateValidStudentId($original_id) {
    // Truncate to the first 4 characters
    return substr($original_id, 0, 4);
}

// Function to get selected student data
function getSelectedStudentData($student_id) {
    $connection = db_connect();
    $query = "SELECT * FROM students WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    $stmt->close();
    $connection->close();

    return $student;
}
?>
