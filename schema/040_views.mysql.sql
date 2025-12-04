-- Auto-generated from schema-views-mysql.yaml (map@4ae85c5)
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
  CAST(UPPER(SHA2(encrypted_customer_blob, 256)) AS CHAR(64)) AS encrypted_customer_blob_hex,
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
