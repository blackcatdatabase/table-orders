-- Auto-generated from schema-views-mysql.psd1 (map@38d5403)
-- engine: mysql
-- table:  orders
-- Contract view for [orders]
-- Hides encrypted_customer_blob; adds UUID helpers.
CREATE OR REPLACE SQL SECURITY INVOKER VIEW vw_orders AS
SELECT
  id,
  uuid,
  uuid_bin,
  BIN_TO_UUID(uuid_bin, TRUE) AS uuid_text,
  HEX(uuid_bin) AS uuid_bin_hex,
  public_order_no,
  user_id,
  status,
  encrypted_customer_blob_key_version,
  encryption_meta,
  currency,
  metadata,
  subtotal,
  discount_total,
  tax_total,
  total,
  payment_method,
  created_at,
  updated_at
FROM orders;
