<!-- Auto-generated from schema-map-postgres.psd1 @ 62c9c93 (2025-11-20T21:38:11+01:00) -->
# Definition – orders

Orders lifecycle, totals, and encrypted customer blob.

## Columns
| Column | Type | Null | Default | Description | Notes |
|-------:|:-----|:----:|:--------|:------------|:------|
| id | BIGINT | — | AS | Surrogate primary key. |  |
| tenant_id | BIGINT | NO | — |  |  |
| uuid | CHAR(36) | NO | — | Unique external order id (UUID text). |  |
| uuid_bin | BYTEA | YES | — | UUID binary form (unique, for compact lookups). |  |
| public_order_no | VARCHAR(64) | YES | — | Human-friendly order number. |  |
| user_id | BIGINT | YES | — | Customer (FK users.id), optional (guest checkout). |  |
| status | TEXT | NO | 'pending' | Order state. | enum: pending, paid, failed, cancelled, refunded, completed |
| encrypted_customer_blob | BYTEA | YES | — | Encrypted PII/customer details. | PII: encrypted |
| encrypted_customer_blob_key_version | VARCHAR(64) | YES | — | Key version of encrypted blob. |  |
| encryption_meta | JSONB | YES | — | JSON encryption metadata. |  |
| currency | CHAR(3) | NO | — | ISO 4217 currency code. |  |
| metadata | JSONB | YES | — | JSON with auxiliary metadata. |  |
| subtotal | NUMERIC(12,2) | NO | 0 | Subtotal amount. |  |
| discount_total | NUMERIC(12,2) | NO | 0 | Discount total. |  |
| tax_total | NUMERIC(12,2) | NO | 0 | Tax total. |  |
| total | NUMERIC(12,2) | NO | 0 | Grand total. |  |
| payment_method | VARCHAR(100) | YES | — | Selected payment method. |  |
| created_at | TIMESTAMPTZ(6) | NO | CURRENT_TIMESTAMP(6) | Creation timestamp (UTC). |  |
| updated_at | TIMESTAMPTZ(6) | NO | CURRENT_TIMESTAMP(6) | Update timestamp (UTC). |  |
| version | INTEGER | NO | 0 |  |  |