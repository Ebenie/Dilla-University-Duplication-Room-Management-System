<?php
session_start();
include '../../../check.php';
checkRole('Duplication Room Head Office');

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

// Fetch total request data
$totalRequestsQuery = "SELECT COUNT(*) as total FROM request";
$pendingRequestsQuery = "SELECT COUNT(*) as pending FROM request WHERE final_status='pending'";
$approvedRequestsQuery = "SELECT COUNT(*) as approved FROM request WHERE final_status='approved'";
$rejectedRequestsQuery = "SELECT COUNT(*) as rejected FROM request WHERE final_status='rejected'";

// Execute each query separately
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

// Function to fetch data for reports
function getReportData($conn, $period) {
    $query = "SELECT final_status, COUNT(*) as count FROM request WHERE req_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY final_status";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $period);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['final_status']] = $row['count'];
    }
    $stmt->close();
    return $data;
}

// Get report data for different periods
$dailyReport = getReportData($conn, 1);
$weeklyReport = getReportData($conn, 7);
$monthlyReport = getReportData($conn, 30);

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
        .total-requests-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background-color: #28a745;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 44px;
            font-weight: bold;
            margin: 0 auto;
            text-align: center;
        }
        .chart-container {
            position: relative;
            width: 50%;
            margin: 0 auto;
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

            <!-- ======================= Welcome Message ================== -->
            <center>
                <div class="welcome-message">
                    <h2>Welcome Back, <?php echo htmlspecialchars($user['fname']); ?> <?php echo htmlspecialchars($user['mname']); ?>!</h2>
                </div>
            </center>

            <!-- ======================= Reports Section ================== -->
            <div class="reports">
                <h3>Request Reports</h3>
                <div class="total-requests-circle">
                    <?php echo $totalRequestsResult['total']; ?>
                </div>
                <div class="charts">
                    <div class="chart-container">
                        <canvas id="dailyChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data for the charts
        const dailyData = <?php echo json_encode($dailyReport); ?>;
        const weeklyData = <?php echo json_encode($weeklyReport); ?>;
        const monthlyData = <?php echo json_encode($monthlyReport); ?>;

        function createChart(ctx, data) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        label: 'Requests Report',
                        data: Object.values(data),
                        backgroundColor: '#28a745',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                    ticks: {
                        stepSize: 16
                    }
                        }
                    }
                }
            });
        }

        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');

        createChart(dailyCtx, dailyData);
        createChart(weeklyCtx, weeklyData);
        createChart(monthlyCtx, monthlyData);
    </script>
</body>
</html>
