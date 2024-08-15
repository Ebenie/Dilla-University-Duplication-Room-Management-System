<?php
session_start();
$err = "";

// Function to authenticate user
function authenticateUser($username, $password) {
    global $err;

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

    // Prepare SQL statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Check if user is active
        if ($row['is_active'] == 0) {
            $err = "Your account is deactivated due to too many failed login attempts.";
            $stmt->close();
            $conn->close();
            return false;
        }

        // Check if password is correct
        if (password_verify($password, $row['password'])) {
            // Reset login attempts on successful login
            $stmt = $conn->prepare("UPDATE users SET login_attempt = 0 WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();

            $_SESSION['role'] = $row['role'];
            $_SESSION['id'] = $row['id'];
            $_SESSION['dept_id'] = $row['dept_id'];
            $_SESSION['profile'] = $row['profile'];
            $_SESSION['loggedin'] = true;

            $stmt->close();
            $conn->close();
            return true;
        } else {
            // Increment login attempts on failed login
            $login_attempts = $row['login_attempt'] + 1;

            // Deactivate user if login attempts exceed 5
            if ($login_attempts >= 5) {
                $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $err = "Your account has been deactivated due to too many failed login attempts.";
            } else {
                $stmt = $conn->prepare("UPDATE users SET login_attempt = ? WHERE username = ?");
                $stmt->bind_param("is", $login_attempts, $username);
                $stmt->execute();
                $err = "Username and Password do not match!";
            }

            $stmt->close();
        }
    } else {
        $err = "Username and Password do not match!";
    }

    // Close connections and return false if authentication fails
    $conn->close();
    return false;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["submit"])) {
        // Validate and sanitize input
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

        // Validate username
        if (empty($username) || empty($password)) {
            $err = "Username and Password cannot be empty!";
        } else {
            // Authenticate user
            if (authenticateUser($username, $password)) {
                // Redirect user based on role
                switch ($_SESSION['role']) {
                    case 'Staff':
                        header("Location: Components/Staff/Responsive/staffHome.php");
                        break;
                    case 'Officer':
                        header("Location: Components/Office/Responsive/officeHome.php");
                        break;
                    case 'Duplication Room':
                        header("Location: Components/DuplicationRoom/Responsive/duplicationHome.php");
                        break;
                    case 'Duplication Room Head Office':
                        header("Location: Components/DuplicationRoomHeadOffice/Responsive/duplicationHeadHome.php");
                        break;
                    case 'Adminstrator':
                        header("Location: Components/Adminstrator/Responsive/adminHome.php");
                        break;
                    default:
                        header("Location: index.php");
                        break;
                }
                exit;
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dilla University</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="b.png" type="image/x-icon">

    <style>
        /* Add custom styles here */
        .logo {
        	margin-top: 5%;
            width: 110px; /* Adjust as needed */
            height: 130px;
        }
        .header-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100px; /* Adjust the height as needed */
            margin-bottom: 50px;
        }
        .error {
            color: red;
            font-size: 20px;
        }
        h2{
           color: #28a745;
        }
    </style>
</head>
<body>
    <header class="containers">
        <div class="header-content">
            <img src="b.png" alt="Logo" class="logo">
        </div><br>
        <center><h2>Dilla University</h2></center>
    </header>

    <div class="container mt-5">
        <form method="post" class="col-md-6 mx-auto">
            <div class="alert-danger " role="alert" >
                <?php echo htmlspecialchars($err); ?>
            </div>
            <div class="form-group">
                <label for="username"><b>Username</b></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" class="form-control" id="username" name="username">
                </div>
            </div>
            <div class="form-group">
                <label for="password"><b>Password</b></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
            </div>
            <button type="submit" class="btn btn-success form-control" name="submit">Log in</button>
        </form>
        <p class="mt-3 text-center text-success"><a href="password_reset.html">Forgot Password?</a></p>
    </div>
    <script src="validation.js"></script>
    <!-- Bootstrap JS (optional) -->
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- Font Awesome JS (optional) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

</body>
</html>
