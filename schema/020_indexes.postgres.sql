-- Auto-generated from schema-map-postgres.yaml (map@94ebe6c)
-- engine: postgres
-- table:  orders

CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders (user_id);

CREATE INDEX IF NOT EXISTS idx_orders_status ON orders (status);

CREATE INDEX IF NOT EXISTS idx_orders_user_status ON orders (user_id, status);

CREATE INDEX IF NOT EXISTS idx_orders_created_at ON orders (created_at);

CREATE INDEX IF NOT EXISTS idx_orders_user_created ON orders (user_id, created_at);

CREATE INDEX IF NOT EXISTS gin_orders_metadata      ON orders USING GIN (metadata jsonb_path_ops);

CREATE UNIQUE INDEX IF NOT EXISTS ux_orders_tenant_uuid_bin ON orders (tenant_id, uuid_bin) WHERE uuid_bin IS NOT NULL AND length(uuid_bin) = 16;

CREATE UNIQUE INDEX IF NOT EXISTS ux_orders_tenant_public_no ON orders (tenant_id, public_order_no) WHERE public_order_no IS NOT NULL;

CREATE INDEX IF NOT EXISTS idx_orders_tenant ON orders (tenant_id);

CREATE INDEX IF NOT EXISTS idx_orders_tenant_user_created ON orders (tenant_id, user_id, created_at);

CREATE UNIQUE INDEX IF NOT EXISTS ux_orders_tenant_id ON orders (tenant_id, id);
