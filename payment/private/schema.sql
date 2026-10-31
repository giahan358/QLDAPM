-- Run once in a reviewed migration. Amounts are whole VND (never FLOAT).
-- These tables are independent of legacy hoadon; see README integration contract.
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NOT NULL,
    amount BIGINT UNSIGNED NOT NULL,
    status ENUM('PENDING','PAID','FAILED','REFUNDED','CANCELLED') NOT NULL DEFAULT 'PENDING',
    fulfillment_status ENUM('PENDING','PROCESSING','SHIPPED','DELIVERED','CANCELLED') NOT NULL DEFAULT 'PENDING',
    attempt_count INT UNSIGNED NOT NULL DEFAULT 0,
    refunded_amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
    review_required TINYINT NOT NULL DEFAULT 0,
    refund_hold TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX orders_customer (customer_id, created_at),
    CHECK (amount > 0 AND amount <= 9999999999),
    CHECK (refunded_amount <= amount)
) ENGINE=InnoDB;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    vnp_txn_ref VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
    vnp_transaction_no VARCHAR(15) CHARACTER SET ascii COLLATE ascii_bin NULL UNIQUE,
    amount BIGINT UNSIGNED NOT NULL,
    status ENUM('PENDING','PAID','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
    response_code CHAR(2) NULL,
    transaction_status CHAR(2) NULL,
    bank_code VARCHAR(20) NULL,
    pay_date CHAR(14) NULL,
    create_date CHAR(14) NOT NULL,
    expire_date CHAR(14) NOT NULL,
    create_ip VARCHAR(45) NOT NULL,
    confirmed_at DATETIME NULL,
    refunded_amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
    raw_response JSON NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    INDEX payments_order (order_id, id)
) ENGINE=InnoDB;

CREATE TABLE payment_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    payment_id BIGINT UNSIGNED NOT NULL UNIQUE,
    attempt_number INT UNSIGNED NOT NULL,
    idempotency_key VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY attempt_number (order_id, attempt_number),
    UNIQUE KEY attempt_key (order_id, idempotency_key),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (payment_id) REFERENCES payments(id)
) ENGINE=InnoDB;

CREATE TABLE refunds (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id BIGINT UNSIGNED NOT NULL,
    request_id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
    idempotency_key VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
    amount BIGINT UNSIGNED NOT NULL,
    transaction_type CHAR(2) NOT NULL,
    status ENUM('PROCESSING','UNKNOWN','SUCCEEDED','REJECTED','REVIEW') NOT NULL,
    response_code CHAR(2) NULL,
    transaction_status CHAR(2) NULL,
    vnp_transaction_no VARCHAR(15) NULL,
    raw_request JSON NOT NULL,
    raw_response JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (payment_id) REFERENCES payments(id),
    INDEX refunds_payment (payment_id, status),
    CHECK (amount > 0)
) ENGINE=InnoDB;

CREATE TABLE payment_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NULL,
    payment_id BIGINT UNSIGNED NULL,
    event VARCHAR(64) NOT NULL,
    context JSON NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX logs_payment (payment_id, created_at)
) ENGINE=InnoDB;
