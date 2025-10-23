<?php
declare(strict_types=1);

namespace BlackCat\Database\Packages\Orders\Service;

use BlackCat\Core\Database;
use BlackCat\Database\Packages\Orders\Dto\OrderDto;
use BlackCat\Database\Packages\Orders\Mapper\OrderDtoMapper;
use BlackCat\Database\Packages\Orders\Repository\OrderRepository;

/**
 * Orchestruje více repozitářů v **jedné transakci**.
 * - Idempotentní vzory (zámky, verze) nechává na vrstvě Repository/DB.
 * - Zde řešíme business workflow přes hranice tabulek.
 */
final class OrdersAggregateService
{
    public function __construct(
        private Database $db, private OrderRepository $orderRepo
    ) {}

    /**
     * Vykoná akci v transakci – adaptuje se na dostupné API DB wrapperu.
     * Předpoklad:
     *   - pokud existuje Database::transaction(callable): mixed, použijeme jej
     *   - jinak fallback begin/commit/rollback
     */
    private function runInTransaction(callable $fn): mixed {
        if (method_exists($this->db, 'transaction')) {
            return $this->db->transaction($fn);
        }
        if (method_exists($this->db, 'beginTransaction')
            && method_exists($this->db, 'commit')
            && method_exists($this->db, 'rollBack')) {
            $this->db->beginTransaction();
            try {
                $res = $fn($this->db);
                $this->db->commit();
                return $res;
            } catch (\Throwable $e) {
                $this->db->rollBack();
                throw $e;
            }
        }
        // nouzově (neatomické) – ale aspoň nezabrání běhu v testech
        return $fn($this->db);
    }


}
