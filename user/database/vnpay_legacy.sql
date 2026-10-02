-- Migration for the existing UniBook hoadon checkout.
-- Run once after backup: mysql webbansach < user/database/vnpay_legacy.sql
-- RETIRED: current storefront uses payment/private/storefront-schema.sql.
-- Do not apply this historical adapter schema to a new install.
CREATE TABLE IF NOT EXISTS vnpay_payments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  idhoadon INT NOT NULL,
  vnp_txn_ref VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status ENUM('PENDING','PAID','FAILED','REFUNDED','REVIEW') NOT NULL DEFAULT 'PENDING',
  vnp_transaction_no VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  vnp_response_code CHAR(2) DEFAULT NULL,
  vnp_transaction_status CHAR(2) DEFAULT NULL,
  vnp_bank_code VARCHAR(20) DEFAULT NULL,
  vnp_pay_date CHAR(14) DEFAULT NULL,
  vnp_create_date CHAR(14) NOT NULL,
  vnp_expire_date CHAR(14) NOT NULL,
  raw_response JSON DEFAULT NULL,
  confirmed_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vnpay_txn_ref (vnp_txn_ref),
  UNIQUE KEY uq_vnpay_transaction_no (vnp_transaction_no),
  KEY idx_vnpay_order (idhoadon),
  CONSTRAINT fk_vnpay_hoadon FOREIGN KEY (idhoadon) REFERENCES hoadon(idhoadon)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vnpay_payment_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  idhoadon INT NOT NULL,
  payment_id BIGINT UNSIGNED NOT NULL,
  attempt_no TINYINT UNSIGNED NOT NULL,
  idempotency_key VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vnpay_attempt (idhoadon, attempt_no),
  UNIQUE KEY uq_vnpay_idempotency (idhoadon, idempotency_key),
  CONSTRAINT fk_vnpay_attempt_order FOREIGN KEY (idhoadon) REFERENCES hoadon(idhoadon),
  CONSTRAINT fk_vnpay_attempt_payment FOREIGN KEY (payment_id) REFERENCES vnpay_payments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE hoadon ADD COLUMN IF NOT EXISTS trangthai_thanhtoan ENUM('PENDING','PAID','FAILED','REFUNDED','REVIEW') NOT NULL DEFAULT 'PENDING';
