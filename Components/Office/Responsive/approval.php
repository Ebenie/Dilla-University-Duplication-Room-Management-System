<?php
session_start();
include '../../../check.php';
checkRole('Officer');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture the form data
    $request_id = htmlspecialchars($_POST['request_id']);
    $action = htmlspecialchars($_POST['action']);

    // Database connection (modify with your own database connection details)
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

    // SQL query to update the request status
    if ($action == "approve") {
        $sql = "UPDATE request SET office_approval ='approved' WHERE id=?";
    } elseif ($action == "reject") {
        $sql = "UPDATE request SET office_approval='rejected', req_status='rejected' WHERE id=?";
    } else {
        echo "Invalid action.";
        exit;
    }

    // Prepare and bind
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);

    // Execute the query
    if ($stmt->execute()) {
        header("Location: officeHome.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }

    // Close the connection
    $stmt->close();
    $conn->close();
}
?>
