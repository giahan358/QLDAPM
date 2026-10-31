ALTER TABLE checkout_payment_attempts ADD COLUMN IF NOT EXISTS last_query_at DATETIME NULL;
ALTER TABLE checkout_payment_attempts ADD COLUMN IF NOT EXISTS last_query_response JSON NULL;
