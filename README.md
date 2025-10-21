# 📦 Orders

![SQL](https://img.shields.io/badge/SQL-MySQL%208.0%2B-4479A1?logo=mysql&logoColor=white) ![License](https://img.shields.io/badge/license-BlackCat%20Proprietary-red) ![Status](https://img.shields.io/badge/status-stable-informational) ![Generated](https://img.shields.io/badge/generated-from%20schema--map-blue)

> Schema package for table **orders** (repo: $slug).

## Files
```
schema/
  001_table.sql
  020_indexes.sql
  030_foreign_keys.sql
```

## Quick apply
```bash
# Apply schema (Linux/macOS):
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < schema/001_table.sql
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < schema/020_indexes.sql
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < schema/030_foreign_keys.sql
```

```powershell
# Apply schema (Windows PowerShell):
mysql -h $env:DB_HOST -u $env:DB_USER -p$env:DB_PASS $env:DB_NAME < schema/001_table.sql
mysql -h $env:DB_HOST -u $env:DB_USER -p$env:DB_PASS $env:DB_NAME < schema/020_indexes.sql
mysql -h $env:DB_HOST -u $env:DB_USER -p$env:DB_PASS $env:DB_NAME < schema/030_foreign_keys.sql
```

## Docker quickstart
```bash
# Spin up a throwaway MySQL and apply just this package:
docker run --rm -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=app -p 3307:3306 -d mysql:8
sleep 15
mysql -h 127.0.0.1 -P 3307 -u root -proot app < schema/001_table.sql
mysql -h 127.0.0.1 -P 3307 -u root -proot app < schema/020_indexes.sql
mysql -h 127.0.0.1 -P 3307 -u root -proot app < schema/030_foreign_keys.sql
```

## Columns
| Column | Type | Null | Default | Extra |
|-------:|:-----|:----:|:--------|:------|
| id | BIGINT UNSIGNED | — | — | AUTO_INCREMENT, PK |
| uuid | CHAR(36) | NO | — |  |
| uuid_bin | BINARY(16) | YES | — |  |
| public_order_no | VARCHAR(64) | YES | — |  |
| user_id | BIGINT UNSIGNED | YES | — |  |
| status | ENUM(''pending'',''paid'',''failed'',''cancelled'',''refunded'',''completed'') | NO | '' |  |
| encrypted_customer_blob | LONGBLOB | YES | — |  |
| encrypted_customer_blob_key_version | VARCHAR(64) | YES | — |  |
| encryption_meta | JSON | YES | — |  |
| currency | CHAR(3) | NO | — |  |
| metadata | JSON | YES | — |  |
| subtotal | DECIMAL(12,2) | NO | 0 |  |
| discount_total | DECIMAL(12,2) | NO | 0 |  |
| tax_total | DECIMAL(12,2) | NO | 0 |  |
| total | DECIMAL(12,2) | NO | 0 |  |
| payment_method | VARCHAR(100) | YES | — |  |
| created_at | DATETIME(6) | NO | CURRENT_TIMESTAMP(6) |  |
| updated_at | DATETIME(6) | NO | CURRENT_TIMESTAMP(6) |  |

## Relationships
- FK → **users** via (user_id) (ON DELETE SET NULL).

```mermaid
erDiagram
  ORDERS {
    BIGINT id PK
    CHAR(36) uuid
    BINARY(16) uuid_bin
    VARCHAR(64) public_order_no
    BIGINT user_id
    ENUM(''pending'',''paid'',''failed'',''cancelled'',''refunded'',''completed'') status
    LONGBLOB encrypted_customer_blob
    VARCHAR(64) encrypted_customer_blob_key_version
    JSON encryption_meta
    CHAR(3) currency
    JSON metadata
    DECIMAL(12,2) subtotal
    DECIMAL(12,2) discount_total
    DECIMAL(12,2) tax_total
    DECIMAL(12,2) total
    VARCHAR(100) payment_method
    DATETIME(6) created_at
    DATETIME(6) updated_at
  }
  ORDERS }o--|| USERS : "user_id"
```

## Indexes
- 1 deferred index statement(s) in schema/020_indexes.sql.

## Notes
- Generated from the umbrella repository **blackcat-database** using `scripts/schema-map.psd1`.
- To change the schema, update the map and re-run the generators.

## License
Distributed under the **BlackCat Store Proprietary License v1.0**. See `LICENSE`.

