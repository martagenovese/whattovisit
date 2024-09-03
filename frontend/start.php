<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    // Redirect to login page if not authenticated
    header('Location: index.html');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Comic Sans MS', cursive, sans-serif;
        }
        #map {
            height: 100vh;
            width: 100%;
        }
        #controls {
            position: fixed;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            z-index: 1000;
        }
        .cute-button {
            display: block;
            width: 80px;
            height: 80px;
            margin: 10px 0;
            border-radius: 50%;
            border: none;
            background-color: #FFB3BA;
            color: #FFF;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .cute-button:hover {
            background-color: #FF69B4;
            transform: scale(1.1);
        }
        #addPin { background-color: #BAFFC9; }
        #addPin:hover { background-color: #77DD77; }
        #savePins { background-color: #BAE1FF; }
        #savePins:hover { background-color: #5DA9E9; }
        #loadPins { background-color: #FFFFBA; }
        #loadPins:hover { background-color: #FDFD96; }
    </style>
</head>
<body>
    <div id="map"></div>
    <div id="controls">
        <button id="addPin" class="cute-button">✨</button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
    <script src="../backend/manage_pins.js" defer></script>

    
    
</body>
</html>
