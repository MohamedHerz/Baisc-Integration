<?php
require_once dirname(__DIR__) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statement = $pdo->prepare(
        'INSERT INTO customers (name, email, mobile) VALUES (?, ?, ?)'
    );
    $statement->execute([
        $_POST['name'] ?? '',
        $_POST['email'] ?? '',
        $_POST['mobile'] ?? '',
    ]);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Customer details</title>
</head>
<body>
  <h1>Customer details</h1>

  <form method="post">
    <p>
      <label for="name">Name</label>
      <input id="name" name="name" maxlength="100" required>
    </p>
    <p>
      <label for="email">Email</label>
      <input id="email" name="email" type="email" maxlength="100" required>
    </p>
    <p>
      <label for="mobile">Mobile number</label>
      <input id="mobile" name="mobile" type="tel" maxlength="20" required>
    </p>
    <button type="submit">Save details</button>
  </form>
</body>
</html>
