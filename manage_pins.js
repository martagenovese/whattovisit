// Initialize the map centered on Italy
var map = L.map('map').setView([42.8333, 12.8333], 6);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>'
}).addTo(map);

var pins = [];

async function addPin(latlng, popupContent) {    
    try {
      // Send the pin to the server with lon, lat, content

      const formData = new FormData();
      formData.append('lat', latlng.lat);
      formData.append('lon', latlng.lng);
      formData.append('content', popupContent);

      const response = await fetch('http://192.168.1.68/manage_pins.php', {
          method: 'POST',
          body: formData
      });

      try {
          const result = JSON.parse(resultText);
          if (result.status === 'success') {
            var marker = L.marker(latlng).addTo(map);
            if (popupContent) {
                marker.bindPopup(popupContent);
            } else {
                marker.bindPopup("Custom Pin");
            }
            alert('Pins added!');
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

document.getElementById('addPin').addEventListener('click', function() {
    map.once('click', function(e) {
        var content = prompt("Enter description for this pin:", "Custom Pin");
        addPin(e.latlng, content);
    });
});

document.getElementById('loadPins').addEventListener('click', function() {
    var savedPins = JSON.parse(localStorage.getItem('mapPins') || '[]');
    pins = savedPins;
    map.eachLayer(function(layer) {
        if (layer instanceof L.Marker) {
            map.removeLayer(layer);
        }
    });
    savedPins.forEach(function(pin) {
        addPin(L.latLng(pin.lat, pin.lng), pin.content);
    });
    alert('Pins loaded!');
});

// Add a default marker for Rome
addPin(L.latLng(41.9028, 12.4964), "Rome, the capital of Italy");