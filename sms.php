<?php
// Include the Composer autoload file
require 'vendor/autoload.php';

$username = 'server_client';
$apiKey = 'f8514f72026589e246138a9c4c38dbe39feb3ea60858de7d39a60ef9cafaed44';

use AfricasTalking\SDK\AfricasTalking;

// Initialize the SDK
$AT = new AfricasTalking($username, $apiKey);

// Initialize the SMS service
$sms = $AT->sms();

$host = "localhost";
$user = "root";
$dbname = "project1";
$password = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Retrieve user phone numbers or other relevant information based on your conditions
$stmt = $db->prepare("SELECT phone FROM users");
$stmt->execute();

$recipients = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Retrieve the user's message from the form
$userMessage = isset($_POST['userText']) ? $_POST['userText'] : "Default message if not provided";

echo "User message: $userMessage";


// Send SMS to users with the user's message
$result = $sms->send([
    'to' => $recipients,
    'message' => $userMessage,
]);

// Handle the result (check if the SMS was sent successfully)
if ($result['status'] === "success") {
    echo "SMS sent successfully";
} else {
    echo "Error sending SMS: " . $result['status'];
}

$db = null;
?>
