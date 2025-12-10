# orders

Orders lifecycle, totals, and encrypted customer blob.

## Columns
| Column | Type | Null | Default | Description |
| --- | --- | --- | --- | --- |
| created_at | mysql: DATETIME(6) / postgres: TIMESTAMPTZ(6) | NO | CURRENT_TIMESTAMP(6) | Creation timestamp (UTC). |
| currency | CHAR(3) | NO |  | ISO 4217 currency code. |
| discount_total | mysql: DECIMAL(12,2) / postgres: NUMERIC(12,2) | NO | 0 | Discount total. |
| encrypted_customer_blob | mysql: LONGBLOB / postgres: BYTEA | YES |  | Encrypted PII/customer details. |
| encrypted_customer_blob_key_version | VARCHAR(64) | YES |  | Key version of encrypted blob. |
| encryption_meta | mysql: JSON / postgres: JSONB | YES |  | JSON encryption metadata. |
| id | BIGINT | NO |  | Surrogate primary key. |
| metadata | mysql: JSON / postgres: JSONB | YES |  | JSON with auxiliary metadata. |
| payment_method | VARCHAR(100) | YES |  | Selected payment method. |
| public_order_no | VARCHAR(64) | YES |  | Human-friendly order number. |
| status | mysql: ENUM('pending','paid','failed','cancelled','refunded','completed') / postgres: TEXT | NO | pending | Order state. (enum: pending, paid, failed, cancelled, refunded, completed) |
| subtotal | mysql: DECIMAL(12,2) / postgres: NUMERIC(12,2) | NO | 0 | Subtotal amount. |
| tax_total | mysql: DECIMAL(12,2) / postgres: NUMERIC(12,2) | NO | 0 | Tax total. |
| total | mysql: DECIMAL(12,2) / postgres: NUMERIC(12,2) | NO | 0 | Grand total. |
| updated_at | mysql: DATETIME(6) / postgres: TIMESTAMPTZ(6) | NO | CURRENT_TIMESTAMP(6) | Update timestamp (UTC). |
| user_id | BIGINT | YES |  | Customer (FK users.id), optional (guest checkout). |
| uuid | CHAR(36) | NO |  | Unique external order id (UUID text). |
| uuid_bin | mysql: BINARY(16) / postgres: BYTEA | YES |  | UUID binary form (unique, for compact lookups). |

## Engine Details

### mysql

Unique keys:
| Name | Columns |
| --- | --- |
| ux_orders_tenant_id | tenant_id, id |
| ux_orders_tenant_public_no | tenant_id, public_order_no |
| ux_orders_tenant_uuid_bin | tenant_id, uuid_bin |
| ux_orders_uuid_bin | uuid_bin |

Indexes:
| Name | Columns | SQL |
| --- | --- | --- |
| idx_orders_created_at | created_at | CREATE INDEX idx_orders_created_at ON orders (created_at) |
| idx_orders_status | status | INDEX idx_orders_status (status) |
| idx_orders_tenant | tenant_id | INDEX idx_orders_tenant (tenant_id) |
| idx_orders_tenant_user | tenant_id,user_id | INDEX idx_orders_tenant_user (tenant_id, user_id) |
| idx_orders_tenant_user_created | tenant_id,user_id,created_at | CREATE INDEX idx_orders_tenant_user_created ON orders (tenant_id, user_id, created_at) |
| idx_orders_user_created | user_id,created_at | CREATE INDEX idx_orders_user_created ON orders (user_id, created_at) |
| idx_orders_user_id | user_id | INDEX idx_orders_user_id (user_id) |
| idx_orders_user_status | user_id,status | INDEX idx_orders_user_status (user_id, status) |
| ux_orders_tenant_id | tenant_id,id | UNIQUE KEY ux_orders_tenant_id (tenant_id, id) |
| ux_orders_tenant_public_no | tenant_id,public_order_no | UNIQUE KEY ux_orders_tenant_public_no (tenant_id, public_order_no) |
| ux_orders_tenant_uuid_bin | tenant_id,uuid_bin | UNIQUE KEY ux_orders_tenant_uuid_bin (tenant_id, uuid_bin) |
| ux_orders_uuid_bin | uuid_bin | UNIQUE KEY ux_orders_uuid_bin (uuid_bin) |

Foreign keys:
| Name | Columns | References | Actions |
| --- | --- | --- | --- |
| fk_orders_tenant | tenant_id | tenants(id) | ON DELETE RESTRICT |
| fk_orders_user | user_id | users(id) | ON DELETE SET |

### postgres

Unique keys:
| Name | Columns |
| --- | --- |
| ux_orders_tenant_id | tenant_id, id |
| ux_orders_tenant_public_no | tenant_id, public_order_no |
| ux_orders_tenant_uuid_bin | tenant_id, uuid_bin |
| ux_orders_uuid_bin | uuid_bin |

Indexes:
| Name | Columns | SQL |
| --- | --- | --- |
| gin_orders_metadata | metadatajsonb_path_ops | CREATE INDEX IF NOT EXISTS gin_orders_metadata      ON orders USING GIN (metadata jsonb_path_ops) |
| idx_orders_created_at | created_at | CREATE INDEX IF NOT EXISTS idx_orders_created_at ON orders (created_at) |
| idx_orders_status | status | CREATE INDEX IF NOT EXISTS idx_orders_status ON orders (status) |
| idx_orders_tenant | tenant_id | CREATE INDEX IF NOT EXISTS idx_orders_tenant ON orders (tenant_id) |
| idx_orders_tenant_user | tenant_id,user_id | CREATE INDEX IF NOT EXISTS idx_orders_tenant_user ON orders (tenant_id, user_id) |
| idx_orders_tenant_user_created | tenant_id,user_id,created_at | CREATE INDEX IF NOT EXISTS idx_orders_tenant_user_created ON orders (tenant_id, user_id, created_at) |
| idx_orders_user_created | user_id,created_at | CREATE INDEX IF NOT EXISTS idx_orders_user_created ON orders (user_id, created_at) |
| idx_orders_user_id | user_id | CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders (user_id) |
| idx_orders_user_status | user_id,status | CREATE INDEX IF NOT EXISTS idx_orders_user_status ON orders (user_id, status) |
| ux_orders_tenant_id | tenant_id,id | CREATE UNIQUE INDEX IF NOT EXISTS ux_orders_tenant_id ON orders (tenant_id, id) |
| ux_orders_tenant_public_no | tenant_id,public_order_no | CREATE UNIQUE INDEX IF NOT EXISTS ux_orders_tenant_public_no ON orders (tenant_id, public_order_no) |
| ux_orders_tenant_uuid_bin | tenant_id,uuid_bin | CREATE UNIQUE INDEX IF NOT EXISTS ux_orders_tenant_uuid_bin ON orders (tenant_id, uuid_bin) |
| ux_orders_uuid_bin | uuid_bin | CREATE UNIQUE INDEX IF NOT EXISTS ux_orders_uuid_bin ON orders (uuid_bin) |

Foreign keys:
| Name | Columns | References | Actions |
| --- | --- | --- | --- |
| fk_orders_tenant | tenant_id | tenants(id) | ON DELETE RESTRICT |
| fk_orders_user | user_id | users(id) | ON DELETE SET |

## Engine differences

## Views
| View | Engine | Flags | File |
| --- | --- | --- | --- |
| vw_orders | mysql | algorithm=MERGE, security=INVOKER | [../schema/040_views.mysql.sql](../schema/040_views.mysql.sql) |
| vw_orders_funnel | mysql | algorithm=TEMPTABLE, security=INVOKER | [../schema/040_views_joins.mysql.sql](../schema/040_views_joins.mysql.sql) |
| vw_orders_payments_latest | mysql | algorithm=TEMPTABLE, security=INVOKER | [../schema/040_views_joins.mysql.sql](../schema/040_views_joins.mysql.sql) |
| vw_orders_user_summary | mysql | algorithm=TEMPTABLE, security=INVOKER | [../schema/040_views_joins.mysql.sql](../schema/040_views_joins.mysql.sql) |
| vw_orders_with_user | mysql | algorithm=TEMPTABLE, security=INVOKER | [../schema/040_views_joins.mysql.sql](../schema/040_views_joins.mysql.sql) |
| vw_revenue_daily | mysql | algorithm=TEMPTABLE, security=INVOKER | [../schema/040_views_joins.mysql.sql](../schema/040_views_joins.mysql.sql) |
| vw_orders | postgres |  | [../schema/040_views.postgres.sql](../schema/040_views.postgres.sql) |
| vw_orders_funnel | postgres |  | [../schema/040_views_joins.postgres.sql](../schema/040_views_joins.postgres.sql) |
| vw_orders_payments_latest | postgres |  | [../schema/040_views_joins.postgres.sql](../schema/040_views_joins.postgres.sql) |
| vw_orders_user_summary | postgres |  | [../schema/040_views_joins.postgres.sql](../schema/040_views_joins.postgres.sql) |
| vw_orders_with_user | postgres |  | [../schema/040_views_joins.postgres.sql](../schema/040_views_joins.postgres.sql) |
| vw_revenue_daily | postgres |  | [../schema/040_views_joins.postgres.sql](../schema/040_views_joins.postgres.sql) |
