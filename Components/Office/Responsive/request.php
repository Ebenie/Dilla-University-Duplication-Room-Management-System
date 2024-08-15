<?php
session_start();
include '../../../check.php';
checkRole('Officer');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // User is not authenticated
    header("Location: ../../../index.php"); // Redirect to the login page
    exit;
}


$success = "";
$err="";

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

// Assuming you have the user's ID stored in a session variable
$user_id = $_SESSION['id'];

// Fetch user data from the database
$sql = "SELECT username, profile FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle password change form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract data from the form
    $num_students = $_POST["num_students"];
    $num_papers = $_POST["num_papers"];
    $num_stencils = $_POST["num_stencils"];
    $double_sided = isset($_POST["double_sided"]) ? $_POST["double_sided"] : "";
    if (isset($_POST['photocopy'])) {
        $photocopy = $_POST['photocopy'];
    } else {
        $photocopy = 'no'; // or any default value you want
    }
    $reason = $_POST["reason"];
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $office_approval="approved";

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

    // SQL query to insert data into the database
    $sql = "INSERT INTO request (no_of_students, no_of_papers, no_of_stencil, is_double_sided, photocopy, reason,req_by, code, office_approval) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";

    // Prepare and bind the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissssss", $num_students, $num_papers, $num_stencils, $double_sided, $num_photocopies, $reason, $user_id, $code,$office_approval);

    // Execute the statement
    if ($stmt->execute()) {
        $success="Request Submitted Successfully !";
    } else {
        //echo "Error: " . $sql . "<br>" . $conn->error;
        $err = "Not Submitted !";
    }

    // Close statement and connection
    $stmt->close();
    
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dilla University</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="assets/css/style.css">
      <link rel="icon" href="../../../b.png" type="image/x-icon">
    <style type="text/css">
        
        .custom-form {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #ffffff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);



}

.custom-form .form-group {
    margin-bottom: 15px;
}

.custom-form label {
    font-weight: bold;
}

.custom-form input.form-control,
.custom-form select.form-control {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;

}

.custom-form button.btn {
      width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            background-color: #008f20; /* Bootstrap primary color */
            color: white;
            font-size: 16px;
            cursor: pointer;
}

.custom-form .success {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    border: 1px solid #c3e6cb;
    border-radius: 5px;
    margin-bottom: 15px;
}

.custom-form .error {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border: 1px solid #c3e6cb;
    border-radius: 5px;
    margin-bottom: 15px;
}





@media (max-width: 768px) {
    .custom-form {
        padding: 10px;
    }
}

    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
         <div class="navigation">
            <ul>
                <li>
                    <a href="officeHome.php">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="officeHome.php">
                        <span class="icon">
                            <ion-icon name="eye-outline"></ion-icon>
                        </span>
                        <span class="title">View Request</span>
                    </a>
                </li>
                <li>
                    <a href="messages.php">
                        <span class="icon">
                            <ion-icon name="chatbubble-outline"></ion-icon>
                        </span>
                        <span class="title">Messages</span>
                    </a>
                </li>
                <li>
                    <a href="request.php">
                        <span class="icon">
                            <ion-icon name="create-outline"></ion-icon>
                        </span>
                        <span class="title">Request</span>
                    </a>
                </li>
               
                <li>
                    <a href="approved.php">
                        <span class="icon">
                            <ion-icon name="checkmark-done-outline"></ion-icon>
                        </span>
                        <span class="title">Approved</span>
                    </a>
                </li>
                <li>
                    <a href="rejected.php">
                        <span class="icon">
                            <ion-icon name="close-outline"></ion-icon>
                        </span>
                        <span class="title">Rejected</span>
                    </a>
                </li>
                
                
                <li>
                    <a href="staff_management.php">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">User Management</span>
                    </a>
                </li>
                <li>
                    <a href="changePassword.php">
                        <span class="icon">
                            <ion-icon name="lock-closed-outline"></ion-icon>
                        </span>
                        <span class="title">Password</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
                        <span class="title">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>


        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>

                <div class="user">
                    <?php if ($user['profile']): ?>
                        <img src="../../../<?php echo htmlspecialchars($user['profile']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>">
                    <?php else: ?>
                        <img src="assets/imgs/b.png" alt="Dilla University">
                    <?php endif; ?>
                </div>
            </div>
            <center>
    
    <div class="error " id="error">
        <?php echo $err; ?>
    </div>
    <div class="success" id="success">
        <?php echo $success; ?>
    </div>
</center>
<br><br>

             <center>

            <!-- ======================= Request Form ================== -->
           <div class="cardBoxs">
        <div class="cards">
            <form method="post" autocomplete="off" class="custom-form">
 
    <br>
    <div class="form-group">
        <label for="num_students">NUMBER OF STUDENTS COPIES :</label>
        <input type="number" id="num_students" name="num_students" step="1" min="1" value="1" class="form-control">
    </div>
    <div class="form-group">
        <label for="num_papers">NUMBER OF PAPERS :</label>
        <input type="number" id="num_papers" name="num_papers" step="1" min="1" value="1" class="form-control">
    </div>
    <div class="form-group">
        <label for="num_stencils">NUMBER OF STENCILS :</label>
        <input type="number" id="num_stencils" name="num_stencils" step="1" min="1" value="1" class="form-control">
    </div>
    <div class="form-group">
        <label for="double_sided">DOUBLE SIDED:</label>
        <input type="checkbox" id="double_sided" name="double_sided" value="yes" checked>
    </div>
    <div class="form-group">
        <label for="num_photocopies">PHOTOCOPYING :</label>
        <input type="checkbox" id="num_photocopies" name="photocopy" value="yes" step="1" checked>
    </div>
    <div class="form-group">
        <label for="reason">REASON:</label>
        <select id="reason" name="reason" class="form-control">
            <option value="Test exam">Test Exam</option>
            <option value="Mid exam">Mid Exam</option>
            <option value="Final exam">Final Exam</option>
            <option value="Others">Others</option>
        </select>
    </div>
    <button type="submit" name="Submit" class="btn btn-success">Submit</button>
</form>

        </div>
    </div>

   </center>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>
