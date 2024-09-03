<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user'])) {
    echo json_encode(['status' => 'success', 'message' => 'User is logged in']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User is not logged in']);
}
?>
