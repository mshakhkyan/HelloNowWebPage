<?php
// send-contact.php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method Not Allowed");
}

// ---- CONFIG ----
$brevoApiKey = "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";
$toEmail = "mshakhkyan@gmail.com";

// IMPORTANT:
// Use a sender email that is verified inside your Brevo account/domain.
// Example: no-reply@yourdomain.com
$senderEmail = "mshakhkyan@gmail.com";
$senderName = "Website Contact Form";
// ----------------

// Collect and sanitize form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Basic validation
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    http_response_code(400);
    exit("Please fill in all fields.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit("Invalid email address.");
}

// Build email HTML
$htmlContent = "
<html>
  <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
    <h2>New Contact Form Submission</h2>
    <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
    <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
    <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
    <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
  </body>
</html>
";

// Brevo API payload
$data = [
    "sender" => [
        "name" => $senderName,
        "email" => $senderEmail
    ],
    "to" => [
        [
            "email" => $toEmail,
            "name" => "Mher Shakhkyan"
        ]
    ],
    "replyTo" => [
        "email" => $email,
        "name" => $name
    ],
    "subject" => "Contact Form: " . $subject,
    "htmlContent" => $htmlContent
];

// Send request to Brevo
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.brevo.com/v3/smtp/email");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "accept: application/json",
    "api-key: " . $brevoApiKey,
    "content-type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    curl_close($ch);
    http_response_code(500);
    exit("cURL Error: Unable to send email.");
}

curl_close($ch);

// Handle response
if ($httpCode >= 200 && $httpCode < 300) {
    echo "success";
} else {
    http_response_code($httpCode);
    echo "Failed to send email. Response: " . $response;
}
?>