<?php
declare(strict_types=1);

namespace BlackCat\Database\Packages\Orders\Joins;

/**
 * Metody generované z cizích klíčů.
 *
 * Vracená struktura: [string $sqlJoinFragment, array $params]
 * Politika JOINů:
 *   - -JoinPolicy left  => vždy LEFT JOIN (výchozí)
 *   - -JoinPolicy all   => INNER JOIN, pokud VŠECHNY lokální FK sloupce jsou NOT NULL
 *   - -JoinPolicy any   => INNER JOIN, pokud ALESPOŇ JEDEN lokální FK sloupec je NOT NULL
 */
final class OrdersJoins {

    /** @internal Stručná kontrola SQL aliasu (ochrana proti nesmyslným vstupům). */
    private function assertAlias(string $s): string {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $s)) {
            throw new \InvalidArgumentException("Invalid SQL alias: {$s}");
        }
        return $s;
    }

    /** @internal Ověří oba aliasy a že se neshodují. */
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
     * @return array{0:string,1:array}
     */
    public function joinUsers(string $alias = 't', string $as = 'j0'): array {
        [$alias, $as] = $this->assertAliasPair($alias, $as);
        return [' LEFT JOIN vw_users AS ' . $as . ' ON ' . $as . '.id = ' . $alias . '.user_id' . ' ', []];
    }

}
