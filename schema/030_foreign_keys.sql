-- Auto-generated from schema-map.psd1 (map@1e83bb6)
-- table: orders
ALTER TABLE orders ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
