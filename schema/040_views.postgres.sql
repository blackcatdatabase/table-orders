-- Auto-generated from schema-views-postgres.psd1 (map@mtime:2025-11-27T15:36:13Z)
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
  UPPER(encode(digest(encrypted_customer_blob,'sha256'),'hex'))::char(64) AS encrypted_customer_blob_hex,
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
