# Definition – orders

Orders lifecycle, totals, and encrypted customer blob.

## Columns
| Column | Type | Null | Default | Description | Notes |
|-------:|:-----|:----:|:--------|:------------|:------|
| id | BIGINT UNSIGNED | — | — | Surrogate primary key. |  |
| uuid | CHAR(36) | NO | — | Unique external order id (UUID text). |  |
| uuid_bin | BINARY(16) | YES | — | UUID binary form (unique). |  |
| public_order_no | VARCHAR(64) | YES | — | Human-friendly order number. |  |
| user_id | BIGINT UNSIGNED | YES | — | Customer (FK users.id), optional (guest checkout). |  |
| status | ENUM('pending','paid','failed','cancelled','refunded','completed') | NO | ''pending'' | Order state. | enum: pending, paid, failed, cancelled, refunded, completed |
| encrypted_customer_blob | LONGBLOB | YES | — | Encrypted PII/customer details. | PII: encrypted |
| encrypted_customer_blob_key_version | VARCHAR(64) | YES | — | Key version of encrypted blob. |  |
| encryption_meta | JSON | YES | — | JSON encryption metadata. |  |
| currency | CHAR(3) | NO | — | ISO 4217 currency code. |  |
| metadata | JSON | YES | — | JSON with auxiliary metadata. |  |
| subtotal | DECIMAL(12,2) | NO | 0 | Subtotal amount. |  |
| discount_total | DECIMAL(12,2) | NO | 0 | Discount total. |  |
| tax_total | DECIMAL(12,2) | NO | 0 | Tax total. |  |
| total | DECIMAL(12,2) | NO | 0 | Grand total. |  |
| payment_method | VARCHAR(100) | YES | — | Selected payment method. |  |
| created_at | DATETIME(6) | NO | CURRENT_TIMESTAMP(6) | Creation timestamp (UTC). |  |
| updated_at | DATETIME(6) | NO | CURRENT_TIMESTAMP(6) | Update timestamp (UTC). |  |
