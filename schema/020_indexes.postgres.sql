-- Auto-generated from schema-map-postgres.psd1 (map@mtime:2025-10-24T09:46:38Z)
-- engine: postgres
-- table:  orders
CREATE INDEX idx_orders_user_id ON orders (user_id);

CREATE INDEX idx_orders_status ON orders (status);

CREATE INDEX idx_orders_user_status ON orders (user_id, status);

CREATE INDEX idx_orders_uuid ON orders (uuid);

CREATE INDEX idx_orders_created_at ON orders (created_at);
