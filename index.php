<?php
session_start();
// Include the Composer autoload file
require 'vendor/autoload.php';

$username = 'server_client';
$apiKey = 'f8514f72026589e246138a9c4c38dbe39feb3ea60858de7d39a60ef9cafaed44';

// Initialize the Africa's Talking SDK
use AfricasTalking\SDK\AfricasTalking;

// Initialize the SDK
$AT = new AfricasTalking($username, $apiKey);

// Initialize the SMS service
$sms = $AT->sms();

// Your database connection code
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process authentication code verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['authenticationCode'])) {
    // Validate the authentication code
    $enteredCode = $_POST['authenticationCode'];
    $phone = $_POST['phone'];
    echo "Phone number from POST: $phone";

    $stmt = $conn->prepare("SELECT authentication_code FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();

    $stmt->bind_result($storedCode);
    if ($stmt->fetch()) {
        echo "Stored authentication code: $storedCode";
    } else {
        echo "Error: Phone number not found in the database.";
        exit;
    }
    $stmt->close();
    echo "Stored authentication code: $storedCode";


    if ($enteredCode != $storedCode) {
        echo "Error: Authentication code is incorrect.";
        exit;
    }

    header("Location: dashboard.html");
    exit;
}

// Process user sign-up and send authentication code
else if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['authenticationCode'])) {
    // Validate the sign-up form data
    $username = $_POST['usernameSignUp'];
    $email = $_POST['emailSignUp'];
    $phone = $_POST['PhoneSignUp'];
    $password = $_POST['passwordSignUp'];
    
    $authenticationCode = rand(1000, 9999);

    $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, authentication_code) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $phone, $password, $authenticationCode);

    if ($stmt->execute()) {
        $userId = $conn->insert_id;

        $_SESSION['user_id'] = $userId;


        // Insert initial transactions for the new user
        $initialTransactions = [
            ['user_id' => $userId, 'transaction_date' => date('Y-m-d H:i:s'), 'transaction_type' => null, 'amount' => 0.00],
        ];
        foreach ($initialTransactions as $transaction) {
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, transaction_date, transaction_type, amount) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isds", $transaction['user_id'], $transaction['transaction_date'], $transaction['transaction_type'], $transaction['amount']);
            $stmt->execute();
        }
        // User signed up successfully, send authentication code via SMS
        $message = "Your authentication code is: $authenticationCode";
        $recipients = [$phone];

        try {
            $result = $sms->send([
                'to' => $recipients,
                'message' => $message
            ]);

            if ($result['status'] === 'success') {
                echo "Data inserted successfully. Authentication code sent to your phone.";

                // Display the form for the user to enter the authentication code
                echo <<<HTML
                <form method="post" action="">
                    <label for="authenticationCode">Enter Authentication Code:</label>
                    <input type="text" name="authenticationCode" required>
                    <input type="hidden" name="phone" value="$phone">
                    <input type="submit" value="Submit">
                </form>
                HTML;
            } else {
                echo "Error sending SMS: " . $result['error'];
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Error: Invalid form submission.";
}
$conn->close();
?>
