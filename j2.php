<?php
session_start();
require 'vendor/autoload.php';

// Set your Africa's Talking API credentials
$username = 'server_client';
$apiKey = 'f8514f72026589e246138a9c4c38dbe39feb3ea60858de7d39a60ef9cafaed44';

// Initialize the Africa's Talking SDK
use AfricasTalking\SDK\AfricasTalking;

// Initialize the SDK
$AT = new AfricasTalking($username, $apiKey);

$sms = $AT->sms();

$host = "localhost";
$dbname = "project1";
$user = "root";
$password = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}


// Retrieve the user's phone number and user ID from the database
$userId = isset( $_SESSION['user_id']) ?  $_SESSION['user_id'] : null;
var_dump($_GET); 
$stmt = $db->prepare("SELECT phone FROM users WHERE id = ?");
$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump($user);
if ($user !== false && isset($user['phone'])) {
    $userPhone = $user['phone'];

    // Retrieve the user's transaction history
    $transactionHistory = getTransactionHistoryForUser($userId, $db);

    // Construct the SMS message based on transaction history
    $message = "Dear user, your transaction history is:\n";
    foreach ($transactionHistory as $transaction) {
        $message .= "- " . $transaction['transaction_date'] . ": " . $transaction['amount'] . "\n";
    }

    // Customize your message and send SMS
    $recipient = $userPhone;

    $result = $sms->send([
        'to' => $recipient,
        'message' => $message,
    ]);

    // Handle the result (check if the SMS was sent successfully)
    if ($result['status'] === "success") {
        echo "SMS sent successfully";
    } else {
        echo "Error sending SMS: " . $result['status'];
    }
} else {
    // Handle the situation where user data is not found
    echo "Error: User data not found or phone number is not set.";
}

// Close the database connection
$db = null;

// Function to retrieve transaction history for a user
function getTransactionHistoryForUser($userId, $db) {
    $stmt = $db->prepare("SELECT * FROM transactions WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Check if there are any transactions
    if ($stmt->rowCount() > 0) {
        // Fetch all transactions
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the transactions array
        return $transactions;
    } else {
        // Return an empty array if there are no transactions
        return [];
    }
}
?>
