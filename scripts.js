document.getElementById('addUserForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    // Collect form data
    const formData = {
        username: document.getElementById('username').value,
        password: document.getElementById('password').value,
        role: document.getElementById('role').value
    };

    // Send data to the server
    fetch('process_add_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        // Display response message
        const responseMessage = document.getElementById('responseMessage');
        if (data.success) {
            responseMessage.textContent = 'User added successfully.';
            responseMessage.style.color = 'green';
            document.getElementById('addUserForm').reset(); // Clear the form
        } else {
            responseMessage.textContent = 'Error: ' + data.message;
            responseMessage.style.color = 'red';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
