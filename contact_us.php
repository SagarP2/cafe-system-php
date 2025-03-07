<?php
// Initialize error and success messages
$errors = [];
$success_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    // Validate name
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Validate message
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }

    // If no errors, process form and send email (or handle it accordingly)
    if (empty($errors)) {
        // Here, you can send the email or store the form data
        // For now, we're setting a success message
        $success_message = 'Thank you for your message! We will get back to you soon.';
        
        // Optionally, clear form fields after success
        $name = $email = $message = '';  // Reset form fields after successful submission
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/contact.css">

    <script>
        // Function to hide the success message after a delay of 2-3 seconds
        function hideSuccessMessage() {
            setTimeout(function() {
                var successMessage = document.getElementById('success-message');
                if (successMessage) {
                    successMessage.style.display = 'none';
                }
            }, 3000); // Hide after 3 seconds
        }
    </script>
</head>

<body>
    <section id="contact" class="contact">
        <div class="contact-header">
            <h1>Contact Us</h1>
            <p>We are here to help! Reach out using the form below or visit us at our location.</p>
        </div>

        <div class="contact-container">
            <!-- Display Success or Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif (!empty($success_message)): ?>
                <div id="success-message" class="success-message">
                    <p><?php echo $success_message; ?></p>
                </div>
                <script>
                    // Call function to hide the success message after 3 seconds
                    hideSuccessMessage();
                </script>
            <?php endif; ?>

            <!-- Contact Form -->
            <form class="contact-form" method="POST">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Your Name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Your Email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <textarea name="message" placeholder="Your Message" rows="5" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                </div>

                <input type="checkbox" name="botcheck" class="hidden" style="display: none;">
                <button type="submit">Send Message</button>
            </form>
        </div>
    </section>
</body>

</html>
