-- Storefront checkout is separate from invoices: failed online payments never create hoadon.
CREATE TABLE IF NOT EXISTS checkout_sessions (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 customer_id INT NOT NULL,
 address_id INT NOT NULL,
 request_key VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 method VARCHAR(32) NOT NULL,
 amount BIGINT UNSIGNED NOT NULL,
 items JSON NOT NULL,
 address_snapshot JSON NOT NULL,
 status ENUM('PENDING','PAID','FAILED','REVIEW') NOT NULL DEFAULT 'PENDING',
 invoice_id INT NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY uq_checkout_request (customer_id, request_key),
 UNIQUE KEY uq_checkout_invoice (invoice_id),
 KEY ix_checkout_customer (customer_id, status),
 FOREIGN KEY (customer_id) REFERENCES khachhang(idkhachhang),
 FOREIGN KEY (address_id) REFERENCES thongtinnhanhang(iddiachi),
 FOREIGN KEY (invoice_id) REFERENCES hoadon(idhoadon)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS checkout_payment_attempts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 checkout_id BIGINT UNSIGNED NOT NULL,
 attempt_no TINYINT UNSIGNED NOT NULL,
 txn_ref VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 amount BIGINT UNSIGNED NOT NULL,
 create_date CHAR(14) NOT NULL,
 expire_date CHAR(14) NOT NULL,
 status ENUM('PENDING','PAID','FAILED','REVIEW') NOT NULL DEFAULT 'PENDING',
 transaction_no VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NULL,
 response_code CHAR(2) NULL,
 transaction_status CHAR(2) NULL,
 raw_response JSON NULL,
 confirmed_at DATETIME NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY uq_checkout_attempt (checkout_id, attempt_no),
 UNIQUE KEY uq_checkout_txn (txn_ref),
 UNIQUE KEY uq_checkout_gateway_txn (transaction_no),
 FOREIGN KEY (checkout_id) REFERENCES checkout_sessions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE hoadon ADD COLUMN IF NOT EXISTS trangthai_thanhtoan
 ENUM('PENDING','PAID','FAILED','REFUNDED','REVIEW') NOT NULL DEFAULT 'PENDING';
