// Initialize the map centered on Italy
var map = L.map('map').setView([42.8333, 12.8333], 6);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>'
}).addTo(map);

var pins = [];

// Function to load all pins from the server
async function loadPins() {
    try {
        const response = await fetch('https://iknowaspot.martagenovese.com/backend/manage_pins.php', {
            method: 'GET'
        });

        const resultText = await response.text();

        try {
            const result = JSON.parse(resultText);
            if (result.status === 'success') {
                result.data.forEach(function(pin) {
                    var marker = L.marker([pin.lat, pin.lon]).addTo(map);
                    if (pin.description) {
                        marker.bindPopup(pin.description);
                    } else {
                        marker.bindPopup("Custom Pin");
                    }
                });
            } else {
                console.error('Error loading pins:', result.message);
            }
        } catch (jsonError) {
            console.error('Error parsing JSON:', jsonError);
            console.error('Raw Response:', resultText);
        }
    } catch (error) {
        console.error('Error fetching pins:', error);
    }
}

// Function to add a pin to the map and save it to the server
async function addPin(latlng, popupContent) {
    try {
        const formData = new FormData();
        formData.append('action', 'addPin');
        formData.append('lat', latlng.lat);
        formData.append('lon', latlng.lng);
        formData.append('title', popupContent);
        formData.append('content', popupContent);

        const response = await fetch('https://iknowaspot.martagenovese.com/backend/manage_pins.php', {
            method: 'POST',
            body: formData
        });

        const resultText = await response.text();

        try {
            const result = JSON.parse(resultText);
            if (result.status === 'success') {
                var marker = L.marker(latlng).addTo(map);
                if (popupContent) {
                    marker.bindPopup(popupContent);
                } else {
                    marker.bindPopup("Custom Pin");
                }
                alert('Pin added!');
            } else {
                displayMessage(result, 'addPin');
            }
        } catch (jsonError) {
            console.error('Error parsing JSON:', jsonError);
            console.error('Raw Response:', resultText);
            displayMessage({ status: 'error', message: 'Invalid response from server.' }, 'addPin');
        }
    } catch (error) {
        console.error('Error:', error);
        displayMessage({ status: 'error', message: 'An unexpected error occurred.' }, 'addPin');
    }
}

// Add event listener for adding a new pin
document.getElementById('addPin').addEventListener('click', function() {
    map.once('click', function(e) {
        var content = prompt("Enter description for this pin:", "Custom Pin");
        addPin(e.latlng, content);
    });
});

document.addEventListener('DOMContentLoaded', function() {
    loadPins();  // Load all pins from the server on page load

    var savedPins = JSON.parse(localStorage.getItem('mapPins') || '[]');
    pins = savedPins;
    savedPins.forEach(function(pin) {
        // Add the pin to the map
        var marker = L.marker(L.latLng(pin.lat, pin.lng)).addTo(map);
        if (pin.content) {
            marker.bindPopup(pin.content);
        } else {
            marker.bindPopup("Custom Pin");
        }
    });
});

function displayMessage(result, elementId) {
    const messageDiv = document.getElementById(elementId);
    if (messageDiv) {
        messageDiv.textContent = result.message;
        messageDiv.style.color = result.status === 'success' ? 'green' : 'red';
    }
}
