-- Auto-generated from schema-views-postgres.psd1 (map@mtime:2025-10-24T09:45:40Z)
-- engine: postgres
-- table:  orders
-- Contract view for [orders]
-- Hides encrypted_customer_blob; keeps metadata and totals.
CREATE OR REPLACE VIEW vw_orders AS
SELECT
  id,
  uuid,
  uuid_bin,
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
