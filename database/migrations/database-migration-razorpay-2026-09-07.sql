-- EnoughEdu: make Razorpay the gateway for all new payment records.
-- Run this once on an existing EnoughEdu database after making a backup.
-- Historical gateway values are intentionally preserved for audit records.
ALTER TABLE orders
MODIFY payment_gateway VARCHAR(50) DEFAULT 'razorpay',
ADD INDEX idx_orders_gateway_reference (payment_gateway, gateway_reference);

ALTER TABLE payments
MODIFY gateway VARCHAR(50) DEFAULT 'razorpay';
