<?php
session_start();
include '../../../check.php';
checkRole('Adminstrator');

$user_id = $_GET['id'] ?? "";

if (!$user_id) {
    die("User ID is required.");
}

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

// First, delete dependent rows from 'request' table
$stmt = $conn->prepare("DELETE FROM request WHERE req_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Now, delete the user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    header("Location: staff_management.php"); // Redirect to user management page after deletion
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
