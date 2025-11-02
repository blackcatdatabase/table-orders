-- Auto-generated from schema-map-postgres.psd1 (map@db2f8b8)
-- engine: postgres
-- table:  orders
CREATE UNIQUE INDEX IF NOT EXISTS ux_orders_uuid_bin ON orders (uuid_bin) WHERE uuid_bin IS NOT NULL AND length(uuid_bin) = 16;

CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders (user_id);

CREATE INDEX IF NOT EXISTS idx_orders_status ON orders (status);

CREATE INDEX IF NOT EXISTS idx_orders_user_status ON orders (user_id, status);

CREATE INDEX IF NOT EXISTS idx_orders_created_at ON orders (created_at);

CREATE INDEX IF NOT EXISTS idx_orders_user_created ON orders (user_id, created_at);
