<?php
session_start();

include '../../../check.php';
checkRole('Adminstrator');

$err = "";

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update campus
    if (isset($_POST["update_campus"])) {
        $campus_id = filter_input(INPUT_POST, 'campus_id', FILTER_SANITIZE_NUMBER_INT);
        $campus_name = filter_input(INPUT_POST, 'campus_name', FILTER_SANITIZE_STRING);
        $campus_description = filter_input(INPUT_POST, 'campus_description', FILTER_SANITIZE_STRING);

        if (empty($campus_name)) {
            $err = "Campus name is required!";
        } else {
            // Prepare SQL statement to update campus
            $stmt = $conn->prepare("UPDATE campus SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $campus_name, $campus_description, $campus_id);

            // Execute statement and check if successful
            if ($stmt->execute()) {
                $err = "Campus updated successfully!";
            } else {
                $err = "Error: " . $stmt->error;
            }

            // Close statement
            $stmt->close();
        }
    }
}

// Fetch campus details for editing
$campus_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$campus = [];
if ($campus_id) {
    $stmt = $conn->prepare("SELECT * FROM campus WHERE id = ?");
    $stmt->bind_param("i", $campus_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $campus = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Campus</title>
    <link rel="stylesheet" href="assets/css/style.css">
      <link rel="icon" href="../../../b.png" type="image/x-icon">
    <style>
        /* Ensure the image is responsive */
        .user img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        /* Basic reset */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
      
        .form-group {
            margin-bottom: 15px;
            
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="adminHome.php">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
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
                    <a href="staff_management.php">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">User Management</span>
                    </a>
                </li>
                    
              <li>
                    <a href="adminHome.php">
                        <span class="icon">
                            <ion-icon name="eye-outline"></ion-icon>
                        </span>
                        <span class="title">View Request</span>
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
                    <a href="office_management.php">
                        <span class="icon">
                            <ion-icon name="settings-outline"></ion-icon>
                        </span>
                        <span class="title">Office Management</span>
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
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                
                <div class="user">
                    <?php if (isset($user['profile']) && $user['profile']): ?>
                        <img src="../../../<?php echo htmlspecialchars($user['profile']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>">
                    <?php else: ?>
                        <img src="assets/imgs/b.png" alt="Dilla University">
                    <?php endif; ?>
                </div>
            </div>
            <h3>Update Campus</h3>
            <?php if ($err): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($err); ?>
                </div>
            <?php endif; ?>
            <form method="post" action="">
                <input type="hidden" name="campus_id" value="<?php echo htmlspecialchars($campus['id']); ?>">
                <div class="form-group">
                    <label for="campus_name">Campus Name</label>
                    <input type="text" id="campus_name" name="campus_name" value="<?php echo htmlspecialchars($campus['name']); ?>">
                </div>
                <div class="form-group">
                    <label for="campus_description">Description</label>
                    <input type="text" id="campus_description" name="campus_description" value="<?php echo htmlspecialchars($campus['description']); ?>">
                </div>
                <button type="submit" name="update_campus">Update Campus</button>
            </form>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
