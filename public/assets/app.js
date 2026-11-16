const form = document.getElementById('customer-form');
const message = document.getElementById('form-message');
const paymentFrame = document.getElementById('payment-frame');
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
    form.hidden = true;
    paymentFrame.src = result.payment_url;
    paymentFrame.hidden = false;
  } catch {
    message.textContent = 'Could not contact the server.';
  } finally {
    submitButton.disabled = false;
  }
});

const paymentDialog = document.getElementById('payment-dialog');
const paymentDetails = document.getElementById('payment-details');
const detailsMessage = document.getElementById('details-message');

function addDetail(label, value) {
  const term = document.createElement('dt');
  const description = document.createElement('dd');
  term.textContent = label;
  description.textContent = value ?? '—';
  paymentDetails.append(term, description);
}

document.querySelectorAll('.view-payment').forEach((button) => {
  button.addEventListener('click', async () => {
    paymentDetails.replaceChildren();
    detailsMessage.textContent = 'Loading payment details...';
    paymentDialog.showModal();

    try {
      const response = await fetch(
        `payment-details.php?id=${encodeURIComponent(button.dataset.paymentId)}`,
        { headers: { Accept: 'application/json' } }
      );
      const result = await response.json();

      if (!response.ok || !result.success) {
        detailsMessage.textContent = result.message || 'Could not load payment details.';
        return;
      }

      const payment = result.payment;
      detailsMessage.textContent = '';
      Object.entries({
        Customer: payment.name,
        Email: payment.email,
        Mobile: payment.mobile,
        'Gate-E payment ID': payment.gateway_payment_id,
        Amount: `${payment.amount} ${payment.currency}`,
        Status: payment.status,
        'Payment created': payment.created_at
      }).forEach(([label, value]) => addDetail(label, value));

      if (result.transactions.length === 0) {
        addDetail('Transactions', 'None recorded');
      } else {
        result.transactions.forEach((transaction, i) => {
          Object.entries({
            ID: transaction.gateway_transaction_id
          }).forEach(([label, value]) => {
            addDetail(`Transaction ${i + 1} ${label}`, value);
          });
        });
      }
    } catch {
      detailsMessage.textContent = 'Could not contact the server.';
    }
  });
});

document.getElementById('close-payment-dialog').addEventListener('click', () => {
  paymentDialog.close();
});
