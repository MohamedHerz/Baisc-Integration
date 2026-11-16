<?php
require_once dirname(__DIR__) . '/config/database.php';

$payments = $pdo->query(
    'SELECT p.id, p.amount, p.currency, p.status, p.created_at, c.name
     FROM payments p
     JOIN customers c ON c.id = p.customer_id
     ORDER BY p.created_at DESC, p.id DESC'
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Customer details</title>
  <script src="assets/app.js" defer></script>
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
  <iframe id="payment-frame" title="Payment page" width="100%" height="600" hidden></iframe>

  <h2>Payments</h2>
  <table>
    <thead>
      <tr>
        <th>Customer</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Created</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if ($payments === []): ?>
        <tr><td colspan="5">No payments yet.</td></tr>
      <?php else: ?>
        <?php foreach ($payments as $payment): ?>
          <tr>
            <td><?= htmlspecialchars($payment['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($payment['amount'] . ' ' . $payment['currency'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($payment['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($payment['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><button type="button" class="view-payment" data-payment-id="<?= (int) $payment['id'] ?>">View</button></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <dialog id="payment-dialog">
    <h2>Payment details</h2>
    <p id="details-message" role="status" aria-live="polite"></p>
    <dl id="payment-details"></dl>
    <button type="button" id="close-payment-dialog">Close</button>
  </dialog>

</body>
</html>
