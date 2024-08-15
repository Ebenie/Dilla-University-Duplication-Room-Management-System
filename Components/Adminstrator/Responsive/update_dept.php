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
    // Update department
    if (isset($_POST["update_dept"])) {
        $dept_id = filter_input(INPUT_POST, 'dept_id', FILTER_SANITIZE_NUMBER_INT);
        $dept_name = filter_input(INPUT_POST, 'dept_name', FILTER_SANITIZE_STRING);
        $college_id = filter_input(INPUT_POST, 'college_id', FILTER_SANITIZE_NUMBER_INT);
        $dept_description = filter_input(INPUT_POST, 'dept_description', FILTER_SANITIZE_STRING);

        if (empty($dept_name) || empty($college_id)) {
            $err = "Department name and college are required!";
        } else {
            // Prepare SQL statement to update department
            $stmt = $conn->prepare("UPDATE department SET name = ?, college_id = ?, description = ? WHERE id = ?");
            $stmt->bind_param("sisi", $dept_name, $college_id, $dept_description, $dept_id);

            // Execute statement and check if successful
            if ($stmt->execute()) {
                $err = "Department updated successfully!";
            } else {
                $err = "Error: " . $stmt->error;
            }

            // Close statement
            $stmt->close();
        }
    }
}

// Fetch department details for editing
$dept_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$department = [];
if ($dept_id) {
    $stmt = $conn->prepare("SELECT * FROM department WHERE id = ?");
    $stmt->bind_param("i", $dept_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $department = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Department</title>
    <link rel="stylesheet" href="assets/css/style.css">
      <link rel="icon" href="../../../b.png" type="image/x-icon">
    <style>
        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background-color: #333;
            color: #fff;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar a:hover {
            background-color: #444;
        }
        .sidebar .icon {
            margin-right: 10px;
        }
        .sidebar .title {
            font-size: 16px;
        }

        /* Main content styles */
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }

        /* Form styles */
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group select {
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
        </div>>
    <div class="main">
        <h3>Update Department</h3>
        <?php if ($err): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($err); ?>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <input type="hidden" name="dept_id" value="<?php echo htmlspecialchars($department['id']); ?>">
            <div class="form-group">
                <label for="dept_name">Department Name</label>
                <input type="text" id="dept_name" name="dept_name" value="<?php echo htmlspecialchars($department['name']); ?>">
            </div>
            <div class="form-group">
                <label for="college_id">College</label>
                <select id="college_id" name="college_id">
                    <option value="">Select College</option>
                    <?php
                    // Fetch colleges for dropdown
                    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
                    $stmt = $conn->prepare("SELECT * FROM college");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <option value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo ($row['id'] == $department['college_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                    <?php endwhile; ?>
                    <?php $stmt->close(); $conn->close(); ?>
                </select>
            </div>
            <div class="form-group">
                <label for="dept_description">Description</label>
                <input type="text" id="dept_description" name="dept_description" value="<?php echo htmlspecialchars($department['description']); ?>">
            </div>
            <button type="submit" name="update_dept">Update Department</button>
        </form>
    </div>
    <script src="assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
