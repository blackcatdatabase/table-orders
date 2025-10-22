-- Auto-generated from schema-map.psd1 (map@6cefe8e)
-- table: orders
ALTER TABLE orders ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
