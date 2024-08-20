<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set Content-Type header to JSON
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'bot', 'Password.123', 'whattovisit');
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Initialize response array
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        // Handle registration
        $firstName = $_POST['firstName'] ?? '';
        $lastName = $_POST['lastName'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Basic validation
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

        // Hash the password before storing it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $firstName, $lastName, $email, $hashedPassword);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Registration successful!'];
        } else {
            $response = ['status' => 'error', 'message' => 'Registration failed. Please try again.'];
        }

        $stmt->close();
        echo json_encode($response);
        exit;

    } elseif ($action === 'login') {
        // Handle login
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Basic validation
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
        // Invalid action
        $response = ['status' => 'error', 'message' => 'Invalid action.'];
        echo json_encode($response);
        exit;
    }
} else {
    // Invalid request method
    $response = ['status' => 'error', 'message' => 'Invalid request method.'];
    echo json_encode($response);
    exit;
}

// Close the database connection
$conn->close();
?>
