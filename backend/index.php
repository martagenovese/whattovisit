<?php
// Start or resume a session
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set Content-Type header to JSON
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: https://iknowaspot.martagenovese.com");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

require 'conf.php';
$conn = new mysqli($HOST, $USER, $PWD, $DB);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        // Registration logic
        $firstName = $_POST['firstName'] ?? '';
        $lastName = $_POST['lastName'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
            $response = ['status' => 'error', 'message' => 'All fields are required.'];
            echo json_encode($response);
            exit;
        }

        if ($password !== $confirmPassword) {
            $response = ['status' => 'error', 'message' => 'Passwords do not match.'];
            echo json_encode($response);
            exit;
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT userID FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $response = ['status' => 'error', 'message' => 'Email is already registered.'];
            $stmt->close();
            echo json_encode($response);
            exit;
        }
        $stmt->close();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $firstName, $lastName, $email, $hashedPassword);

        if ($stmt->execute()) {
            // Set session variable to indicate user is logged in
            $_SESSION['user'] = $email;
            $response = ['status' => 'success', 'message' => 'Registration successful!'];
        } else {
            $response = ['status' => 'error', 'message' => 'Registration failed. Please try again.'];
        }

        $stmt->close();
        echo json_encode($response);
        exit;

    } elseif ($action === 'login') {
        // Login logic
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $response = ['status' => 'error', 'message' => 'Email and password are required.'];
            echo json_encode($response);
            exit;
        }

        // Check if email exists and verify the password
        $stmt = $conn->prepare("SELECT userID, password FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userId, $hashedPassword);
            $stmt->fetch();
            if (password_verify($password, $hashedPassword)) {
                // Set session variable to indicate user is logged in
                $_SESSION['user'] = $email;
                $response = ['status' => 'success', 'message' => 'Login successful!'];
            } else {
                $response = ['status' => 'error', 'message' => 'Incorrect password.'];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Email not registered.'];
        }

        $stmt->close();
        echo json_encode($response);
        exit;

    } else {
        $response = ['status' => 'error', 'message' => 'Invalid action.'];
        echo json_encode($response);
        exit;
    }
} else {
    $response = ['status' => 'error', 'message' => 'Invalid request method.'];
    echo json_encode($response);
    exit;
}

$conn->close();
?>
