<?php
// login.php
session_start();
$_SESSION['last_activity'] = time();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: deshbord.php");
    exit();
}

// Database credentials
$host = 'localhost';
$db = 'icon_cafe';
$user = 'root';
$pass = '';

// Establish PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize variables
$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate email and password
    if (!empty($email) && !empty($password)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Prepare SQL query to fetch user data
            $stmt = $conn->prepare("SELECT * FROM registration WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if user exists and password is correct
            if ($user && password_verify($password, $user['password'])) {
                session_start();
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['last_activity'] = time(); // Add timestamp for session timeout
                header("Location: deshbord.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Please enter a valid email address.";
        }
    } else {
        $error = "Please fill in both fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ICON Cafe</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="login-container">
        <h1>Login</h1>
        <!-- Display error message -->
        <?php if (!empty($error)): ?>
            <div class="error-message" style="color: red;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST">
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter email" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Sign up here</a>.</p>
    </div>
</body>

</html>