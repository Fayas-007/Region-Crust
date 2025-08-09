document.addEventListener('DOMContentLoaded', function () {
  const usernameInput = document.getElementById('emp_username');
  const statusText = document.getElementById('emp_username_status');

  usernameInput.addEventListener('input', function () {
    const username = usernameInput.value.trim();

    if (username.length === 0) {
      statusText.textContent = '';
      return;
    }
(fetch('ajax/check_username.php?username=' + encodeURIComponent(username))
 + encodeURIComponent(username))
      .then(response => response.json())
      .then(data => {
        if (data.exists) {
          statusText.textContent = '❌ Username already exists';
          statusText.style.color = 'red';
        } else {
          statusText.textContent = '✅ Username available';
          statusText.style.color = 'green';
        }
      })
      .catch(error => {
        console.error('Error checking username:', error);
        statusText.textContent = '⚠️ Could not check username';
        statusText.style.color = 'orange';
      });
  });
});

// form-clear.js
function clearForm() {
    const form = document.querySelector('form');
    if (form) {
        form.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]').forEach(input => input.value = '');
    }
}

