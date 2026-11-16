CREATE DATABASE IF NOT EXISTS gate_e_checkout
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gate_e_checkout;

CREATE TABLE customers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(64) NOT NULL,
  email VARCHAR(100) NOT NULL,
  mobile VARCHAR(20) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id BIGINT UNSIGNED NOT NULL,
  reference_code VARCHAR(80) NOT NULL,
  gateway_payment_id VARCHAR(80) NULL,
  amount DECIMAL(12, 3) NOT NULL,
  currency CHAR(3) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'initiated',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_payments_reference_code (reference_code),
  UNIQUE KEY uq_payments_gateway_payment_id (gateway_payment_id),
  KEY idx_payments_customer_id (customer_id),
  CONSTRAINT fk_payments_customer
    FOREIGN KEY (customer_id) REFERENCES customers (id)
) ENGINE=InnoDB;

CREATE TABLE transactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  payment_id BIGINT UNSIGNED NOT NULL,
  gateway_transaction_id VARCHAR(80) NULL,
  status VARCHAR(30) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_transactions_gateway_id (gateway_transaction_id),
  KEY idx_transactions_payment_id (payment_id),
  CONSTRAINT fk_transactions_payment
    FOREIGN KEY (payment_id) REFERENCES payments (id)
    ON DELETE CASCADE
) ENGINE=InnoDB;
