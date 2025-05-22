const form = document.getElementById('registerForm');
const responseEl = document.getElementById('response');

form.addEventListener('submit', async (e) => {
  e.preventDefault();

  const name = form.name.value;
  const email = form.email.value;
  const password = form.password.value;
  const confirmPassword = form.confirmPassword.value;

  // Check if passwords match
  if (password !== confirmPassword) {
    alert('Passwords do not match!');
    return;
  }

  const data = { name, email, password };

  try {
    const res = await fetch('http://localhost:8090/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    });

    const json = await res.json();
    responseEl.textContent = JSON.stringify(json, null, 2);

    if (res.ok) {
      alert('Registration successful!');
      window.location.href = 'login.html'; 
    } else {
      alert('Registration failed: ' + (json.error || 'Unknown error'));
    }
  } catch (error) {
    responseEl.textContent = error.toString();
  }
});