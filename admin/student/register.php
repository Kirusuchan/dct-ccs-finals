<?php
$title = "Register a New Student"; // Set the title
require_once '../partials/header.php';
require_once '../partials/side-bar.php';
guard();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize error message and success message
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_data = [
        'student_id' => generateValidStudentId(trim($_POST['student_id'] ?? '')), // Ensure it's 4 characters
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? '')
    ];

    // Validate student data
    $errors = validateStudentData($student_data);

    if (empty($errors)) {
        // Check for duplicate student ID
        $duplicateError = checkDuplicateStudentData($student_data);

        if (!empty($duplicateError)) {
            $error_message = renderAlert([$duplicateError], 'danger');
        } else {
            // Insert the student data into the database
            $connection = db_connect();

            // Generate a unique ID for the student
            $student_id_unique = generateUniqueIdForStudents();

            $query = "INSERT INTO students (id, student_id, first_name, last_name) VALUES (?, ?, ?, ?)";
            $stmt = $connection->prepare($query);
            if ($stmt) {
                $stmt->bind_param('isss', $student_id_unique, $student_data['student_id'], $student_data['first_name'], $student_data['last_name']);
                if ($stmt->execute()) {
                    $success_message = renderAlert(["Student successfully registered!"], 'success');
                } else {
                    $error_message = renderAlert(["Failed to register student. Error: " . $stmt->error], 'danger');
                }
                $stmt->close();
            } else {
                $error_message = renderAlert(["Statement preparation failed: " . $connection->error], 'danger');
            }

            $connection->close();
        }
    } else {
        $error_message = renderAlert($errors, 'danger');
    }
}

?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    
    <h1 class="h2">Register a New Student</h1>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Register Student</li>
        </ol>
    </nav>

    <?php if (!empty($error_message)): ?>
        <?php echo $error_message; ?>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <?php echo $success_message; ?>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label for="student_id" class="form-label">Student ID</label>
            <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter Student ID" value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter First Name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter Last Name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">Add Student</button>
        </div>
    </form>

    <hr>

    <h2 class="h4">Student List</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">Student ID</th>
                <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $connection = db_connect();
            $query = "SELECT * FROM students";
            $result = $connection->query($query);

            while ($student = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="delete.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                        <a href="attach-subject.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning">Attach Subject</a>
                    </td>
                </tr>
            <?php endwhile; ?>

            <?php
            $connection->close();
            ?>
        </tbody>
    </table>
</main>

<?php require_once '../partials/footer.php'; ?>
