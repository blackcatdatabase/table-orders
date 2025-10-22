<?php
declare(strict_types=1);

namespace BlackCat\Database\Packages\Orders;

final class Definitions {
    // --- základní metadata ---
    public static function table(): string { return 'orders'; }
    public static function contractView(): string { return 'v_orders_contract'; }
    /** @return string[] */
    public static function columns(): array { return [ 'id', 'uuid', 'uuid_bin', 'public_order_no', 'user_id', 'status', 'encrypted_customer_blob', 'encrypted_customer_blob_key_version', 'encryption_meta', 'currency', 'metadata', 'subtotal', 'discount_total', 'tax_total', 'total', 'payment_method', 'created_at', 'updated_at' ]; }
    public static function pk(): string { return 'id'; }

    // --- volitelná metadata (mohou být prázdná) ---
    public static function softDeleteColumn(): ?string {
        $c = ''; return $c !== '' ? $c : null;
    }
    public static function updatedAtColumn(): ?string {
        $c = 'updated_at'; return $c !== '' ? $c : null;
    }
    public static function versionColumn(): ?string {
        $c = ''; return $c !== '' ? $c : null; // pro optimistic locking
    }
    /** např. "created_at DESC, id DESC" */
    public static function defaultOrder(): ?string {
        $c = 'created_at DESC, id DESC'; return $c !== '' ? $c : null;
    }
    /** @return array<int,array<int,string>> seznam unikátních klíčů (sloupcových kombinací) */
    public static function uniqueKeys(): array { return []; }
    /** @return string[] JSON sloupce kvůli castům/operacím */
    public static function jsonColumns(): array { return [ 'encryption_meta', 'metadata' ]; }

    // --- pomocníci ---
    public static function hasColumn(string $col): bool {
        static $set = null;
        if ($set === null) { $set = array_fill_keys(self::columns(), true); }
        return isset($set[$col]);
    }
}
