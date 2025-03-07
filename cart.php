<?php
// Enable strict error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Implement more secure session handling
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

// Start session with more secure configuration
session_start([
    'cookie_lifetime' => 86400,  // 24 hours
    'read_and_close'  => false   // Keep session open for modifications
]);

// Regenerate session ID periodically to prevent session fixation
function regenerateSessionId()
{
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } else {
        // Regenerate session ID every 30 minutes
        if (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// CSRF Protection
function generateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(64));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Establish PDO connection with improved error handling
function createDatabaseConnection()
{
    $host = 'localhost';
    $dbname = 'icon_cafe';
    $username = 'root';
    $password = '';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ]);
        return $conn;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die("A database error occurred. Please try again later.");
    }
}

// Validate and sanitize user inputs
function sanitizeInput($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Check user session validity
function checkSessionValidity()
{
    $maxLifetime = 1800; // 30 minutes

    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity']) > $maxLifetime
    ) {
        // Session expired, destroy it
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

    $_SESSION['last_activity'] = time();
}

// Calculate total cart value
function calculateCartTotal($cart)
{
    return array_reduce($cart, function ($sum, $item) {
        // Safely access price and quantity with default values
        $price = $item['price'] ?? 0;
        $quantity = $item['quantity'] ?? 1;

        // Additional validation to prevent potential errors
        $price = is_numeric($price) ? floatval($price) : 0;
        $quantity = is_numeric($quantity) ? intval($quantity) : 1;

        return $sum + ($price * $quantity);
    }, 0);
}

// Main script execution
try {
    // Regenerate session ID and validate session
    regenerateSessionId();
    checkSessionValidity();

    // Establish database connection
    $conn = createDatabaseConnection();

    // Retrieve user information
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT firstname, lastname FROM registration WHERE id = :id");
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found");
    }

    $firstName = sanitizeInput($user['firstname']);
    $lastName = sanitizeInput($user['lastname']);

    // Ensure cart is initialized
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Handle cart operations with CSRF protection
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrfToken)) {
            throw new Exception("CSRF token validation failed");
        }

        // Add to cart
        if (isset($_POST['add_to_cart'])) {
            $itemName = sanitizeInput($_POST['item_name']);
            $itemPrice = filter_input(INPUT_POST, 'item_price', FILTER_VALIDATE_FLOAT);
            $itemImage = sanitizeInput($_POST['item_image']);

            if ($itemPrice === false) {
                throw new Exception("Invalid item price");
            }

            // Check if item already exists in cart
            $itemExists = false;
            foreach ($_SESSION['cart'] as &$cartItem) {
                if ($cartItem['name'] === $itemName) {
                    $cartItem['quantity']++;
                    $itemExists = true;
                    break;
                }
            }

            // Add new item if it doesn't exist
            if (!$itemExists) {
                $_SESSION['cart'][] = [
                    'name' => $itemName,
                    'price' => $itemPrice,
                    'image_path' => $itemImage,
                    'quantity' => 1
                ];
            }
        }

        // Remove item from cart
        if (isset($_POST['remove'])) {
            $index = filter_input(INPUT_POST, 'remove', FILTER_VALIDATE_INT);
            if ($index !== false && isset($_SESSION['cart'][$index])) {
                array_splice($_SESSION['cart'], $index, 1);
            }
        }

        if (isset($_POST['update_quantity'])) {
            $index = filter_input(INPUT_POST, 'index', FILTER_VALIDATE_INT);
            $action = $_POST['action'] ?? '';
        
            if ($index !== false && isset($_SESSION['cart'][$index])) {
                $currentQuantity = $_SESSION['cart'][$index]['quantity'] ?? 1;
        
                if ($action === 'increment') {
                    $_SESSION['cart'][$index]['quantity'] = min($currentQuantity + 1, 10);
                } elseif ($action === 'decrement') {
                    $_SESSION['cart'][$index]['quantity'] = max(1, $currentQuantity - 1);
                }
        
                // Calculate updated cart total
                $newCartTotal = calculateCartTotal($_SESSION['cart']);
            }
        }
        



        // Checkout process
        if (isset($_POST['checkout'])) {
            if (empty($_SESSION['cart'])) {
                $resultMessage = 'Please add at least one item!';
            } else {
                // Process checkout (you would typically integrate with payment gateway here)
                $totalAmount = calculateCartTotal($_SESSION['cart']);
                $resultMessage = "Checkout successful! Total amount: ₹$totalAmount";

                // Clear the cart after successful checkout
                unset($_SESSION['cart']);
            }
        }
    }

    // Calculate cart total for display
    $cartTotal = calculateCartTotal($_SESSION['cart'] ?? []);
} catch (Exception $e) {
    error_log($e->getMessage());
    die("An unexpected error occurred.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - ICON Cafe</title>
    <link rel="stylesheet" href="css/cart.css">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <style>
        .payment-methods-container {
            display: none;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <header class="fixed-header">
        <div class="logo">
            <a href="deshbord.php"><img src="/img/icon cafe iogo.png" alt="Logo"></a>
        </div>
        <nav>
            <ul>
                <li>
                    <span id="user-name">
                        <?php echo $firstName . ' ' . $lastName; ?>
                    </span>
                </li>
            </ul>
        </nav>
    </header>

    <section id="cart">
        <div class="cart-container">
            <h2>Cart</h2>
            <div></div>
            <!-- Cart Items -->
            <div id="cart-items">
                <?php if (empty($_SESSION['cart'])): ?>
                    <p style="color:white; margin-left:515px">Your cart is empty.</p>
                <?php else: ?>
                    <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                        <div class="cart-item">
                            <div class="nameimg">
                                <img src="<?php echo htmlspecialchars($item['image_path'] ?? ''); ?>"
                                    alt="<?php echo htmlspecialchars($item['name'] ?? ''); ?>"
                                    class="cart-item-img" />
                                <h3><?php echo htmlspecialchars($item['name'] ?? ''); ?></h3>
                            </div>
                            <p>Price: ₹<?php echo htmlspecialchars($item['price'] ?? 0); ?></p>
                            <p>
                                Quantity:
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                                    <input type="hidden" name="action" value="decrement">
                                    <input type="submit" name="update_quantity" value="-" />
                                </form>
                                <?php echo $item['quantity'] ?? 1; ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                                    <input type="hidden" name="action" value="increment">
                                    <input type="submit" name="update_quantity" value="+" />
                                </form>
                            </p>
                            
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <button type="submit" name="remove" value="<?php echo $index; ?>" class="btn">Remove</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- Cart Summary -->
            <div class="cart-summary">
                <p>Total: ₹<span id="cart-total"><?php echo $cartTotal; ?></span></p><br><br>

                <div class="cart-actions">
                    <button class="btn" onclick="showPaymentMethods()">Checkout</button>
                </div>
            </div>

            <!-- Payment Methods -->
            <div id="payment-methods-container" class="payment-methods-container">
                <h3>Select Payment Method:</h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="payment-methods">
                        <label>
                            <input type="radio" name="payment_method" value="Credit Card" checked> Credit Card
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="Debit Card"> Debit Card
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="UPI"> UPI
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="Net Banking"> Net Banking
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="Cash on Delivery"> Cash on Delivery
                        </label>
                    </div><br>
                    <button type="submit" name="checkout" class="btn">Proceed to Payment</button>
                </form>
            </div>
        </div>
    </section>

    <script>
        function showPaymentMethods() {
            document.getElementById('payment-methods-container').style.display = 'block';
        }


    </script>

    <footer>
        <p>&copy; 2024 ICON Cafe. All rights reserved.</p>
    </footer>
</body>

</html>
