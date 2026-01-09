<?php
// Contact form handler for The Innocent band website
// Sends form submissions to adam@theinnocent.co.uk

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("HTTP/1.1 405 Method Not Allowed");
    exit("Method not allowed");
}

// Configuration
$to_email = "adam@theinnocent.co.uk";
$from_name = "The Innocent Website";
$from_email = "adam@theinnocent.co.uk";

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Initialize response array
$response = array();

try {
    // Get and sanitize form data
    $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';

    // Validation
    $errors = array();

    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!is_valid_email($email)) {
        $errors[] = "Please enter a valid email address";
    }

    if (empty($message)) {
        $errors[] = "Message is required";
    }

    // Check for errors
    if (!empty($errors)) {
        $response['success'] = false;
        $response['errors'] = $errors;
    } else {
        // Prepare email content
        $subject = "New Booking Enquiry from " . $name;
        
        $email_body = "New booking enquiry received from The Innocent website:\n\n";
        $email_body .= "Name: " . $name . "\n";
        $email_body .= "Email: " . $email . "\n";
        $email_body .= "Subject: " . ($subject ?: 'Not specified') . "\n\n";
        $email_body .= "Message:\n" . $message . "\n\n";
        $email_body .= "---\n";
        $email_body .= "Sent from: " . $_SERVER['HTTP_HOST'] . "\n";
        $email_body .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
        $email_body .= "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
        $email_body .= "Date: " . date('Y-m-d H:i:s');

        // Email headers
        $headers = array();
        $headers[] = "From: " . $from_name . " <" . $from_email . ">";
        $headers[] = "Reply-To: " . $name . " <" . $email . ">";
        $headers[] = "Content-Type: text/plain; charset=UTF-8";
        $headers[] = "X-Mailer: PHP/" . phpversion();

        // Send email
        if (mail($to_email, $subject, $email_body, implode("\r\n", $headers))) {
            $response['success'] = true;
            $response['message'] = "Thank you for your enquiry! We will get back to you within 24 hours.";
        } else {
            $response['success'] = false;
            $response['errors'] = array("Sorry, there was an error sending your message. Please try again later or email us directly at " . $to_email);
        }
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['errors'] = array("An unexpected error occurred. Please try again later.");
    
    // Log error (you might want to implement proper logging)
    error_log("Contact form error: " . $e->getMessage());
}

// Return JSON response for AJAX requests
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle non-AJAX form submissions
if ($response['success']) {
    // Redirect back to contact page with success message
    header("Location: contact.html?sent=1");
    exit;
} else {
    // Redirect back to contact page with error
    $error_msg = implode(', ', $response['errors']);
    header("Location: contact.html?error=" . urlencode($error_msg));
    exit;
}
?>