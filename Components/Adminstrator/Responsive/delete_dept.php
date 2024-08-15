<?php
session_start();
include '../../../check.php';
checkRole('Adminstrator');

// Database connection details
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "duplicationmgmt";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete department
$dept_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if ($dept_id) {
    $stmt = $conn->prepare("DELETE FROM department WHERE id = ?");
    $stmt->bind_param("i", $dept_id);

    if ($stmt->execute()) {
        header("Location: office_management.php"); // Redirect after successful deletion
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
