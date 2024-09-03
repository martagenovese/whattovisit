document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', (event) => handleFormSubmit(event, 'loginForm'));
    }
    if (registerForm) {
        registerForm.addEventListener('submit', (event) => handleFormSubmit(event, 'registerForm'));
    }

    checkLoginStatus();
});

async function handleFormSubmit(event, formId) {
    event.preventDefault();
    const formData = new FormData(event.target);

    try {
        const response = await fetch('https://iknowaspot.martagenovese.com/backend/index.php', {
            method: 'POST',
            body: formData
        });

        const resultText = await response.text();

        try {
            const result = JSON.parse(resultText);
            if (result.status === 'success') {
                localStorage.setItem('isLoggedIn', 'true');
                window.location.href = 'frontend/start.html';
            } else {
                displayMessage(result, formId);
            }
        } catch (jsonError) {
            console.error('Error parsing JSON:', jsonError);
            console.error('Raw Response:', resultText);
            displayMessage({ status: 'error', message: 'Invalid response from server.' }, formId);
        }
    } catch (error) {
        console.error('Error:', error);
        displayMessage({ status: 'error', message: 'An unexpected error occurred.' }, formId);
    }
}

function checkLoginStatus() {
    const isLoggedIn = localStorage.getItem('isLoggedIn');

    if (!isLoggedIn) {
        window.location.href = 'frontend/login.html';
    }
}

function displayMessage(result, elementId) {
    const messageDiv = document.getElementById(elementId);
    if (messageDiv) {
        messageDiv.textContent = result.message;
        messageDiv.style.color = result.status === 'success' ? 'green' : 'red';
    }
}
