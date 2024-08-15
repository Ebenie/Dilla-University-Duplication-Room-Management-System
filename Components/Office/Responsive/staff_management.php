<?php
session_start();
include '../../../check.php';
checkRole('Officer');
$err = "";
$dept_id = $_SESSION['dept_id'] ?? "";




if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["register"])) {
        // Validate and sanitize input
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $fname = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_STRING);
        $mname = filter_input(INPUT_POST, 'mname', FILTER_SANITIZE_STRING);
        $lname = filter_input(INPUT_POST, 'lname', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
        $dept_id = filter_input(INPUT_POST, 'dept_id', FILTER_SANITIZE_STRING);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Validate required fields
        if (empty($username) || empty($password) || empty($fname) || empty($lname) || empty($email)) {
            $err = "All fields marked with an asterisk (*) are required!";
        } else {
            // Encrypt the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Handle file upload for profile picture
            $profile = null;
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

            // Prepare SQL statement to insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, password, fname, mname, lname, email, gender, role, is_active, profile, dept_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssi", $username, $hashed_password, $fname, $mname, $lname, $email, $gender, $role, $is_active, $profile, $dept_id);

            // Execute statement and check if successful
            if ($stmt->execute()) {
                $err = "Registration successful!";
            } else {
                $err = "Error: " . $stmt->error;
            }

            // Close statement and connection
            $stmt->close();
            $conn->close();
        }
    }
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

// Fetch users specific to the department
$dept_id = $_SESSION['dept_id'] ?? "";
$user_id = $_SESSION['id'];
$users = [];
if (!empty($dept_id)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'Staff' AND dept_id = ?");
    $stmt->bind_param("i", $dept_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
         if ($row['id'] == $user_id) {
            $user = $row;
           
        }
    }
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

        .alert-danger {
            color: #dc3545;
            margin-bottom: 1rem;
        }

        /* Responsive Styling */
        @media (max-width: 768px) {
            .form-group input,
            .form-group select,
            button[type="submit"] {
                font-size: 0.9rem;
            }

            button[type="submit"] {
                padding: 0.5rem 1rem;
            }
        }

        table {
            width: 95%;
            border-collapse: collapse;
            margin: 20px;
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
.btn-update{
    display: inline-block;
    padding: 8px 12px;
    margin: 0 5px;
    color: #fff; /* Text color */
    background-color: #007bff; /* Background color */
    border: none;
    border-radius: 4px;
    text-decoration: none; /* Remove underline */
    font-size: 14px;
    transition: background-color 0.3s; /* Smooth transition */
}

.btn-delete{
    display: inline-block;
    padding: 8px 12px;
    margin: 0 5px;
    color: #fff; /* Text color */
    background-color: red; /* Background color */
    border: none;
    border-radius: 4px;
    text-decoration: none; /* Remove underline */
    font-size: 14px;
    transition: background-color 0.3s; /* Smooth transition */
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
            <div class="main-content">
                <form method="post" enctype="multipart/form-data">
                    <h2>Register Staff Members</h2><br>
                    <div class="alert-danger">
                        <?php echo htmlspecialchars($err); ?>
                    </div>
                    <div class="form-group">
                        <label for="username"><b>Username*</b></label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password"><b>Password*</b></label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="fname"><b>First Name*</b></label>
                        <input type="text" id="fname" name="fname" required>
                    </div>
                    <div class="form-group">
                        <label for="mname"><b>Middle Name</b></label>
                        <input type="text" id="mname" name="mname">
                    </div>
                    <div class="form-group">
                        <label for="lname"><b>Last Name*</b></label>
                        <input type="text" id="lname" name="lname" required>
                    </div>
                    <div class="form-group">
                        <label for="email"><b>Email*</b></label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="gender"><b>Gender</b></label>
                        <select id="gender" name="gender">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="role"><b>Role</b></label>
                        <select id="role" name="role">
                            <option value="Staff">Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="is_active"><b>Is Active</b></label>
                        <input type="checkbox" id="is_active" name="is_active" checked>
                    </div>
                    <div class="form-group">
                        <label for="profile"><b>Profile Picture</b></label>
                        <input type="file" id="profile" name="profile">
                    </div>
                    <input type="hidden" name="dept_id" value="<?php echo htmlspecialchars($dept_id); ?>">
                    <button type="submit" name="register">Register</button>
                </form>

                <!-- Display users specific to the department -->
                <center><h3>Your Department(Office) Staff Members </h3></center>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['fname']); ?></td>
                                <td><?php echo htmlspecialchars($user['mname']); ?></td>
                                <td><?php echo htmlspecialchars($user['lname']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <a href="update_user.php?id=<?php echo $user['id']; ?>"class="btn-update">Update</a>
                                    <a href="delete_user.php?id=<?php echo $user['id']; ?>"class="btn-delete">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>
