
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Reset Link</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <style>
        .logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem; /* Adjust margin as needed */
        }
        .logo-container img {
            max-width: 100px;
            height: auto; /* Ensure image scales correctly */
        }
    </style>

</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="container" style="margin-bottom: 20%;">
       <div class="logo-container">
            <img src="b.png" alt="Logo">
        </div>
   <?php
// send_reset_link.php
include 'config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(50)); // Generate a secure token

    // Store token in database with expiration date (1 hour)
    $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
    $stmt->bind_param("sss", $token, $expiry, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Send email
        $resetLink = "http://localhost/DillaUniversity/reset_password.php?token=$token";
        $subject = "Password Reset Request";
        $message = "Click the link below to reset your password:\n\n$resetLink";
        $headers = "From: avelox6867@gmail.com";

        if (mail($email, $subject, $message, $headers)) {
            // Success message
            echo '<div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> Password reset link sent to your email address.
                  </div>';
        } else {
            // Failure message
            echo '<div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> Failed to send email.
                  </div>';
        }
    } else {
        // Email not found message
        echo '<div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle"></i> Email address not found.
              </div>';
    }
}
?>
    
</div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
