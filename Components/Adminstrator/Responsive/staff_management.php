<?php
session_start();
include '../../../check.php';
checkRole('Adminstrator');

$err = "";
$dept_id = $_SESSION['dept_id'] ?? "";

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

// Fetch departments
$departments = [];
$result = $conn->query("SELECT id, name FROM department");
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

// Handle registration
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

            // Prepare SQL statement to insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, password, fname, mname, lname, email, gender, role, is_active, profile, dept_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssi", $username, $hashed_password, $fname, $mname, $lname, $email, $gender, $role, $is_active, $profile, $dept_id);

            // Execute statement and check if successful
            if ($stmt->execute()) {
                $err = "Registration successful!";
            } else {
                $err = "Error: " . $stmt->error;
            }

            // Close statement
            $stmt->close();
        }
    }
}

// Fetch users specific to the department
$users = [];
if (!empty($dept_id)) {
    $stmt = $conn->prepare("
    SELECT 
        users.*, 
        department.name AS department_name 
    FROM 
        users 
    LEFT JOIN 
        department 
    ON 
        users.dept_id = department.id 
    WHERE 
        users.role IN ('Duplication Room Head Office', 'Officer', 'Administrator')
");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dilla University</title>
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

        .btn-update {
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

        .btn-delete {
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
                <li><a href="adminHome.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span><span class="title">Dashboard</span></a></li>
                <li><a href="messages.php"><span class="icon"><ion-icon name="chatbubble-outline"></ion-icon></span><span class="title">Messages</span></a></li>
                <li><a href="staff_management.php"><span class="icon"><ion-icon name="people-outline"></ion-icon></span><span class="title">User Management</span></a></li>
                <li><a href="adminHome.php"><span class="icon"><ion-icon name="eye-outline"></ion-icon></span><span class="title">View Request</span></a></li>
                <li><a href="changePassword.php"><span class="icon"><ion-icon name="lock-closed-outline"></ion-icon></span><span class="title">Password</span></a></li>
                <li><a href="office_management.php"><span class="icon"><ion-icon name="settings-outline"></ion-icon></span><span class="title">Office Management</span></a></li>
                <li><a href="logout.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span><span class="title">Sign Out</span></a></li>
            </ul>
        </div>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                
            </div>

            <div class="container">
                <center><h3 class="title">User Registration</h3></center>
                <?php if ($err): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($err); ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" name="username" id="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <div class="form-group">
                        <label for="fname">First Name *</label>
                        <input type="text" name="fname" id="fname" required>
                    </div>
                    <div class="form-group">
                        <label for="mname">Middle Name</label>
                        <input type="text" name="mname" id="mname">
                    </div>
                    <div class="form-group">
                        <label for="lname">Last Name *</label>
                        <input type="text" name="lname" id="lname" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select name="gender" id="gender">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select name="role" id="role" required>
                            <option value="Admin">Admin</option>
                            <option value="Officer">Officer</option>
                            <option value="User">User</option>
                        </select>
                    </div>
                    <div class="form-group" id="department-container" style="display:none;">
                        <label for="dept_id">Department</label>
                        <select name="dept_id" id="dept_id">
                            <?php foreach ($departments as $department): ?>
                                <option value="<?= $department['id']; ?>"><?= htmlspecialchars($department['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="profile">Profile Picture</label>
                        <input type="file" name="profile" id="profile">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" checked>
                            Active
                        </label>
                    </div>
                    <button type="submit" name="register">Register</button>
                </form>
            </div>

            <!-- Display users -->
            <h2 class="title">Users in Department</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Email</th>
                        <th>Office</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username']); ?></td>
                            <td><?= htmlspecialchars($user['fname']); ?></td>
                            <td><?= htmlspecialchars($user['mname']); ?></td>
                            
                            <td><?= htmlspecialchars($user['email']); ?></td>
                            <td><?= htmlspecialchars($user['department_name']); ?></td>
                            <td><?= htmlspecialchars($user['role']); ?></td>
                            <td>
                                <a href="update_user.php?id=<?= $user['id']; ?>" class="btn-update">Update</a>
                                <a href="delete_user.php?id=<?= $user['id']; ?>" class="btn-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
        <script src="assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script>
        document.getElementById('role').addEventListener('change', function() {
            var role = this.value;
            var departmentContainer = document.getElementById('department-container');
            if (role === 'Officer') {
                departmentContainer.style.display = 'block';
            } else {
                departmentContainer.style.display = 'none';
                document.getElementById('dept_id').value = ''; // Clear selected department if role changes
            }
        });
    </script>

</body>
</html>
