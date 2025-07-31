<?php
// Contact form processor for OTL Website

// Set the recipient email address (change this to your actual email)
$to = "obijonstradelink@gmail.com";

// Get form data
$name = $_REQUEST['name'] ?? '';
$email = $_REQUEST['email'] ?? '';
$message = $_REQUEST['message'] ?? '';

// Set email subject
$subject = "New Contact Form Message from OTL Website";

// Set email headers
$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Create email body
$body = "New contact form submission from OTL Website:\n\n";
$body .= "Name: " . $name . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Message:\n" . $message . "\n\n";
$body .= "---\n";
$body .= "Sent from OTL Website Contact Form\n";
$body .= "Date: " . date('Y-m-d H:i:s') . "\n";

// Send email
$send = mail($to, $subject, $body, $headers);

// Return JSON response for AJAX
header('Content-Type: application/json');

if ($send) {
    echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
}
?>
