<?php
session_start();
include '../../../check.php';
checkRole('Adminstrator');

$err = "";
$success = "";
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

// Fetch existing user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $fname = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_STRING);
    $mname = filter_input(INPUT_POST, 'mname', FILTER_SANITIZE_STRING);
    $lname = filter_input(INPUT_POST, 'lname', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $login_attempt = 0;

    // Validate required fields
    if (empty($username) || empty($fname) || empty($lname) || empty($email)) {
        $err = "All fields marked with an asterisk (*) are required!";
    } else {
        // Encrypt the password if provided
        $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : $user['password'];

        // Handle file upload for profile picture
        $profile = $user['profile'];
        if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
            $profile_tmp = $_FILES['profile']['tmp_name'];
            $profile_name = basename($_FILES['profile']['name']);
            $profile_path = "uploads/" . $profile_name;

            // Ensure the uploads directory exists
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }

            if (move_uploaded_file($profile_tmp, $profile_path)) {
                $profile = $profile_path;
            } else {
                $err = "Failed to upload profile picture.";
            }
        }

        // Update user in the database
        $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, fname = ?, mname = ?, lname = ?, email = ?, gender = ?, role = ?, is_active = ?, profile = ?, login_attempt = ? WHERE id = ?");
        $stmt->bind_param("ssssssssssii", $username, $hashed_password, $fname, $mname, $lname, $email, $gender, $role, $is_active, $profile, $login_attempt, $user_id);

        if ($stmt->execute()) {
            $success = "User updated successfully!";
        } else {
            $err = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <link rel="stylesheet" href="assets/css/style.css">
      <link rel="icon" href="../../../b.png" type="image/x-icon">
    <style>
        /* Sidebar Styling */
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #333;
            padding-top: 20px;
        }

        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #f1f1f1;
            display: block;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* General Form Styling */
        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 1rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 0.5rem;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            background-color: #008f20; /* Bootstrap primary color */
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #097132;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    <div class="container">
    <div class="navigation">
            <ul>
                <li><a href="adminHome.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span><span class="title">Dashboard</span></a></li>
                <li><a href="messages.php"><span class="icon"><ion-icon name="chatbubble-outline"></ion-icon></span><span class="title">Messages</span></a></li>
                <li><a href="staff_management.php"><span class="icon"><ion-icon name="people-outline"></ion-icon></span><span class="title">User Management</span></a></li>
                <li><a href="adminHome.php"><span class="icon"><ion-icon name="eye-outline"></ion-icon></span><span class="title">View Request</span></a></li>
                <li><a href="changePassword.php"><span class="icon"><ion-icon name="lock-closed-outline"></ion-icon></span><span class="title">Password</span></a></li>
                <li><a href="office_management.php"><span class="icon"><ion-icon name="settings-outline"></ion-icon></span><span class="title">Office Management</span></a></li>
                <li><a href="logout.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span><span class="title">Sign Out</span></a></li>
            </ul>
        </div>

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

        <center><h3>Update Staff Members</h3></center><br>
        <?php if ($err): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Username*</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="form-group">
                <label for="fname">First Name*</label>
                <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($user['fname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="mname">Middle Name</label>
                <input type="text" id="mname" name="mname" value="<?php echo htmlspecialchars($user['mname']); ?>">
            </div>
            <div class="form-group">
                <label for="lname">Last Name*</label>
                <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($user['lname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address*</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="Male" <?php echo ($user['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($user['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($user['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
            </div>
            <div class="form-group">
                <label for="profile">Profile Picture</label>
                <input type="file" id="profile" name="profile">
                <?php if ($user['profile']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile']); ?>" alt="Profile Picture" style="width: 80px; height: 80px;border-radius: 50%;">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="is_active">Active</label>
                <input type="checkbox" id="is_active" name="is_active" <?php echo ($user['is_active'] ? 'checked' : ''); ?>>
            </div>
            <button type="submit">Update User</button>
        </form>
    </div>
    <script src="assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
