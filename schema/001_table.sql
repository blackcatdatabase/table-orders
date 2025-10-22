-- Auto-generated from schema-map.psd1 (map@1e83bb6)
-- table: orders
CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid CHAR(36) NOT NULL UNIQUE,
  uuid_bin BINARY(16) NULL,
  public_order_no VARCHAR(64) NULL,
  user_id BIGINT UNSIGNED NULL,
  status ENUM(''pending'',''paid'',''failed'',''cancelled'',''refunded'',''completed'') NOT NULL DEFAULT ''pending'',
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
  INDEX idx_orders_user_id (user_id),
  INDEX idx_orders_status (status),
  INDEX idx_orders_user_status (user_id, status),
  INDEX idx_orders_uuid (uuid),
  UNIQUE KEY ux_orders_uuid_bin (uuid_bin),
  CONSTRAINT chk_orders_currency CHECK (currency REGEXP ''^[A-Z]{3}$'')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
