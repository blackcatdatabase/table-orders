<?php
declare(strict_types=1);

namespace BlackCat\Database\Packages\Orders\Repository;

use BlackCat\Core\Database;
use BlackCat\Database\Packages\Orders\Definitions;
use BlackCat\Database\Packages\Orders\Criteria;
use BlackCat\Database\Packages\Orders\ContractRepository;

final class OrderRepository implements ContractRepository {
    public function __construct(private Database $db) {}

    /** Whitelist validních sloupců proti mass-assignmentu */
    private function filterCols(array $row): array {
        $out = [];
        foreach ($row as $k=>$v) {
            if (Definitions::hasColumn($k)) { $out[$k] = $v; }
        }
        return $out;
    }

    // ============ INSERT / BULK / UPSERT ============
    public function insert(array $row): void {
        $row = $this->filterCols($row);
        if (!$row) { return; }
        $cols = array_keys($row);
        $place = array_map(fn($c)=>":".$c, $cols);
        $sql = "INSERT INTO orders (".implode(',', $cols).") VALUES (".implode(',', $place).")";
        $params = array_combine($place, array_values($row));
        $this->db->execute($sql, $params);
    }

    /** Rychlý bulk insert (stejné sloupce pro všechny řádky) */
    public function insertMany(array $rows): void {
        if (!$rows) return;
        $rows = array_map([$this,'filterCols'], $rows);
        $cols = array_keys($rows[0]);
        $allPlace = [];
        $params = [];
        $i=0;
        foreach ($rows as $r) {
            $place = [];
            foreach ($cols as $c) {
                $key = ':p_'.$i.'_'.$c;
                $place[] = $key;
                $params[$key] = $r[$c] ?? null;
            }
            $allPlace[] = '('.implode(',', $place).')';
            $i++;
        }
        $sql = "INSERT INTO orders (".implode(',', $cols).") VALUES ".implode(',', $allPlace);
        $this->db->execute($sql, $params);
    }

    /**
     * Dialektově bezpečný UPSERT (MySQL ON DUPLICATE KEY / Postgres ON CONFLICT).
     * [] = unikátní klíče (sloupce) pro konflikty,
     * [] = které sloupce aktualizovat.
     */
    public function upsert(array $row): void {
        $row = $this->filterCols($row);
        if (!$row) return;

        $keys = [];
        $upd  = [];
        if (!$keys) { $this->insert($row); return; }

        $cols = array_keys($row);
        $place = array_map(fn($c)=>":".$c, $cols);
        $params = array_combine($place, array_values($row));

        $mysqlUpd = [];
        $pgUpd    = [];
        foreach ($upd as $c) {
            if (!Definitions::hasColumn($c)) continue;
            $mysqlUpd[] = "`$c` = VALUES(`$c`)";
            $pgUpd[]    = "\"$c\" = EXCLUDED.\"$c\"";
        }

        $sqlBase = "INSERT INTO orders (".implode(',', $cols).") VALUES (".implode(',', $place).")";
        $isMysql = $this->db->isMysql(); // očekává se v BlackCat\Core\Database
        if ($isMysql) {
            $sql = $sqlBase . " ON DUPLICATE KEY UPDATE " . implode(',', $mysqlUpd);
        } else {
            $colsEsc = array_map(fn($c)=>'"'.$c.'"', $keys);
            $sql = $sqlBase . " ON CONFLICT (".implode(',', $colsEsc).") DO UPDATE SET ".implode(',', $pgUpd);
        }
        $this->db->execute($sql, $params);
    }

    // ============ UPDATE / DELETE / RESTORE ============
    public function updateById(int|string $id, array $row): int {
        $row = $this->filterCols($row);
        if (!$row) return 0;

        $assign=[]; $params=[":id"=>$id];
        foreach ($row as $k=>$v) { $assign[]="$k = :$k"; $params[":$k"]=$v; }

        // optimistic locking, pokud existuje version sloupec
        $verCol = Definitions::versionColumn();
        $verCond = '';
        if ($verCol) {
            $assign[] = "$verCol = $verCol + 1";
            $verCond  = " AND $verCol = :__expected_version";
            if (!isset($params[':__expected_version']) && isset($row[$verCol])) {
                $params[':__expected_version'] = $row[$verCol];
            }
        }

        $sql = "UPDATE orders SET ".implode(',', $assign)." WHERE id = :id".$verCond;
        return $this->db->execute($sql, $params);
    }

    public function deleteById(int|string $id): int {
        $soft = Definitions::softDeleteColumn();
        if ($soft) {
            $params = [':id'=>$id];
            $set = "$soft = CURRENT_TIMESTAMP";
            $updAt = Definitions::updatedAtColumn();
            if ($updAt && $updAt !== $soft) { $set .= ", $updAt = CURRENT_TIMESTAMP"; }
            return $this->db->execute("UPDATE orders SET $set WHERE id = :id", $params);
        }
        return $this->db->execute("DELETE FROM orders WHERE id = :id", [':id'=>$id]);
    }

    public function restoreById(int|string $id): int {
        $soft = Definitions::softDeleteColumn();
        if (!$soft) return 0;
        $updAt = Definitions::updatedAtColumn();
        $set = "$soft = NULL";
        if ($updAt && $updAt !== $soft) { $set .= ", $updAt = CURRENT_TIMESTAMP"; }
        return $this->db->execute("UPDATE orders SET $set WHERE id = :id", [':id'=>$id]);
    }

    // ============ READ / PAGE / LOCK ============
    public function findById(int|string $id): ?array {
        $sql = "SELECT * FROM orders WHERE id = :id";
        $row = $this->db->fetchOne($sql, [':id'=>$id]);
        return $row ?: null;
    }

    public function exists(string $whereSql = '1=1', array $params = []): bool {
        $sql = "SELECT 1 FROM orders WHERE $whereSql LIMIT 1";
        return (bool)$this->db->fetchOne($sql, $params);
    }

    public function count(string $whereSql = '1=1', array $params = []): int {
        $sql = "SELECT COUNT(*) FROM orders WHERE $whereSql";
        return (int)$this->db->fetchOne($sql, $params);
    }

    /**
     * Stránkování přes Criteria (viz Criteria třída v šabloně).
     * Vrací: ['items'=>[], 'total'=>int, 'page'=>int, 'perPage'=>int]
     */
    public function paginate(Criteria $c): array {
        [$where, $params, $order, $limit, $offset, $joins] = $c->toSql();
        $order = $order ?: (Definitions::defaultOrder() ?? 'id DESC');
        $joins = $joins ?: '';

        $total = (int)$this->db->fetchOne("SELECT COUNT(*) FROM orders t $joins WHERE $where", $params);
        $items = $this->db->fetchAll("SELECT t.* FROM orders t $joins WHERE $where ORDER BY $order LIMIT $limit OFFSET $offset", $params);

        return ['items'=>$items,'total'=>$total,'page'=>$c->page(),'perPage'=>$c->perPage()];
    }

    /** Přečtení a zamknutí řádku pro transakční práci */
    public function lockById(int|string $id): ?array {
        $sql = $this->db->isMysql()
            ? "SELECT * FROM orders WHERE id = :id FOR UPDATE"
            : "SELECT * FROM orders WHERE id = :id FOR UPDATE";
        return $this->db->fetchOne($sql, [':id'=>$id]) ?: null;
    }
}
