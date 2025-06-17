<?php
session_start();
require_once 'functions.php';

$message = '';
$show_verification_form = false;

// Handle email submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $code = generateVerificationCode();
        if (sendVerificationEmail($email, $code)) {
            $_SESSION['verification_email'] = $email;
            $_SESSION['verification_code'] = $code;
            $show_verification_form = true;
            $message = "A verification code has been sent to your email.";
        } else {
            $message = "Failed to send verification email. Please try again.";
        }
    } else {
        $message = "Invalid email address provided.";
    }
}

// Handle verification code submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verification_code'])) {
    if (isset($_SESSION['verification_code']) && $_POST['verification_code'] === $_SESSION['verification_code']) {
        if (registerEmail($_SESSION['verification_email'])) {
            $message = "Success! You are now subscribed to daily XKCD comics.";
            // Clear session variables after successful registration
            unset($_SESSION['verification_email']);
            unset($_SESSION['verification_code']);
        } else {
            $message = "Failed to register email. It might already be registered.";
        }
    } else {
        $message = "Invalid verification code. Please try again.";
        $show_verification_form = true; // Show form again on failure
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subscribe to XKCD Comics</title>
</head>
<body>
    <h1>Subscribe to Daily XKCD Comics</h1>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Email Submission Form -->
    <form action="index.php" method="post">
        <label for="email">Enter your email to subscribe:</label><br>
        <input type="email" id="email" name="email" required>
        <button type="submit" id="submit-email">Submit</button>
    </form>

    <?php if ($show_verification_form): ?>
    <hr>
    <!-- Verification Code Form -->
    <form action="index.php" method="post">
        <label for="verification_code">Enter the 6-digit code sent to your email:</label><br>
        <input type="text" id="verification_code" name="verification_code" maxlength="6" required>
        <button type="submit" id="submit-verification">Verify</button>
    </form>
    <?php endif; ?>
</body>
</html>