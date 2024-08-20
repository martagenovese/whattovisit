document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', handleFormSubmitLogin);
    }

    if (registerForm) {
        registerForm.addEventListener('submit', handleFormSubmitRegister);
    }
});

async function handleFormSubmitLogin(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    try {
        const response = await fetch('http://192.168.1.68/index.php', {
            method: 'POST',
            body: formData
        });

        const resultText = await response.text();

        try {
            const result = JSON.parse(resultText);
            if (result.status === 'success') {
                window.location.href = 'start.html'; // Redirect on success
            } else {
                displayMessage(result, 'loginMessage');
            }
        } catch (jsonError) {
            console.error('Error parsing JSON:', jsonError);
            console.error('Raw Response:', resultText);
            displayMessage({ status: 'error', message: 'Invalid response from server.' }, 'loginMessage');
        }
    } catch (error) {
        console.error('Error:', error);
        displayMessage({ status: 'error', message: 'An unexpected error occurred.' }, 'loginMessage');
    }
}

async function handleFormSubmitRegister(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    try {
        const response = await fetch('http://192.168.1.68/index.php', {
            method: 'POST',
            body: formData
        });

        const resultText = await response.text();

        try {
            const result = JSON.parse(resultText);
            if (result.status === 'success') {
                window.location.href = 'start.html'; // Redirect on success
            } else {
                displayMessage(result, 'registerMessage');
            }
        } catch (jsonError) {
            console.error('Error parsing JSON:', jsonError);
            console.error('Raw Response:', resultText);
            displayMessage({ status: 'error', message: 'Invalid response from server.' }, 'registerMessage');
        }
    } catch (error) {
        console.error('Error:', error);
        displayMessage({ status: 'error', message: 'An unexpected error occurred.' }, 'registerMessage');
    }
}

function displayMessage(result, elementId) {
    const messageDiv = document.getElementById(elementId);
    if (messageDiv) {
        messageDiv.textContent = result.message;
        messageDiv.style.color = result.status === 'success' ? 'green' : 'red';
    }
}
