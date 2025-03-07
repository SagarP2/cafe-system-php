<?php
// MySQL connection
$host = "localhost";
$dbname = "icon_cafe";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user inputs and sanitize them
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $secondname = htmlspecialchars(trim($_POST['secondname']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirmPassword = htmlspecialchars(trim($_POST['confirm-password']));

    // Password match validation
    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // SQL query to insert user data
        $sql = "INSERT INTO registration (firstname, secondname, lastname, email, password) 
                VALUES (:firstname, :secondname, :lastname, :email, :password)";

        // Execute the SQL query
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':firstname' => $firstname,
                ':secondname' => $secondname,
                ':lastname' => $lastname,
                ':email' => $email,
                ':password' => $hashedPassword,
            ]);
            echo "<script> window.location.href = 'login.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ICON Cafe</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
    <div class="signup-container">
        <h1>Create an Account</h1>
        <form id="signupForm" action="signup.php" method="POST">
            <div class="input-group">
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" placeholder="Enter First Name..." required>
            </div>
            <div class="input-group">
                <label for="secondname">Second Name:</label>
                <input type="text" id="secondname" name="secondname" placeholder="Enter Second Name..." required>
            </div>
            <div class="input-group">
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" placeholder="Enter Last Name..." required>
            </div>
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter email.." required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter password.." required>
            </div>
            <div class="input-group">
                <label for="confirm-password">Confirm Password:</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Re-Enter password.." required>
            </div>
            <button type="submit" class="btn-signup">Sign Up</button>
        </form>
        <p class="login-link">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
