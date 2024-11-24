$(document).ready(function () {
    console.log('main script');
});

function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container');
    const toast = document.createElement('div');

    // Define toast style based on type (success or error)
    toast.style.cssText = `
        min-width: 200px;
        margin-bottom: 10px;
        padding: 10px 20px;
        color: #fff;
        border-radius: 5px;
        font-size: 14px;
        display: inline-block;
        opacity: 0.9;
        transition: opacity 0.5s ease-in-out;
    `;

    // Change color based on type
    if (type === 'success') {
        toast.style.backgroundColor = '#28a745';  // Green for success
    } else {
        toast.style.backgroundColor = '#dc3545';  // Red for error
    }

    // Set the message
    toast.textContent = message;

    // Append to container
    toastContainer.appendChild(toast);

    // Remove the toast after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.remove();
        }, 500);  // Delay to allow the fade-out animation
    }, 3000);
}

// Refresh Page Function
function RefreshPage() {
    location.reload();
}