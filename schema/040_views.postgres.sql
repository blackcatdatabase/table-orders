-- Auto-generated from schema-views-postgres.psd1 (map@9d3471b)
-- engine: postgres
-- table:  orders
-- Contract view for [orders]
-- Hides encrypted_customer_blob; PG has native uuid (uuid_bin removed).
-- Adds uuid_text and uuid_hex.
CREATE OR REPLACE VIEW vw_orders AS
SELECT
  id,
  uuid,
  uuid::text::char(36) AS uuid_text,
  UPPER(translate(uuid::text,'-',''))::char(32) AS uuid_hex,
  UPPER(encode(uuid_bin,'hex'))::char(32) AS uuid_bin_hex,
  public_order_no,
  user_id,
  status,
  encrypted_customer_blob_key_version,
  UPPER(encode(encrypted_customer_blob,'hex'))::char(64) AS encrypted_customer_blob_hex,
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
