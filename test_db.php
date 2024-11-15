<?php
require_once 'functions.php'; // Ensure this points to the correct file

// Test database connection
$connection = db_connect();

if ($connection) {
    echo "Database connection successful!";
} else {
    echo "Database connection failed!";
}

// Optional: Perform a simple query to test
$query = "SHOW TABLES";
$result = $connection->query($query);

if ($result) {
    echo "<br>Tables in database:<br>";
    while ($row = $result->fetch_array()) {
        echo $row[0] . "<br>";
    }
} else {
    echo "<br>Error fetching tables: " . $connection->error;
}

$connection->close();
?>
