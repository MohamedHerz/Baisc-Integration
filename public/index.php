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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
  <script src="assets/app.js" defer></script>
</head>
<body>
  <main class="container py-4">
    <h1 class="mb-4">Customer details</h1>

    <form id="customer-form" method="post" action="create-payment.php" class="row g-3 mb-4">
      <div class="col-md-4">
        <label for="name" class="form-label">Name</label>
        <input id="name" name="name" class="form-control" maxlength="64" required>
      </div>
      <div class="col-md-4">
        <label for="email" class="form-label">Email</label>
        <input id="email" name="email" type="email" class="form-control" maxlength="100" required>
      </div>
      <div class="col-md-4">
        <label for="mobile" class="form-label">Mobile number</label>
        <input id="mobile" name="mobile" type="tel" class="form-control" maxlength="20" required>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">Create payment</button>
      </div>
    </form>

    <p id="form-message" class="small" role="status" aria-live="polite"></p>
    <iframe id="payment-frame" class="w-100 border rounded" title="Payment page" height="600" hidden></iframe>

    <section class="mt-5">
      <h2 class="h4 mb-3">Payments</h2>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead class="table-light">
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
              <tr><td colspan="5" class="text-center text-body-secondary py-4">No payments yet.</td></tr>
            <?php else: ?>
              <?php foreach ($payments as $payment): ?>
                <tr>
                  <td><?= htmlspecialchars($payment['name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($payment['amount'] . ' ' . $payment['currency'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($payment['status'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($payment['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><button type="button" class="view-payment btn btn-sm btn-outline-primary" data-payment-id="<?= (int) $payment['id'] ?>">View</button></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <dialog id="payment-dialog" class="border-0 rounded-3 shadow p-4">
      <h2 class="h4">Payment details</h2>
      <p id="details-message" class="small" role="status" aria-live="polite"></p>
      <dl id="payment-details" class="row"></dl>
      <button type="button" id="close-payment-dialog" class="btn btn-secondary">Close</button>
    </dialog>
  </main>
</body>
</html>
