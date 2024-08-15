<?php
session_start();
$err = "";

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
            $stmt = $conn->prepare("INSERT INTO users (username, password, fname, mname, lname, email, gender, role, is_active, profile) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $username, $hashed_password, $fname, $mname, $lname, $email, $gender, $role, $is_active, $profile);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</head>
<body>
    <div class="container mt-5">
        <form method="post" autocomplete="off" enctype="multipart/form-data" class="col-md-6 mx-auto">
            <div class="alert-danger" role="alert">
                <?php echo htmlspecialchars($err); ?>
            </div>
            <div class="form-group">
                <label for="username"><b>Username*</b></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
            </div>
            <div class="form-group">
                <label for="password"><b>Password*</b></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>
            <div class="form-group">
                <label for="fname"><b>First Name*</b></label>
                <input type="text" class="form-control" id="fname" name="fname" required>
            </div>
            <div class="form-group">
                <label for="mname"><b>Middle Name</b></label>
                <input type="text" class="form-control" id="mname" name="mname">
            </div>
            <div class="form-group">
                <label for="lname"><b>Last Name*</b></label>
                <input type="text" class="form-control" id="lname" name="lname" required>
            </div>
            <div class="form-group">
                <label for="email"><b>Email*</b></label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="gender"><b>Gender</b></label>
                <select class="form-control" id="gender" name="gender">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="role"><b>Role</b></label>
                <select class="form-control" id="role" name="role">
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                    <option value="manager">Manager</option>
                </select>
            </div>
            <div class="form-group">
                <label for="is_active"><b>Is Active</b></label>
                <input type="checkbox" id="is_active" name="is_active">
            </div>
            <div class="form-group">
                <label for="profile"><b>Profile Picture</b></label>
                <input type="file" class="form-control-file" id="profile" name="profile">
            </div>
            <button type="submit" class="btn btn-success form-control" name="register">Register</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
