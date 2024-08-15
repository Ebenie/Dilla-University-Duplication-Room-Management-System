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
$sql = "SELECT fname, mname, username, profile FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch user-specific request data
$totalRequestsQuery = "SELECT COUNT(*) as total FROM request WHERE req_by = ?";
$pendingRequestsQuery = "SELECT COUNT(*) as pending FROM request WHERE req_by = ? AND req_status='pending'";
$approvedRequestsQuery = "SELECT COUNT(*) as approved FROM request WHERE req_by = ? AND (req_status = 'completed' OR req_status = 'approved')";
$rejectedRequestsQuery = "SELECT COUNT(*) as rejected FROM request WHERE req_by = ? AND req_status='rejected'";
$pendingRequests = "SELECT 
    request.id, 
    request.no_of_students, 
    request.no_of_papers, 
    request.no_of_stencil,
    request.is_double_sided,
    request.photocopy,
    request.reason,
    request.req_date, 
    request.req_status, 
    request.id, 
    users.fname, 
    users.mname, 
    users.username, 
    users.dept_id,
    department.name AS dept_name
FROM 
    request 
JOIN 
    users ON request.req_by = users.id 
JOIN 
    department ON users.dept_id = department.id
WHERE 
    request.req_by = ? 
    AND request.req_status = 'pending'
ORDER BY 
    request.req_date DESC 
LIMIT 4";


// Prepare and execute each statement separately
function executeQuery($conn, $query, $user_id) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

$totalRequestsResult = executeQuery($conn, $totalRequestsQuery, $user_id);
$pendingRequestsResult = executeQuery($conn, $pendingRequestsQuery, $user_id);
$approvedRequestsResult = executeQuery($conn, $approvedRequestsQuery, $user_id);
$rejectedRequestsResult = executeQuery($conn, $rejectedRequestsQuery, $user_id);

// Fetch pending requests
$stmt = $conn->prepare($pendingRequests);
$stmt->bind_param("i", $user_id);
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
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="staffHome.php">
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
                    <div class="cardHeader">
                        <h2>Recent Requests</h2>
                        <a href="viewRequest.php" class="btn">View All</a>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <td>ID</td>
                                <td>Requested By</td>
                                <td>Department Or Office</td>
                                <td>Number Of Students</td>
                                <td>Number Of Papers</td>
                                <td>Number Of Stencil</td>
                                <td>Double Sided</td>
                                <td>Photocopy</td>
                                <td>Reason</td>
                                <td>Requested Date</td>

                                
                                
                                
                                
                                <td>Status</td>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = $pendingResult->fetch_assoc()): ?>
                                <tr>
                                     <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['fname']."  ".$row['mname']); ?></td>
                                     <td><?php echo htmlspecialchars($row['dept_name']); ?></td>

                                    <td><?php echo htmlspecialchars($row['no_of_students']); ?></td>
                                    <td><?php echo htmlspecialchars($row['no_of_papers']); ?></td>
                                    <td><?php echo htmlspecialchars($row['no_of_stencil']); ?></td>
                                    <td><?php echo htmlspecialchars($row['is_double_sided']); ?></td>
                                    <td><?php echo htmlspecialchars($row['photocopy']); ?></td>
                                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d F Y', strtotime($row['req_date']))); ?></td>

                                   
                                    
                                    
                                    
                                    <td><?php echo htmlspecialchars($row['req_status']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
