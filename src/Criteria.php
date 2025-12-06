<?php
declare(strict_types=1);

namespace BlackCat\Database\Packages\Orders;

use BlackCat\Database\Support\Criteria as BaseCriteria;
use BlackCat\Core\Database;

/**
 * Per-repo Criteria - thin layer on top of the central BlackCat\Database\Support\Criteria.
 *
 * Tokens filled by the generator:
 *  - FILTERABLE_COLUMNS_ARRAY   e.g., ["id","tenant_id","status","created_at"]
 *  - SEARCHABLE_COLUMNS_ARRAY   e.g., ["order_no","customer_email"]
 *  - DEFAULT_PER_PAGE           e.g., 50
 *  - MAX_PER_PAGE               e.g., 500
 *
 * All the "hard" logic (dialect, LIKE/ILIKE, NULLS LAST, tenancy, seek, join params,
 * andWhere()/bind() compatibility, etc.) lives in BaseCriteria. Here we only declare whitelists
 * and per-repo limits plus an optional fromDb() factory.
 */
final class Criteria extends BaseCriteria
{
    /** Hard clamp perPage to [1..maxPerPage] for this repo. */
    public function perPage(): int
    {
        $pp = (int) parent::perPage();
        $pp = max(1, $pp);
        return min($pp, $this->maxPerPage());
    }

    /** Columns that are safe to use inside WHERE filters. */
    protected function filterable(): array
    {
        return [ 'id', 'tenant_id', 'uuid', 'uuid_bin', 'public_order_no', 'user_id', 'status', 'encrypted_customer_blob', 'encrypted_customer_blob_key_version', 'encryption_meta', 'currency', 'metadata', 'subtotal', 'discount_total', 'tax_total', 'total', 'payment_method', 'created_at', 'updated_at', 'version' ];
    }

    /** Columns used for full-text LIKE/ILIKE searches. */
    protected function searchable(): array
    {
        return [ 'uuid', 'public_order_no', 'encrypted_customer_blob_key_version', 'currency', 'payment_method' ];
    }

/** Columns allowed in ORDER BY (falls back to filterable() when empty). */
protected function sortable(): array
{
    return [ 'id', 'tenant_id', 'uuid', 'public_order_no', 'user_id', 'status', 'encrypted_customer_blob_key_version', 'currency', 'subtotal', 'discount_total', 'tax_total', 'total', 'payment_method', 'created_at', 'updated_at', 'version' ];
}

    /**
     * Whitelist of joinable entities (for safe ->join() usage):
     * e.g., [ 'orders' => 'j0', 'users' => 'j1' ]
     */
    protected function joinable(): array
    {
        /** @var array<string,string> */
        return [ 'users' => 'j0', 'tenants' => 'j1' ];
    }

    /** Default page size for this repository. */
    protected function defaultPerPage(): int
    {
        return 50;
    }

    /** Maximum allowed page size. */
    protected function maxPerPage(): int
    {
        return 500;
    }

    /**
     * QoL factory: detect dialect based on the PDO driver and optionally apply a tenancy filter.
     *
     * Example:
     *   $crit = Criteria::fromDb($db, tenantId: 42)
     *                   ->search("foo")
     *                   ->orderBy("created_at","DESC");
     */
    public static function fromDb(
        Database $db,
        int|string|array|null $tenantId = null,
        string $tenantColumn = "tenant_id",
        bool $quoteIdentifiers = false
    ): static {
        $c = new static(); // previously: new self()

        $c->setDialectFromDatabase($db);
        if ($quoteIdentifiers) { $c->enableIdentifierQuoting(true); }
        if ($tenantId !== null && $tenantColumn !== '') { $c->tenant($tenantId, $tenantColumn); }

        if (\method_exists(\BlackCat\Database\Packages\Orders\Definitions::class, 'softDeleteColumn')) {
            $soft = \BlackCat\Database\Packages\Orders\Definitions::softDeleteColumn();
            if ($soft) { $c->softDelete($soft); }
        }
        return $c;
    }

    // --- Generated criteria helpers (per table) ---
    
    public function byId(int|string $id): static {
        return $this->where('id', '=', $id);
    }
    public function byIds(array $ids): static {
        if (!$ids) return $this->whereRaw('1=0');
        return $this->where('id', 'IN', array_values($ids));
    }
    /** @param string|array<int,string> $status */
    public function byStatus(string|array $status): static {
        if (is_array($status)) { return $this->where('status', 'IN', $status); }
        return $this->where('status', '=', $status);
    }
    /** @param int|string|array<int,int|string> $tenantId */
    public function forTenant(int|string|array $tenantId): static {
        if (is_array($tenantId)) { return $this->where('tenant_id', 'IN', $tenantId); }
        return $this->where('tenant_id', '=', $tenantId);
    }
    public function createdBetween(?\DateTimeInterface $from, ?\DateTimeInterface $to): static {
        return $this->between('created_at', $from, $to);
    }
    public function updatedSince(\DateTimeInterface $ts): static {
        return $this->where('updated_at', '>=', $ts);
    }
    public function byUuid(string $uuid): static {
        return $this->whereRaw('uuid = :c_uuid', ['c_uuid' => $uuid]);
    }
    public function byUuidBin(string $uuid_bin): static {
        return $this->whereRaw('uuid_bin = :c_uuid_bin', ['c_uuid_bin' => $uuid_bin]);
    }

}
