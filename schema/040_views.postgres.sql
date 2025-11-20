-- Auto-generated from schema-views-postgres.psd1 (map@9d3471b)
-- engine: postgres
-- table:  orders_revenue_daily
-- Daily revenue (orders) and counts; refunds reported separately
CREATE OR REPLACE VIEW vw_revenue_daily AS
SELECT
  date_trunc(''day'', created_at) AS day,
  COUNT(*) FILTER (WHERE status IN (''paid'',''completed'')) AS paid_orders,
  SUM(total) FILTER (WHERE status IN (''paid'',''completed'')) AS revenue_gross,
  COUNT(*) FILTER (WHERE status IN (''failed'',''cancelled'')) AS lost_orders,
  SUM(total) FILTER (WHERE status IN (''failed'',''cancelled'')) AS lost_total
FROM orders
GROUP BY 1
ORDER BY day DESC;

-- Auto-generated from schema-views-postgres.psd1 (map@9d3471b)
-- engine: postgres
-- table:  orders
-- Contract view for [orders]
-- Hides encrypted_customer_blob; PG has native uuid.
-- Adds uuid_text and uuid_hex.
CREATE OR REPLACE VIEW vw_orders AS
SELECT
  id,
  tenant_id,
  uuid,
  uuid_bin,
  uuid::text                AS uuid_text,
  UPPER(replace(uuid::text,'-','')) AS uuid_hex,
  UPPER(encode(uuid_bin,'hex'))     AS uuid_bin_hex,
  public_order_no,
  user_id,
  status,
  encrypted_customer_blob_key_version,
  encrypted_customer_blob,
  UPPER(encode(encrypted_customer_blob,'hex')) AS encrypted_customer_blob_hex,
  octet_length(encrypted_customer_blob) AS encrypted_customer_blob_len,
  encryption_meta,
  currency,
  metadata,
  subtotal,
  discount_total,
  tax_total,
  total,
  payment_method,
  created_at,
  updated_at,
  version
FROM orders;


-- Auto-generated from schema-views-postgres.psd1 (map@9d3471b)
-- engine: postgres
-- table:  orders_funnel
-- Global funnel of orders
CREATE OR REPLACE VIEW vw_orders_funnel AS
SELECT
  COUNT(*)                               AS orders_total,
  COUNT(*) FILTER (WHERE status=''pending'')   AS pending,
  COUNT(*) FILTER (WHERE status=''paid'')      AS paid,
  COUNT(*) FILTER (WHERE status=''completed'') AS completed,
  COUNT(*) FILTER (WHERE status=''failed'')    AS failed,
  COUNT(*) FILTER (WHERE status=''cancelled'') AS cancelled,
  COUNT(*) FILTER (WHERE status=''refunded'')  AS refunded,
  ROUND(100.0 * COUNT(*) FILTER (WHERE status IN (''paid'',''completed'')) / GREATEST(COUNT(*),1), 2) AS payment_conversion_pct
FROM orders;

