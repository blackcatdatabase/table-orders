<?php
declare(strict_types=1);

namespace BlackCat\Database\Packages\Orders;

final class Definitions {
    // --- základní metadata ---
    public static function table(): string { return 'orders'; }
    public static function contractView(): string { return 'vw_orders'; }
    /** @return string[] */
    public static function columns(): array { return [ 'id', 'uuid', 'uuid_bin', 'public_order_no', 'user_id', 'status', 'encrypted_customer_blob', 'encrypted_customer_blob_key_version', 'encryption_meta', 'currency', 'metadata', 'subtotal', 'discount_total', 'tax_total', 'total', 'payment_method', 'created_at', 'updated_at', 'version' ]; }

    /**
     * Primární klíč(e) tabulky. Podporuje jednoduché i složené PK.
     * id může být "id" nebo "col1, col2".
     * @return string[]
     */
    public static function pkColumns(): array {
        $raw = 'id';
        // povol formát "a,b" i s mezerami
        $parts = array_values(array_filter(array_map(
            static fn($p) => trim($p, " \t\n\r\0\x0B`\""),
            preg_split('/\s*,\s*/', $raw ?? '')
        )));
        if (!$parts) { return [$raw]; }
        return $parts;
    }
    /** Zpětná kompatibilita: první sloupec z PK. */
    public static function pk(): string { return self::pkColumns()[0]; }

    // --- volitelná metadata ---
    public static function softDeleteColumn(): ?string {
        $c = ''; return $c !== '' ? $c : null;
    }
    public static function updatedAtColumn(): ?string {
        $c = 'updated_at'; return $c !== '' ? $c : null;
    }
    public static function versionColumn(): ?string {
        $c = 'version'; return $c !== '' ? $c : null;
    }
    /** např. "created_at DESC, id DESC" */
    public static function defaultOrder(): ?string {
        $c = 'created_at DESC, id DESC'; return $c !== '' ? $c : null;
    }

    /** @return array<int,array<int,string>> seznam unikátních klíčů */
    public static function uniqueKeys(): array { return [ [ 'uuid_bin' ], [ 'uuid' ], [ 'constraint' ], [ 'id' ] ]; }

    /** @return string[] JSON sloupce kvůli castům/operacím */
    public static function jsonColumns(): array { return [ 'encryption_meta', 'metadata' ]; }

    /** @return string[] Seznam číselných sloupců (heuristika z generátoru; bez runtime DB dotazů). */
    public static function intColumns(): array { return [ 'id', 'user_id', 'version' ]; }

    /** @return array<string,string> alias => column (pro normalizaci vstupů) */
    public static function paramAliases(): array { return []; }

    /** Hint pro repo: je sloupec s verzí opravdu číselný? (bez information_schema) */
    public static function versionIsNumeric(): bool
    {
        $v = self::versionColumn();
        return $v !== null && in_array($v, self::intColumns(), true);
    }

    // --- pomocníci ---
    public static function hasColumn(string $col): bool {
        static $set = null;
        if ($set === null) { $set = array_fill_keys(self::columns(), true); }
        return isset($set[$col]);
    }

    /**
     * identity | uuid | natural | composite
     */
    public static function pkStrategy(): string {
        $c = 'identity';
        return $c !== '' ? $c : 'natural';
    }

    public static function isIdentityPk(): bool {
        return self::pkStrategy() === 'identity';
    }

    /** True, pokud je tabulka vhodná pro testy row-locků (bez kaskád/FK, malá šíře řádku apod.). */
    public static function isRowLockSafe(): bool {
        return false;
    }

    /** Pohodlný alias – má tabulka verzi pro optimistic locking? */
    public static function supportsOptimisticLocking(): bool {
        return self::versionColumn() !== null;
    }

    /** Pro JSON casty/operace – rychlý test bez vytváření setu. */
    public static function hasJsonColumn(string $col): bool {
        static $set = null;
        if ($set === null) { $set = array_fill_keys(self::jsonColumns(), true); }
        return isset($set[$col]);
    }

    public static function isSoftDeleteEnabled(): bool { return self::softDeleteColumn() !== null; }
}
