<?php
session_start();
require_once 'functions.php';

$message = '';
$show_verification_form = false;

// Handle unsubscribe email submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsubscribe_email'])) {
    $email = filter_var($_POST['unsubscribe_email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $code = generateVerificationCode();
        if (sendVerificationEmail($email, $code, true)) {
            $_SESSION['unsubscribe_email'] = $email;
            $_SESSION['unsubscribe_code'] = $code;
            $show_verification_form = true;
            $message = "A confirmation code has been sent to your email.";
        } else {
            $message = "Failed to send confirmation email.";
        }
    } else {
        $message = "Invalid email address provided.";
    }
}

// Handle unsubscribe verification code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verification_code'])) {
    if (isset($_SESSION['unsubscribe_code']) && $_POST['verification_code'] === $_SESSION['unsubscribe_code']) {
        if (unsubscribeEmail($_SESSION['unsubscribe_email'])) {
            $message = "You have been successfully unsubscribed.";
            // Clear session variables
            unset($_SESSION['unsubscribe_email']);
            unset($_SESSION['unsubscribe_code']);
        } else {
            $message = "Failed to unsubscribe. Please try again.";
        }
    } else {
        $message = "Invalid verification code.";
        $show_verification_form = true; // Show form again
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unsubscribe from XKCD Comics</title>
</head>
<body>
    <h1>Unsubscribe from Daily XKCD Comics</h1>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Unsubscribe Email Form -->
    <form action="unsubscribe.php" method="post">
        <label for="unsubscribe_email">Enter your email to unsubscribe:</label><br>
        <input type="email" id="unsubscribe_email" name="unsubscribe_email" required>
        <button type="submit" id="submit-unsubscribe">Unsubscribe</button>
    </form>

    <?php if ($show_verification_form): ?>
    <hr>
    <!-- Unsubscribe Verification Form -->
    <form action="unsubscribe.php" method="post">
        <label for="verification_code">Enter the 6-digit code sent to your email:</label><br>
        <input type="text" id="verification_code" name="verification_code" maxlength="6" required>
        <button type="submit" id="submit-verification">Verify</button>
    </form>
    <?php endif; ?>
</body>
</html>