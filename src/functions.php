<?php

/**
 * Generate a 6-digit numeric verification code.
 */
function generateVerificationCode(): string {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Send a verification or unsubscription email.
 */
function sendVerificationEmail(string $email, string $code, bool $isUnsubscribe = false): bool {
    if ($isUnsubscribe) {
        $subject = 'Confirm Un-subscription';
        $body = "<p>To confirm un-subscription, use this code: <strong>{$code}</strong></p>";
    } else {
        $subject = 'Your Verification Code';
        $body = "<p>Your verification code is: <strong>{$code}</strong></p>";
    }

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: no-reply@example.com' . "\r\n";

    // The mail() function requires a configured mail server (e.g., Mailpit) to work.
    return mail($email, $subject, $body, $headers);
}

/**
 * Register an email by storing it in the file.
 */
function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!in_array($email, $emails)) {
        return file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
    }
    return true; // Email is already registered
}

/**
 * Unsubscribe an email by removing it from the file.
 */
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Create a new array without the email to be removed
    $new_emails = array_filter($emails, function($e) use ($email) {
        return trim($e) !== trim($email);
    });

    // Write the updated list back to the file
    return file_put_contents($file, implode(PHP_EOL, $new_emails) . PHP_EOL, LOCK_EX) !== false;
}

/**
 * Fetch a random XKCD comic and format its data as HTML.
 */
function fetchAndFormatXKCDData(): ?string {
    // Fetch the latest comic to find the max ID number
    $latest_comic_json = @file_get_contents('https://xkcd.com/info.0.json');
    if ($latest_comic_json === false) {
        return null; // Could not fetch latest comic info
    }
    $latest_comic_info = json_decode($latest_comic_json, true);
    $max_comic_id = $latest_comic_info['num'];

    // Get a random comic ID
    $random_id = rand(1, $max_comic_id);
    $comic_url = "https://xkcd.com/{$random_id}/info.0.json";

    // Fetch the random comic's data
    $comic_json = @file_get_contents($comic_url);
    if ($comic_json === false) {
        return null; // Could not fetch random comic
    }
    $comic_data = json_decode($comic_json, true);
    $image_url = htmlspecialchars($comic_data['img']);
    $title = htmlspecialchars($comic_data['title']);
    $alt_text = htmlspecialchars($comic_data['alt']);

    // Format the email body as specified in the README
    $html = "<h2>XKCD Comic: {$title}</h2>\n";
    $html .= "<img src=\"{$image_url}\" alt=\"{$alt_text}\">\n";
    $html .= "<p><a href=\"#\" id=\"unsubscribe-button\">Unsubscribe</a></p>";

    return $html;
}

/**
 * Send the formatted XKCD updates to all registered emails.
 */
function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) {
        return;
    }

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (empty($emails)) {
        return; // No subscribers
    }

    $comic_html = fetchAndFormatXKCDData();
    if ($comic_html === null) {
        return; // Failed to get comic data
    }

    $subject = 'Your XKCD Comic';
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: no-reply@example.com' . "\r\n";

    foreach ($emails as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            mail(trim($email), $subject, $comic_html, $headers);
        }
    }
}