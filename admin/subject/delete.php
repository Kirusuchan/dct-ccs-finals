<?php
ob_start(); // Start output buffering
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../functions.php';
require_once '../partials/header.php';
require_once '../partials/side-bar.php';

guard(); // Ensure user is authenticated

// Initialize variables
$error_message = '';
$success_message = '';

// Check if an ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /admin/subject/add.php"); // Redirect back to subjects if no ID is provided
    exit();
}

$subject_id = intval($_GET['id']);

// Fetch the subject details for confirmation
$connection = db_connect();
$query = "SELECT * FROM subjects WHERE id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param('i', $subject_id);
$stmt->execute();
$result = $stmt->get_result();
$subject = $result->fetch_assoc();

if (!$subject) {
    $error_message = "Subject not found.";
} else {
    // Handle the delete request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_subject'])) {
        $delete_query = "DELETE FROM subjects WHERE id = ?";
        $delete_stmt = $connection->prepare($delete_query);
        $delete_stmt->bind_param('i', $subject_id);

        if ($delete_stmt->execute()) {
            // Redirect immediately after successful deletion
            header("Location: /admin/subject/add.php");
            exit(); // Ensure no further code execution
        } else {
            $error_message = "Failed to delete the subject: " . $connection->error; // Log error
        }
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h2">Delete Subject</h1>

    <!-- Display error message -->
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Display subject details for confirmation -->
    <?php if (!empty($subject)): ?>
        <nav class="breadcrumb">
            <a class="breadcrumb-item" href="/admin/dashboard.php">Dashboard</a>
            <a class="breadcrumb-item" href="/admin/subject/add.php">Add Subject</a>
            <span class="breadcrumb-item active">Delete Subject</span>
        </nav>

        <div class="card mt-4">
            <div class="card-body">
                <p>Are you sure you want to delete the following subject record?</p>
                <ul>
                    <li><strong>Subject Code:</strong> <?php echo htmlspecialchars($subject['subject_code']); ?></li>
                    <li><strong>Subject Name:</strong> <?php echo htmlspecialchars($subject['subject_name']); ?></li>
                </ul>
                <form method="post">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='/admin/subject/add.php'">Cancel</button>
                    <button type="submit" name="delete_subject" class="btn btn-primary">Delete Subject Record</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php
require_once '../partials/footer.php';
ob_end_flush(); // Flush output buffer
?>
