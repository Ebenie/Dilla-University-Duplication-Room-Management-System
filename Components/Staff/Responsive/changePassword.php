<?php
session_start();
include '../../../check.php';
checkRole('Staff');

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
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the user's current hashed password
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $current_password_hash = $user_data['password'];
    $stmt->close();

    // Verify old password
    if (password_verify($old_password, $current_password_hash)) {
        if ($new_password === $confirm_password) {
            $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the new password
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_password_hash, $user_id);
            if ($stmt->execute()) {
                $message = "<p class='success-message'>Password updated successfully.</p>";
            } else {
                $message = "<p class='error-message'>Error updating password.</p>";
            }
            $stmt->close();
        } else {
            $message = "<p class='error-message'>New passwords do not match.</p>";
        }
    } else {
        $message = "<p class='error-message'>Old password is incorrect.</p>";
    }
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
        /* Unique and centered class for password change form */
        .password-change-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh; /* Full viewport height */
            background-color: #ffffff; /* White background color */
        }

        .password-change-card {
            background-color: #ffffff; /* White background for the card */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Shadow effect */
            padding: 20px;
            width: 100%;
            max-width: 600px; /* Increased maximum width for larger screens */
            box-sizing: border-box;
        }

        .password-change-card h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .password-change-card label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .password-change-card input {
            width: 100%; /* Full width of the container */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .password-change-card button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            background-color: #008f20; /* Bootstrap primary color */
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .password-change-card button:hover {
            background-color: #009b23; /* Darker shade on hover */
        }

        .password-change-card .error-message {
            color: #dc3545; /* Bootstrap danger color */
            font-weight: bold;
            margin-bottom: 15px;
        }

        .password-change-card .success-message {
            color: #28a745; /* Bootstrap success color */
            font-weight: bold;
            margin-bottom: 15px;
        }

        /* Responsive design for smaller screens */
        @media (max-width: 600px) {
            .password-change-card {
                padding: 15px;
                max-width: 100%; /* Ensure card width is responsive */
            }
            
            .password-change-card input {
                width: calc(100% - 20px); /* Adjust width for smaller screens */
            }
            
            .password-change-card button {
                font-size: 14px;
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
                   
                </li>

                <li>
                    <a href="staffHome.php">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

                <li>
                    

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
                <a href="viewRequest.php">
                    <span class="icon">
                        <ion-icon name="eye-outline"></ion-icon>
                    </span>
                    <span class="title">View Request</span>
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

            <!-- ======================= Password Change Form ================== -->
            <div class="password-change-container">
    <div class="password-change-card">
        <h2>Change Password</h2>
        <form action="" method="post">
            <label for="old_password">Old Password:</label>
            <input type="password" id="old_password" name="old_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Change Password</button>
        </form>
    </div>
</div>

        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>
