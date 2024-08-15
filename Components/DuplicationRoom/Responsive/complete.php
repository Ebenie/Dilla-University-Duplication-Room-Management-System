<?php
session_start();
include '../../../check.php';
checkRole('Duplication Room');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // User is not authenticated
    header("Location: ../../../index.php"); // Redirect to the login page
    exit;
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

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = isset($_POST['user_input']) ? $_POST['user_input'] : '';
    $request_id = isset($_POST['request_id']) ? $_POST['request_id'] : '';
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Sanitize user input
    $user_input = $conn->real_escape_string(trim($user_input));
    $request_id = $conn->real_escape_string(trim($request_id));

    if (!empty($user_input) && !empty($request_id)) {
        // Fetch the correct code from the database for the specific request
        $check_sql = "SELECT code FROM request WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $request_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $db_code = $check_result->fetch_assoc()['code'];

            // Compare user input with database code
            if ($user_input == $db_code) {
                // User input matches the code, proceed with the update
                if ($action === 'complete') {
                    $update_sql = "UPDATE request SET req_status = 'completed', final_status = 'completed' WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("i", $request_id);
                    if ($update_stmt->execute()) {
                        header("Location: approved.php");
                        exit;
                    } else {
                        echo "Error updating request: " . $conn->error;
                    }
                    $update_stmt->close();
                }
            } else {
                echo "The code you entered does not match the code for this request.";
                 header("Location: approved.php"); 
                
            }
        } else {
            echo "Request not found.";
        }
        $check_stmt->close();
    } else {
        echo "Invalid input.";
    }
}

$conn->close();
?>
