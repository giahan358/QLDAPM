-- Preserve the fact that old orders used checkout-time deduction.
ALTER TABLE hoadon ADD COLUMN IF NOT EXISTS stock_deducted TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE hoadon ALTER COLUMN stock_deducted SET DEFAULT 0;
ALTER TABLE hoadon ADD COLUMN IF NOT EXISTS received_at DATETIME NULL;
UPDATE hoadon SET stock_deducted=0 WHERE trangthai=3;
