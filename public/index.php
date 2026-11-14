<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Customer details</title>
</head>
<body>
  <h1>Customer details</h1>

  <form id="customer-form" method="post" action="create-payment.php">
    <p>
      <label for="name">Name</label>
      <input id="name" name="name" maxlength="64" required>
    </p>
    <p>
      <label for="email">Email</label>
      <input id="email" name="email" type="email" maxlength="100" required>
    </p>
    <p>
      <label for="mobile">Mobile number</label>
      <input id="mobile" name="mobile" type="tel" maxlength="20" required>
    </p>
    <button type="submit">Create payment</button>
  </form>

  <p id="form-message" role="status" aria-live="polite"></p>

  <script>
    const form = document.getElementById('customer-form');
    const message = document.getElementById('form-message');
    const submitButton = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      submitButton.disabled = true;
      message.textContent = '';

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { Accept: 'application/json' }
        });
        const result = await response.json();

        if (!response.ok || !result.success) {
          message.textContent = result.message || 'Could not create payment.';
          return;
        }

        message.textContent = result.message;
        form.reset();
      } catch {
        message.textContent = 'Could not contact the server.';
      } finally {
        submitButton.disabled = false;
      }
    });
  </script>
</body>
</html>
