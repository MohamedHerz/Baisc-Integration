# Baisc-Integration

DB schema:

```text
+------------------+
| customers        |
+------------------+
| PK  id           |
|     name         |
|     email        |
|     mobile       |
+------------------+
        1
        |
        | customer_id
        *
+------------------+
| payments         |
+------------------+
| PK  id           |
| FK  customer_id  |
|     reference    |
|     gateway ID   |
|     amount/status|
+------------------+
        1
        |
        | payment_id
        *
+------------------+
| transactions     |
+------------------+
| PK  id           |
| FK  payment_id   |
|     gateway ID   |
|     status       |
|     auth code/RRN|
+------------------+
```

### Relationships

- One customer can have many payments. Each payment belongs to one customer through `payments.customer_id`.
- One payment can have zero or many transactions. Each transaction belongs to one payment through `transactions.payment_id`.
