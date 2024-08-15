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

// Assuming the user's ID is stored in a session variable
$user_id = $_SESSION['id'];

// // Fetch approved requests for the specific user from the database
// $sql = "SELECT * FROM request WHERE req_status = 'rejected' AND req_by = ?";

$sql = "SELECT 
    request.id, 
    request.no_of_students, 
    request.no_of_papers, 
    request.no_of_stencil,
    request.is_double_sided,
    request.photocopy,
    request.reason,
    request.req_date,
    request.req_status, 
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
    req_status = 'rejected' AND req_by = ?
ORDER BY 
    request.req_date DESC 
LIMIT 10
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user details
$sql_user = "SELECT fname, mname, username, profile FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
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
            <center><h1>Rejected Requests</h1></center>
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Rejected Requests</h2>
                        
                    </div>
                    <?php if ($result->num_rows > 0): ?>
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
                        <?php while ($row = $result->fetch_assoc()): ?>
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

                     <?php else: ?>
                <p>No rejected requests found for your account.</p>
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
