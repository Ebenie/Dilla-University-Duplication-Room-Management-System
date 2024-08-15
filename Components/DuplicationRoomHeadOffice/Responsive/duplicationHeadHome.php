<?php
session_start();
include '../../../check.php';
checkRole('Duplication Room Head Office');

// Check if the user is logged in
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
$dept_id = $_SESSION['dept_id'];

// Fetch user data from the database
$sql = "SELECT fname, mname, username, profile FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch user-specific request data
$totalRequestsQuery = "
    SELECT COUNT(*) as total 
    FROM request 
    JOIN users ON request.req_by = users.id
WHERE request.office_approval = 'approved'
    ";

$pendingRequestsQuery = "
    SELECT COUNT(*) as pending 
    FROM request 
    JOIN users ON request.req_by = users.id 
    WHERE request.office_approval = 'approved' AND request.duplication_approval = 'pending'";

$approvedRequestsQuery = "
    SELECT COUNT(*) as approved 
    FROM request 
    JOIN users ON request.req_by = users.id 
    WHERE request.duplication_approval = 'approved'";

$rejectedRequestsQuery = "
    SELECT COUNT(*) as rejected 
    FROM request 
    JOIN users ON request.req_by = users.id 
    WHERE request.duplication_approval = 'rejected'";

$allPendingRequests = "SELECT r.*, u.fname, u.mname, d.name as name
                       FROM request r
                       JOIN users u ON r.req_by = u.id
                       JOIN department d ON u.dept_id = d.id
                       WHERE r.req_status = 'pending' AND r.office_approval = 'approved' AND r.duplication_approval = 'pending' 
                       ORDER BY r.req_date ASC";

// Execute the queries
function executeQuery($conn, $query) {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

$totalRequestsResult = executeQuery($conn, $totalRequestsQuery);
$pendingRequestsResult = executeQuery($conn, $pendingRequestsQuery);
$approvedRequestsResult = executeQuery($conn, $approvedRequestsQuery);
$rejectedRequestsResult = executeQuery($conn, $rejectedRequestsQuery);

// Fetch pending requests
$stmt = $conn->prepare($allPendingRequests);
$stmt->execute();
$pendingResult = $stmt->get_result();
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
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="assets/css/style.css">
      <link rel="icon" href="../../../b.png" type="image/x-icon">
    <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> -->
    <!-- <script src="../../../assets/js/script.js"></script> -->
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

            <!-- ======================= Welcome Message ================== -->
            <center>
                <div class="welcome-message">
                    <h2>Welcome Back, <?php echo htmlspecialchars($user['fname']); ?> <?php echo htmlspecialchars($user['mname']); ?>!</h2>
                </div>
            </center>

            <!-- ======================= Cards ================== -->
            <div class="cardBox">
                <div class="card">
                    <div>
                        <div class="numbers"><?php echo number_format($totalRequestsResult['total']); ?></div>
                        <div class="cardName">Total Requests</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="document-text-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo number_format($pendingRequestsResult['pending']); ?></div>
                        <div class="cardName">Pending Requests</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="hourglass-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo number_format($approvedRequestsResult['approved']); ?></div>
                        <div class="cardName">Approved Requests</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="checkmark-done-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo number_format($rejectedRequestsResult['rejected']); ?></div>
                        <div class="cardName">Rejected Requests</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="close-outline"></ion-icon>
                    </div>
                </div>
            </div>

            <!-- ================ Order Details List ================= -->
            <div class="details">
                <div class="recentOrders">
                    
                        <h2>Recent Requests</h2>
                        
                   

                    <table>
                        <thead>
                            <tr>
                                <td>Request By</td>
                                <td>Department</td>
                                <td>Status</td>
                                <td>Payment</td>
                                <td>Approvals</td>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = $pendingResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['fname']." ".$row['mname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['req_status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['final_status']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
