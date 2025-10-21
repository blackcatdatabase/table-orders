-- Auto-generated from schema-map.psd1 on 2025-10-21T02:32:05
-- table: orders
ALTER TABLE orders ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
