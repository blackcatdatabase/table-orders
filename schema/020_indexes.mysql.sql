-- Auto-generated from schema-map-mysql.yaml (map@sha1:7AAC4013A2623AC60C658C9BF8458EFE0C7AB741)
-- engine: mysql
-- table:  orders

CREATE INDEX idx_orders_created_at ON orders (created_at);

CREATE INDEX idx_orders_user_created ON orders (user_id, created_at);

CREATE INDEX idx_orders_tenant_user_created ON orders (tenant_id, user_id, created_at);
