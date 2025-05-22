    const form = document.getElementById('loginForm');
    const responseEl = document.getElementById('response');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const data = {
        email: form.email.value,
        password: form.password.value,
      };

      try {
        const res = await fetch('http://localhost:8090/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        });

        const json = await res.json();
        responseEl.textContent = JSON.stringify(json, null, 2);

        if (res.ok) {
          alert('Login successful!');
          window.location.href = 'dashboard.html'; 
        } else {
          alert('Login failed: ' + (json.error || 'Unknown error'));
        }
      } catch (error) {
        responseEl.textContent = error.toString();
      }
    });