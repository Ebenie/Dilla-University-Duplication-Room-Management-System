document.addEventListener('DOMContentLoaded', function() {
    // Get the form and input elements
    const form = document.querySelector('form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const errorDiv = document.querySelector('.alert-danger');

    form.addEventListener('submit', function(event) {
        // Clear previous errors
        errorDiv.textContent = '';

        // Validate username
        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();
        let isValid = true;

        if (username.length < 4) {
            errorDiv.textContent += 'Username must be at least 4 characters long.';
            isValid = false;
        }

        if (username === '') {
            errorDiv.textContent += 'Username is required. ';
            isValid = false;
        }

        // Validate password
        if (password.length < 8) {
            errorDiv.textContent += 'Password must be at least 8 characters long. ';
            isValid = false;
        }

        if (password === '') {
            errorDiv.textContent += 'Password is required. ';
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault(); // Prevent form submission
        }
    });
});
