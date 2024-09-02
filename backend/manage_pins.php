<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set Content-Type header to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


// Database connection
require 'conf.php';

$conn = new mysqli($HOST, $USER, $PWD, $DB);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Initialize response array
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'addPin') {
        $lat = $_POST['lat'] ?? '';
        $lon = $_POST['lon'] ?? '';
        $title = $_POST['title'] ?? '';
        $description = $_POST['content'] ?? '';
    }

    if (empty($lat) || empty($lon) || empty($title) || empty($description)) {
        $response = ['lat' => $lat, 'lon' => $lon, 'title' => $title, 'description' => $description];
        echo json_encode($response);
        exit;
    }

    // Insert the pin into the database
    $stmt = $conn->prepare("SELECT placeID FROM places WHERE lat = ? AND lon = ?");
    $stmt->bind_param('dd', $lat, $lon);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response = ['status' => 'error', 'message' => 'Pin already exists.'];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO places (lat, lon, title, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ddss', $lat, $lon, $title, $description);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Pin added successfully.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Failed to add pin.'];
    }

    $stmt->close();
    echo json_encode($response);
    exit;
} else {
    $response = ['status' => 'error', 'message' => 'Invalid request method.'];
    echo json_encode($response);
    exit;
}

// Close the database connection
$conn->close();
?>
