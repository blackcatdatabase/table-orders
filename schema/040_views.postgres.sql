-- Auto-generated from schema-views-postgres.psd1 (map@c5e4097)
-- engine: postgres
-- table:  orders
-- Contract view for [orders]
-- Hides encrypted_customer_blob; PG has native uuid (uuid_bin removed).
-- Adds uuid_text and uuid_hex.
CREATE OR REPLACE VIEW vw_orders AS
SELECT
  id,
  uuid,
  uuid::text AS uuid_text,
  upper(replace(uuid::text, '-','')) AS uuid_hex,
  encode(uuid_bin, 'hex') AS uuid_bin_hex,
  public_order_no,
  user_id,
  status,
  encrypted_customer_blob_key_version,
  encode(encrypted_customer_blob, 'hex') AS encrypted_customer_blob_hex,
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
