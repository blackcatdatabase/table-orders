<?php
declare(strict_types=1);

namespace BlackCat\Database\Packages\Orders\Joins;

use BlackCat\Database\Support\SqlIdentifier as Ident;
use BlackCat\Core\Database as Database;

/**
 * Methods generated from foreign keys.
 *
 * Return structure: [string $sqlJoinFragment, array $params]
 * Join policy:
 *   - -JoinPolicy left  => always LEFT JOIN (default)
 *   - -JoinPolicy all   => INNER JOIN if ALL local FK columns are NOT NULL
 *   - -JoinPolicy any   => INNER JOIN if AT LEAST ONE local FK column is NOT NULL
 */
final class OrdersJoins {

    /** @internal Short SQL alias validation (guards against invalid input). */
    private function assertAlias(string $s): string {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $s)) {
            throw new \InvalidArgumentException("Invalid SQL alias: {$s}");
        }
        return $s;
    }

    /** @internal Validate both aliases and ensure they differ. */
    /** @phpstan-ignore-next-line unused helper (present even when no joins are emitted) */
    private function assertAliasPair(string $alias, string $as): array {
        $alias = $this->assertAlias($alias);
        $as    = $this->assertAlias($as);
        if ($alias === $as) {
            throw new \InvalidArgumentException("Join alias must differ from base alias: {$alias}");
        }
        return [$alias, $as];
    }


    /**
     * FK: orders -> users
     * LEFT JOIN vw_users AS $as ON $as.id = $alias.user_id
     * @return array{0:string,1:array<string,mixed>}
     */
    public function joinUsers(string $alias = 't', string $as = 'j0'): array {
        [$alias, $as] = $this->assertAliasPair($alias, $as);
        return [' LEFT JOIN vw_users AS ' . $as . ' ON ' . $as . '.id = ' . $alias . '.user_id' . ' ', []];
    }
    /**
     * FK: orders -> tenants
     * LEFT JOIN vw_tenants AS $as ON $as.id = $alias.tenant_id
     * @return array{0:string,1:array<string,mixed>}
     */
    public function joinTenants(string $alias = 't', string $as = 'j1'): array {
        [$alias, $as] = $this->assertAliasPair($alias, $as);
        return [' LEFT JOIN vw_tenants AS ' . $as . ' ON ' . $as . '.id = ' . $alias . '.tenant_id' . ' ', []];
    }

}
