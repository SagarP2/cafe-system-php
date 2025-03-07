<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form input variables
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $botcheck = $_POST['botcheck'] ?? '';

    // Response array
    $response = [
        'isSubmitting' => false,
        'isSuccess' => false,
        'message' => '',
    ];

    // Honeypot validation
    if (!empty($botcheck)) {
        $response['message'] = 'Bot detected. Submission rejected.';
        echo json_encode($response);
        exit;
    }

    // Input validation
    if (empty($name) || empty($email) || empty($message)) {
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address.';
        echo json_encode($response);
        exit;
    }

    // Web3Forms API submission
    $url = 'https://api.web3forms.com/submit';
    $data = [
        'access_key' => '1400f062-c664-4724-9dba-729e9a79a5b0',
        'name' => $name,
        'email' => $email,
        'message' => $message,
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        $response['message'] = 'There was an error submitting the form. Please try again later.';
        echo json_encode($response);
        exit;
    }

    $resultData = json_decode($result, true);

    if ($resultData['success'] ?? false) {
        $response['isSuccess'] = true;
        $response['message'] = 'Submitted successfully. Sagar Panchal will reach you shortly. Thank you!';
    } else {
        $response['message'] = $resultData['message'] ?? 'Submission failed. Please try again.';
    }

    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link rel="stylesheet" href="/css/home.css">
    <link rel="stylesheet" href="assets/vendors/themify-icons/css/themify-icons.css">
</head>
<body>
    <div class="section contact" id="contact">
    <h4 class="contact-title">Send A Message</h4>
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="contact-form-card">
                        
                        <form method="POST" action="" id="contactForm">
                            <!-- Honeypot field -->
                            <input type="checkbox" name="botcheck" class="hidden" style="display: none;">

                            <div class="form-group">
                                <input
                                    class="form-control"
                                    type="text"
                                    placeholder="Name *"
                                    required
                                    name="name"
                                    id="Name"
                                >
                            </div>
                            <div class="form-group">
                                <input
                                    class="form-control"
                                    type="email"
                                    placeholder="Email *"
                                    required
                                    name="email"
                                    id="Email"
                                >
                            </div>
                            <div class="form-group">
                                <textarea
                                    class="form-control"
                                    placeholder="Message *"
                                    rows="7"
                                    required
                                    name="message"
                                    id="Message"
                                ></textarea>
                            </div>

                            <div class="form-group">
                                <button
                                    type="submit"
                                    class="form-control btn btn-primary"
                                    id="Button"
                                >
                                    Send Message
                                </button>
                            </div>
                        </form>
                        <?php if (isset($response['message'])): ?>
                            <p class="form-message">
                                <?= htmlspecialchars($response['message']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>