<?php
declare(strict_types=1);

namespace BlackCat\Database\Packages\Orders;

use BlackCat\Database\SqlDialect;
use BlackCat\Database\Contracts\ModuleInterface;
use BlackCat\Database\Support\SqlIdentifier;
use BlackCat\Database\Support\SqlDirectoryRunner;
use BlackCat\Database\Support\SchemaIntrospector;
use BlackCat\Core\Database as Database;

final class OrdersModule implements ModuleInterface
{
    public function name(): string { return 'table-orders'; }
    public function table(): string { return 'orders'; }
    public function version(): string { return '1.0.0'; }

    /** @return string[] */
    public function dialects(): array { return [ 'mysql', 'postgres' ]; }
    /** @return string[] */
    public function dependencies(): array { return [ 'table-tenants', 'table-users' ]; }

    public static function contractView(): string { return 'vw_orders'; }

    public function install(Database $db, SqlDialect $d): void
    {
        // 1) Run schema files from ../schema for the dialect (NNN_*.sql order respected)
        SqlDirectoryRunner::run($db, $d, __DIR__ . '/../schema');

        // 2) Contract view = SELECT * FROM <table>
        $table = SqlIdentifier::qi($db, $this->table());
        $view  = SqlIdentifier::qi($db, self::contractView());

        if ($d->isMysql()) {
            $createViewSql = <<<'SQL'
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
SQL;
        } else {
            $createViewSql = <<<'SQL'
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
SQL;
        }

        if (\class_exists('\\BlackCat\\Database\\Support\\DdlGuard')) {
            (new \BlackCat\Database\Support\DdlGuard($db, $d))->applyCreateView($createViewSql);
        } else {
            // Prefer CREATE OR REPLACE VIEW (gentle on dependencies)
            $db->exec($createViewSql);
        }

    }

    public function upgrade(Database $db, SqlDialect $d, string $from): void
    {
        // Optional: generator may place module-specific upgrade steps here (e.g., data migrations).
    }

    /** Does not drop the table, only the contract (view). */
    public function uninstall(Database $db, SqlDialect $d): void
    {
        $qiV = SqlIdentifier::qi($db, self::contractView());
        try {
            $db->exec("DROP VIEW IF EXISTS {$qiV}" . ($d->isMysql() ? "" : " CASCADE"));
        } catch (\Throwable) {
            // swallow
        }
    }

    public function status(Database $db, SqlDialect $d): array
    {
        $table = $this->table();
        $view  = self::contractView();

        $hasTable = SchemaIntrospector::hasTable($db, $d, $table);
        $hasView  = SchemaIntrospector::hasView($db, $d, $view);

        // Quick index/FK check – generator injects names (case-sensitive per DB)
        $expectedIdx = [ 'idx_orders_created_at', 'idx_orders_tenant_user_created', 'idx_orders_user_created' ];
        if ($d->isMysql()) {
            // Drop PG-only index naming patterns (e.g., GIN/GiST)
            $expectedIdx = array_values(array_filter(
                $expectedIdx,
                static fn(string $n): bool => !str_starts_with($n, 'gin_') && !str_starts_with($n, 'gist_')
            ));
        }
        $expectedFk  = [ 'fk_orders_tenant', 'fk_orders_user' ];

        $haveIdx = $hasTable ? SchemaIntrospector::listIndexes($db, $d, $table)     : [];
        $haveFk  = $hasTable ? SchemaIntrospector::listForeignKeys($db, $d, $table) : [];

        $missingIdx = array_values(array_diff($expectedIdx, $haveIdx));
        $missingFk  = array_values(array_diff($expectedFk, $haveFk));

        return [
            'table'       => $hasTable,
            'view'        => $hasView,
            'missing_idx' => $missingIdx,
            'missing_fk'  => $missingFk,
            'version'     => $this->version(),
        ];
    }

    public function info(): array
    {
        return [
            'table'       => $this->table(),
            'view'        => self::contractView(),
            'columns'     => Definitions::columns(),
            'version'     => $this->version(),
            'dialects'    => [ 'mysql', 'postgres' ],
            'indexes'     => [ 'idx_orders_created_at', 'idx_orders_tenant_user_created', 'idx_orders_user_created' ],
            'foreignKeys' => [ 'fk_orders_tenant', 'fk_orders_user' ],
        ];
    }
}
