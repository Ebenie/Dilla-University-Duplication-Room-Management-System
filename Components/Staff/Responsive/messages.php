<?php
session_start();
include '../../../check.php';
checkRole('Staff');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../../../index.php");
    exit;
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "duplicationmgmt";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['id'];

// Fetch user data from the database
$sql = "SELECT username, profile, fname, mname FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['receiver'], $_POST['message'])) {
    $receiver_id = (int)$_POST['receiver'];
    $message_content = htmlspecialchars($_POST['message']);

    if ($receiver_id > 0 && !empty($message_content)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message_content);
        $stmt->execute();
        $stmt->close();
        // Redirect to the same page to avoid resubmitting the form on refresh
        header("Location: messages.php?receiver=" . $receiver_id);
        exit;
    }
}

// Fetch list of users for selecting recipient
$users_result = $conn->query("SELECT id, username,fname, mname FROM users WHERE id != $user_id");

// Fetch message history if a receiver is selected
$receiver_id = isset($_GET['receiver']) ? (int)$_GET['receiver'] : 0;
$messages_result = null;
$receiver_name = '';

if ($receiver_id > 0) {
    // Fetch receiver's username
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    $receiver_result = $stmt->get_result();
    if ($receiver = $receiver_result->fetch_assoc()) {
        $receiver_name = $receiver['username'];
    }
    $stmt->close();

    // Fetch messages
    $sql = "SELECT m.content, m.date, u.username AS sender
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.date ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();
    $messages_result = $stmt->get_result();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Dilla University</title>
    <link rel="stylesheet" href="assets/css/style.css">
      <link rel="icon" href="../../../b.png" type="image/x-icon">
    <style type="text/css">
        /* Chat Section Styles */
        .message-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 100%;
            overflow: hidden;
        }

        .message-section form {
            margin-bottom: 20px;
        }

        .message-section label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .message-section select,
        .message-section textarea,
        .message-section button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Ensures padding is included in width */
        }

        .message-section button {
            background-color: #008f20;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .message-section button:hover {
            background-color: #008f20;
        }

        .messages {
            max-height: 400px; /* Adjust based on your needs */
            overflow-y: auto; /* Scroll if messages overflow */
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .messages .message {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .messages .message:last-child {
            border-bottom: none;
        }

        .messages .message strong {
            display: block;
            margin-bottom: 5px;
        }

        .messages .message p {
            margin: 0;
        }

        .messages .message small {
            display: block;
            color: #888;
            margin-top: 5px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .message-section {
                padding: 10px;
            }

            .message-section select,
            .message-section textarea,
            .message-section button {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .messages {
                max-height: 300px; /* Adjust for smaller screens */
            }

            .message-section select,
            .message-section textarea,
            .message-section button {
                font-size: 12px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
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

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                <div class="user">
                    <?php if (isset($user['profile'])): ?>
                        <img src="../../../<?php echo htmlspecialchars($user['profile']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>">
                    <?php else: ?>
                        <img src="assets/imgs/b.png" alt="Dilla University">
                    <?php endif; ?>
                </div>
            </div>

            <div class="message-section">
                <h2>Messages</h2>
                <form action="messages.php" method="get">
                    <label for="receiver">Select Receivers</label>
                    <select name="receiver" id="receiver">
                        <?php while ($row = $users_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($receiver_id == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['fname']." ".$row['mname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit">Select</button>
                </form>

                <?php if ($receiver_id > 0): ?>
                    <h3>Chat with <?php echo htmlspecialchars($receiver_name); ?></h3>
                    <form action="messages.php?receiver=<?php echo $receiver_id; ?>" method="post">
                        <input type="hidden" name="receiver" value="<?php echo $receiver_id; ?>">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" rows="4"></textarea>
                        <button type="submit">Send</button>
                    </form>

                    <div class="messages">
                        <?php if ($messages_result && $messages_result->num_rows > 0): ?>
                            <?php while ($msg = $messages_result->fetch_assoc()): ?>
                                <div class="message">
                                    <strong><?php echo htmlspecialchars($msg['sender']); ?></strong>
                                    <p><?php echo htmlspecialchars($msg['content']); ?></p>
                                    <small><?php echo htmlspecialchars($msg['date']); ?></small>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No messages to display yet.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

  
    <script src="assets/js/main.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
