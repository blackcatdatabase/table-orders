-- Auto-generated from schema-map-mysql.yaml (map@sha1:B9D3BE28A74392B9B389FDAFB493BD80FA1F6FA4)
-- engine: mysql
-- table:  orders

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  uuid CHAR(36) NOT NULL UNIQUE,
  uuid_bin BINARY(16) NULL,
  public_order_no VARCHAR(64) NULL,
  user_id BIGINT UNSIGNED NULL,
  status ENUM('pending','paid','failed','cancelled','refunded','completed') NOT NULL DEFAULT 'pending',
  encrypted_customer_blob LONGBLOB NULL,
  encrypted_customer_blob_key_version VARCHAR(64) NULL,
  encryption_meta JSON NULL,
  currency CHAR(3) NOT NULL,
  metadata JSON NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount_total DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax_total DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  payment_method VARCHAR(100) NULL,
  created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  version INT UNSIGNED NOT NULL DEFAULT 0,
  INDEX idx_orders_user_id (user_id),
  INDEX idx_orders_status (status),
  INDEX idx_orders_user_status (user_id, status),
  INDEX idx_orders_tenant (tenant_id),
  INDEX idx_orders_tenant_user (tenant_id, user_id),
  UNIQUE KEY ux_orders_uuid_bin (uuid_bin),
  UNIQUE KEY ux_orders_tenant_uuid_bin (tenant_id, uuid_bin),
  UNIQUE KEY ux_orders_tenant_public_no (tenant_id, public_order_no),
  UNIQUE KEY ux_orders_tenant_id (tenant_id, id),
  CONSTRAINT chk_orders_nonneg CHECK (subtotal >= 0 AND discount_total >= 0 AND tax_total >= 0 AND total >= 0),
  CONSTRAINT chk_orders_total_eq CHECK (total = subtotal - discount_total + tax_total),
  CONSTRAINT chk_orders_currency CHECK (currency REGEXP '^[A-Z]{3}$'),
  CONSTRAINT chk_orders_version CHECK (version >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
