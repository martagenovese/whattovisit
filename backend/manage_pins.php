<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set Content-Type header to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'conf.php';
$conn = new mysqli($HOST, $USER, $PWD, $DB);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$response = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'addPin') {
        $lat = $_POST['lat'] ?? '';
        $lon = $_POST['lon'] ?? '';
        $title = $_POST['title'] ?? '';
        $description = $_POST['content'] ?? '';

        if (empty($lat) || empty($lon) || empty($title) || empty($description)) {
            $response = ['status' => 'error', 'message' => 'All fields are required.'];
            echo json_encode($response);
            exit;
        }

        // Check if the pin already exists
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

        // Insert the new pin into the database
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
        $response = ['status' => 'error', 'message' => 'Invalid action.'];
        echo json_encode($response);
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT lat, lon, title, description FROM places");
    $stmt->execute();
    $result = $stmt->get_result();

    $places = [];
    while ($row = $result->fetch_assoc()) {
        $places[] = $row;
    }

    $stmt->close();
    
    $response = ['status' => 'success', 'data' => $places];
    echo json_encode($response);
    exit;
} else {
    $response = ['status' => 'error', 'message' => 'Invalid request method.'];
    echo json_encode($response);
    exit;
}

$conn->close();
?>
