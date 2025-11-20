-- Auto-generated from schema-map-mysql.psd1 (map@734a489)
-- engine: mysql
-- table:  orders
CREATE INDEX idx_orders_created_at ON orders (created_at);

CREATE INDEX idx_orders_user_created ON orders (user_id, created_at);

CREATE INDEX idx_orders_tenant_user_created ON orders (tenant_id, user_id, created_at);
