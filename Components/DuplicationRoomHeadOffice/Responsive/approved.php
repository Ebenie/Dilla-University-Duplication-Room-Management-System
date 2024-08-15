<?php
session_start();
include '../../../check.php';
checkRole('Duplication Room Head Office');

// Check if the user is authenticated
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
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

// Fetch approved requests for the specific user from the database
$sql = "SELECT r.*, u.fname, u.mname, d.name
        FROM request r
        JOIN users u ON r.req_by = u.id
        JOIN department d ON u.dept_id = d.id
        WHERE r.duplication_approval = 'approved' AND r.final_status = 'pending' OR r.final_status = 'completed'
        ORDER BY r.req_date ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user details
$user_id = $_SESSION['id'];
$sql_user = "SELECT fname, mname, username, profile FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id); // Bind the user_id parameter
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();

$stmt->close();
$stmt_user->close();
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
    <style>
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
                    <a href="staff_management.php">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">User Management</span>
                    </a>
                </li>
                    
              <li>
                    <a href="duplicationHeadHome.php">
                        <span class="icon">
                            <ion-icon name="eye-outline"></ion-icon>
                        </span>
                        <span class="title">View Request</span>
                    </a>
                </li>
                <li>
                    <a href="view_reports.php">
                     <span class="icon">
                     <ion-icon name="analytics-outline"></ion-icon>
                     </span>
                      <span class="title">Report</span>
                        </a>
                </li>
                <li>
                    <a href="duplicationHeadHome.php">
                       <span class="icon">
                           <ion-icon name="create-outline"></ion-icon>
                         </span>
                      <span class="title">Register</span>
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
                    <a href="../../../index.php">
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
            <center><h1>Approved Requests</h1></center>
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Approved Requests</h2>
                        
                    </div>
                    <?php if ($result->num_rows > 0): ?>
                    <table>
                       <thead>
                        <tr>
                                                     
                            <th>Requested By</th>
                            <td>Department</td>
                            <td>Number Of Students</td>
                            <td>Number Of Papers</td>
                            <td>Number Of Stencil</td>
                            <td>Double Sided</td>
                            <td>Photocopy</td>
                            <td>Reason</td>
                            <td>Date</td>
                            <td>Code and Approval</td>
                                
                            
                        </tr>
                    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                                <td><?php echo htmlspecialchars($row['fname']." ".$row['mname']); ?>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <!-- Adjust as needed -->

                                   <td><?php echo htmlspecialchars($row['no_of_students']); ?></td>
                                    <td><?php echo htmlspecialchars($row['no_of_papers']); ?></td>
                                    <td><?php echo htmlspecialchars($row['no_of_stencil']); ?></td>
                                    <td><?php echo htmlspecialchars($row['is_double_sided']); ?></td>
                                    <td><?php echo htmlspecialchars($row['photocopy']); ?></td>
                                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d F Y', strtotime($row['req_date']))); ?></td>
                                
              
                                      <td>
                                        <?php echo htmlspecialchars($row['final_status']); ?>
                                     </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
                    </table>
                     <?php else: ?>
                <p>No approved requests found for your account.</p>
                     <?php endif; ?>
                </div>

                <!-- ================= New Customers ================ -->
                
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