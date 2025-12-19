-- Auto-generated from schema-map-mysql.yaml (map@sha1:0D716345C0228A9FD8972A3D31574000D05317DB)
-- engine: mysql
-- table:  orders

CREATE INDEX idx_orders_created_at ON orders (created_at);

CREATE INDEX idx_orders_user_created ON orders (user_id, created_at);

CREATE INDEX idx_orders_tenant_user_created ON orders (tenant_id, user_id, created_at);
