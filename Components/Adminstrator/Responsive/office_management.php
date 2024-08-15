<?php
session_start();
include '../../../check.php';
checkRole('Adminstrator');

$err = "";
$dept_id = $_SESSION['dept_id'] ?? "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input based on form type
    if (isset($_POST["register_dept"])) {
        // Department registration
        $dept_name = filter_input(INPUT_POST, 'dept_name', FILTER_SANITIZE_STRING);
        $college_id = filter_input(INPUT_POST, 'college_id', FILTER_SANITIZE_NUMBER_INT);
        $dept_description = filter_input(INPUT_POST, 'dept_description', FILTER_SANITIZE_STRING);

        if (empty($dept_name) || empty($college_id)) {
            $err = "Department name and college are required!";
        } else {
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

            // Prepare SQL statement to insert new department
            $stmt = $conn->prepare("INSERT INTO department (name, college_id, description) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $dept_name, $college_id, $dept_description);

            // Execute statement and check if successful
            if ($stmt->execute()) {
                $err = "Department registration successful!";
            } else {
                $err = "Error: " . $stmt->error;
            }

            // Close statement and connection
            $stmt->close();
            $conn->close();
        }
    } elseif (isset($_POST["register_college"])) {
        // College registration
        $college_name = filter_input(INPUT_POST, 'college_name', FILTER_SANITIZE_STRING);
        $college_description = filter_input(INPUT_POST, 'college_description', FILTER_SANITIZE_STRING);

        if (empty($college_name)) {
            $err = "College name is required!";
        } else {
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

            // Prepare SQL statement to insert new college
            $stmt = $conn->prepare("INSERT INTO college (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $college_name, $college_description);

            // Execute statement and check if successful
            if ($stmt->execute()) {
                $err = "College registration successful!";
            } else {
                $err = "Error: " . $stmt->error;
            }

            // Close statement and connection
            $stmt->close();
            $conn->close();
        }
    } elseif (isset($_POST["register_campus"])) {
        // Campus registration
        $campus_name = filter_input(INPUT_POST, 'campus_name', FILTER_SANITIZE_STRING);
        $campus_description = filter_input(INPUT_POST, 'campus_description', FILTER_SANITIZE_STRING);

        if (empty($campus_name)) {
            $err = "Campus name is required!";
        } else {
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

            // Prepare SQL statement to insert new campus
            $stmt = $conn->prepare("INSERT INTO campus (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $campus_name, $campus_description);

            // Execute statement and check if successful
            if ($stmt->execute()) {
                $err = "Campus registration successful!";
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

// Fetch departments
$departments = [];
$stmt = $conn->prepare("SELECT * FROM department");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}
$stmt->close();

// Fetch colleges
$colleges = [];
$stmt = $conn->prepare("SELECT * FROM college");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $colleges[] = $row;
}
$stmt->close();

// Fetch campuses
$campuses = [];
$stmt = $conn->prepare("SELECT * FROM campus");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $campuses[] = $row;
}
$stmt->close();

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

        <!-- =============== Content ================ -->
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
            

            <!-- Display Error/Success Messages -->
            <center>
            <?php if ($err): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($err); ?>
                </div>
            <?php endif; ?>

            <!-- Department Registration Form -->
            <h3>Register Department</h3>
            </center>
            <form method="post" action="">
                <div class="form-group">
                    <label for="dept_name">Department Name</label>
                    <input type="text" id="dept_name" name="dept_name">
                </div>
                <div class="form-group">
                    <label for="college_id">College</label>
                    <select id="college_id" name="college_id">
                        <option value="">Select College</option>
                        <?php foreach ($colleges as $college): ?>
                            <option value="<?php echo htmlspecialchars($college['id']); ?>">
                                <?php echo htmlspecialchars($college['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dept_description">Description</label>
                    <input type="text" id="dept_description" name="dept_description">
                </div>
               
                <button type="submit" name="register_dept">Register Department</button>

            </form>

            <!-- College Registration Form -->
            <br><br>
             <center>
            <h3>Register College</h3>
            </center>
            <form method="post" action="">
                <div class="form-group">
                    <label for="college_name">College Name</label>
                    <input type="text" id="college_name" name="college_name">
                </div>
                <div class="form-group">
                    <label for="college_description">Description</label>
                    <input type="text" id="college_description" name="college_description">
                </div>
                <button type="submit" name="register_college">Register College</button>
            </form>
            <br><br>

            <!-- Campus Registration Form -->

             <center>
            <h3>Register Campus</h3>
            </center>
            <form method="post" action="">
                <div class="form-group">
                    <label for="campus_name">Campus Name</label>
                    <input type="text" id="campus_name" name="campus_name">
                </div>
                <div class="form-group">
                    <label for="campus_description">Description</label>
                    <input type="text" id="campus_description" name="campus_description">
                </div>
                <button type="submit" name="register_campus">Register Campus</button>
            </form>

            <br><br>

            <!-- Display Departments -->
            <center>
            <h3>Departments</h3>
            </center>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>College ID</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $department): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($department['id']); ?></td>
                            <td><?php echo htmlspecialchars($department['name']); ?></td>
                            <td><?php echo htmlspecialchars($department['college_id']); ?></td>
                            <td><?php echo htmlspecialchars($department['description']); ?></td>
                            <td>
                                <a href="update_dept.php?id=<?php echo htmlspecialchars($department['id']); ?>" class="btn-update">Update</a>
                                <a href="delete_dept.php?id=<?php echo htmlspecialchars($department['id']); ?>" class="btn-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br><br>
            <!-- Display Colleges -->
            <center>
            <h3>Colleges</h3>
            </center>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($colleges as $college): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($college['id']); ?></td>
                            <td><?php echo htmlspecialchars($college['name']); ?></td>
                            <td><?php echo htmlspecialchars($college['description']); ?></td>
                            <td>
                                <a href="update_college.php?id=<?php echo htmlspecialchars($college['id']); ?>" class="btn-update">Update</a>
                                <a href="delete_college.php?id=<?php echo htmlspecialchars($college['id']); ?>" class="btn-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <br><br>

            <!-- Display Campuses -->
            <center>
            <h3>Campuses</h3>
            </center>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campuses as $campus): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($campus['id']); ?></td>
                            <td><?php echo htmlspecialchars($campus['name']); ?></td>
                            <td><?php echo htmlspecialchars($campus['description']); ?></td>
                            <td>
                                <a href="update_campus.php?id=<?php echo htmlspecialchars($campus['id']); ?>" class="btn-update">Update</a>
                                <a href="delete_campus.php?id=<?php echo htmlspecialchars($campus['id']); ?>" class="btn-delete">Delete</a>
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
</body>
</html>
