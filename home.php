<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICON Cafe</title>
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <?php include('header.php'); ?>

    <section id="home" class="hero">
        <div class="hero-content">
            <h2>Welcome to ICON Cafe</h2>
            <p>Discover the best coffee in town.</p>
            <a href="login.php" ><button class="book-table">Book Table</button></a>
            <a href="login.php" ><button class="online-order">Online Order</button></a>
        </div>
    </section>

    <section id="gallery">
        <div class="gallery-container">
            <h2>Recommends</h2>
            <div class="gallery-track">
                <?php
                // Database connection
                $host = 'localhost';  // Update with your database host
                $user = 'root';       // Update with your database username
                $pass = '';           // Update with your database password
                $db_name = 'icon_cafe'; // Update with your database name

                $conn = new mysqli($host, $user, $pass, $db_name);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Fetch items from the "Recommends" category
                $query = "SELECT image_path, name, price FROM menu_items WHERE category = 'Recommends'";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="gallery-item">';
                        echo '<img src="' . $row['image_path'] . '" alt="' . $row['name'] . '">';
                        echo '<h3>' . $row['name'] . '</h3>';
                        echo '<p>₹' . $row['price'] . '</p>';
                        echo '<button class="btn"><a href="login.php"> Add item</a></button>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No recommended items found.</p>';
                }

                // Close connection
                $conn->close();
                ?>
            </div>
        </div>
    </section>

   
    <?php include('Contact.php'); ?>
    <?php include('footer.php'); ?>
</body>
</html>
