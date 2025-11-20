-- Auto-generated from schema-views-mysql.psd1 (map@62c9c93)
-- engine: mysql
-- table:  orders
-- Contract view for [orders]
-- Hides encrypted_customer_blob; adds UUID helpers.
CREATE OR REPLACE ALGORITHM=MERGE SQL SECURITY INVOKER VIEW vw_orders AS
SELECT
  id,
  tenant_id,
  uuid,
  uuid_bin,
  CAST(BIN_TO_UUID(uuid_bin, TRUE) AS CHAR(36)) AS uuid_text,
  CAST(LPAD(HEX(uuid_bin), 32, '0') AS CHAR(32)) AS uuid_bin_hex,
  CAST(HEX(COALESCE(uuid_bin, UNHEX(REPLACE(CAST(uuid AS CHAR(36)), '-', '')))) AS CHAR(32)) AS uuid_hex,
  public_order_no,
  user_id,
  status,
  encrypted_customer_blob_key_version,
  encrypted_customer_blob,
  UPPER(HEX(encrypted_customer_blob)) AS encrypted_customer_blob_hex,
  OCTET_LENGTH(encrypted_customer_blob) AS encrypted_customer_blob_len,
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

-- Auto-generated from schema-views-mysql.psd1 (map@62c9c93)
-- engine: mysql
-- table:  orders_funnel
-- Global funnel of orders
CREATE OR REPLACE ALGORITHM=MERGE SQL SECURITY INVOKER VIEW vw_orders_funnel AS
SELECT
  COUNT(*) AS orders_total,
  SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS pending,
  SUM(CASE WHEN status = 'paid'      THEN 1 ELSE 0 END) AS paid,
  SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
  SUM(CASE WHEN status = 'failed'    THEN 1 ELSE 0 END) AS failed,
  SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
  SUM(CASE WHEN status = 'refunded'  THEN 1 ELSE 0 END) AS refunded,
  ROUND(
    100.0 * SUM(CASE WHEN status IN ('paid','completed') THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0),
    2
  ) AS payment_conversion_pct
FROM orders;


-- Auto-generated from schema-views-mysql.psd1 (map@62c9c93)
-- engine: mysql
-- table:  orders_revenue_daily
-- Daily revenue (orders) and counts; refunds reported separately
CREATE OR REPLACE ALGORITHM=MERGE SQL SECURITY INVOKER VIEW vw_revenue_daily AS
SELECT
  DATE(created_at) AS day,
  SUM(CASE WHEN status IN ('paid','completed') THEN 1 ELSE 0 END) AS paid_orders,
  SUM(CASE WHEN status IN ('paid','completed') THEN total ELSE 0 END) AS revenue_gross,
  SUM(CASE WHEN status IN ('failed','cancelled') THEN 1 ELSE 0 END) AS lost_orders,
  SUM(CASE WHEN status IN ('failed','cancelled') THEN total ELSE 0 END) AS lost_total
FROM orders
GROUP BY DATE(created_at)
ORDER BY day DESC;

