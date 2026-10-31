ALTER TABLE hoadon ADD COLUMN IF NOT EXISTS cancel_status ENUM('NONE','REQUESTED','APPROVED','REJECTED') NOT NULL DEFAULT 'NONE';
CREATE TABLE IF NOT EXISTS order_cancel_requests (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id INT NOT NULL UNIQUE,
 customer_id INT NOT NULL,
 status ENUM('PENDING','APPROVED','REJECTED') NOT NULL,
 reason VARCHAR(500) NOT NULL,
 decision_note VARCHAR(500) NULL,
 staff_id INT NULL,
 created_at DATETIME NOT NULL,
 decided_at DATETIME NULL,
 FOREIGN KEY(order_id) REFERENCES hoadon(idhoadon),
 FOREIGN KEY(customer_id) REFERENCES khachhang(idkhachhang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS order_refunds (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id INT NOT NULL UNIQUE,
 payment_id BIGINT UNSIGNED NOT NULL UNIQUE,
 amount BIGINT UNSIGNED NOT NULL,
 request_id VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
 status ENUM('QUEUED','SENDING','PROCESSING','SUCCEEDED','REJECTED','UNKNOWN','REVIEW') NOT NULL DEFAULT 'QUEUED',
 response_code VARCHAR(2) NULL,
 transaction_status VARCHAR(2) NULL,
 raw_request JSON NULL,
 raw_response JSON NULL,
 last_query_at DATETIME NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL,
 FOREIGN KEY(order_id) REFERENCES hoadon(idhoadon),
 FOREIGN KEY(payment_id) REFERENCES checkout_payment_attempts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS product_reviews (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id INT NOT NULL,
 customer_id INT NOT NULL,
 book_id INT NOT NULL,
 rating TINYINT UNSIGNED NOT NULL,
 comment VARCHAR(2000) NOT NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY uq_order_book_review(order_id,book_id),
 KEY ix_review_book_date(book_id,created_at),
 FOREIGN KEY(order_id) REFERENCES hoadon(idhoadon),
 FOREIGN KEY(customer_id) REFERENCES khachhang(idkhachhang),
 FOREIGN KEY(book_id) REFERENCES sach(idsach),
 CHECK(rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS product_review_images (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 review_id BIGINT UNSIGNED NOT NULL,
 filename VARCHAR(80) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
 FOREIGN KEY(review_id) REFERENCES product_reviews(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
