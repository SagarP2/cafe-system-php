<?php
session_start();
// Session timeout duration in seconds (e.g., 30 minutes)
$session_timeout = 1800;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}



// Check if the session has timed out
if (time() - $_SESSION['last_activity'] > $session_timeout) {
    session_unset();     // Clear all session variables
    session_destroy();   // Destroy the session
    header('Location: login.php?timeout=1');
    exit();
}


// Establish PDO connection
try {
    $conn = new PDO("mysql:host=localhost;dbname=icon_cafe", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Retrieve user's first name and last name from the database
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT firstname, lastname FROM registration WHERE id = :id");
$stmt->bindParam(':id', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $firstName = htmlspecialchars($user['firstname']);
    $lastName = htmlspecialchars($user['lastname']);
} else {
    // Handle case where user data is not found
    $firstName = '';
    $lastName = '';
}

// Function to calculate cart information
function updateCartInfo()
{
    // Retrieve the cart from the session or initialize it as an empty array
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

    // Count the number of items in the cart
    $cartCount = count($cart);

    // Calculate the total price of items in the cart
    $total = array_reduce($cart, function ($sum, $item) {
        return $sum + $item['price'];
    }, 0);

    // Return the cart information
    return [
        'cartCount' => $cartCount,
        'total' => $total
    ];
}

// Call the function to update cart info
$cartInfo = updateCartInfo();

try {
    // Fetch menu items for specific categories
    $stmt = $conn->prepare("
        SELECT * 
        FROM menu_items 
        WHERE category IN ('Coffees', 'Sandwiches', 'Burgers & Fries') 
        ORDER BY category
    ");
    $stmt->execute();
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group menu items by category
    $menuByCategory = [];
    foreach ($menuItems as $item) {
        $menuByCategory[$item['category']][] = $item;
    }
} catch (PDOException $e) {
    die("Error fetching menu items: " . $e->getMessage());
}

// Handle adding item to the cart
if (isset($_POST['add_to_cart'])) {
    $itemName = $_POST['item_name'];
    $itemPrice = $_POST['item_price'];
    $itemImage = $_POST['item_image'];

    // Create item array
    $item = [
        'name' => $itemName,
        'price' => $itemPrice,
        'image_path' => $itemImage
    ];

    // Add the item to the session cart
    $_SESSION['cart'][] = $item;

    // Update cart info
    $cartInfo = updateCartInfo();

    // Return updated cart info as a JSON response
    echo json_encode($cartInfo);
    exit();
}
// Update last activity time
$_SESSION['last_activity'] = time();

// Prevent caching to disable browser back button after logout
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Page - ICON Cafe</title>
    <link rel="stylesheet" href="/css/home.css">

</head>

<body>
    <header class="fixed-header">
        <div class="logo">
            <a id="logo-link"><img src="/img/icon cafe iogo.png" alt="ICON Cafe Logo"></a>
        </div>

        <nav>
            <ul>
                <li><a href="cart.php">Cart (<span id="cart-count"><?php echo $cartInfo['cartCount']; ?></span> items)</a></li>
                <li>
                    <span id="user-name">
                        <?php echo $firstName . ' ' . $lastName; ?>
                    </span>
                </li>
                <li><a id="logoutButton" href="login.php">Log out</a></li>
            </ul>
        </nav>
    </header>

    <section id="home" class="hero">
        <div class="hero-content">
            <h2>Welcome to ICON Cafe</h2>
            <p>Discover the best coffee in town.</p>
            <a href="#menu" class="main-btn">Explore Our Menu</a>
        </div>
    </section>
    <section id="menu">
        <div class="menu-container">
            <h2>Our Menu</h2>

            <?php foreach ($menuByCategory as $category => $items): ?>
                <h2><?php echo htmlspecialchars($category); ?></h2>
                <div class="menu-img">
                    <?php foreach ($items as $item): ?>
                        <div class="menu-item">
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" />
                            <div class="item_name">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            </div>
                            <p>₹<?php echo htmlspecialchars($item['price']); ?></p>
                            <button class="btn" onclick="addToCart('<?php echo addslashes($item['name']); ?>', <?php echo $item['price']; ?>, '<?php echo addslashes($item['image_path']); ?>')">Add Item</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <section id="contact">
        <div class="contact-container">
            <h2>Contact Us</h2>
            <p>Get in touch or visit us at our location.</p>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 ICON Cafe. All rights reserved.</p>
    </footer>

    <script>
        function addToCart(itemName, itemPrice, itemImage) {
            // Create a new FormData object to send data
            var formData = new FormData();
            formData.append('add_to_cart', true);
            formData.append('item_name', itemName);
            formData.append('item_price', itemPrice);
            formData.append('item_image', itemImage);

            // Send the data using AJAX
            fetch('deshbord.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Update cart count in the header
                    document.getElementById('cart-count').innerText = data.cartCount;

                    // Display success message
                    const messageDiv = document.createElement('div');
                    messageDiv.innerText = itemName + " is added";
                    messageDiv.className = "success-message"; // Add a CSS class for styling
                    document.body.appendChild(messageDiv);

                    // Remove the message after 2 seconds
                    setTimeout(() => {
                        document.body.removeChild(messageDiv);
                    }, 2000);
                })
                .catch(error => console.error('Error:', error));
        }

        // Add this to your existing JavaScript
        document.getElementById('logoutButton').addEventListener('click', function(e) {
            e.preventDefault();
            // Send an AJAX request to destroy the session
            fetch('logout.php', {
                method: 'POST',
                credentials: 'same-origin'
            }).then(() => {
                window.location.href = 'login.php';
            });
        });

        // Prevent going back after logout
        window.addEventListener('load', function() {
            window.history.pushState(null, '', window.location.href);
            window.addEventListener('popstate', function() {
                window.history.pushState(null, '', window.location.href);
            });
        });

        document.getElementById('logo-link').addEventListener('click', function (event) {
    event.preventDefault(); // Prevent the default navigation
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

    </script>
</body>

</html>