<?php
declare(strict_types=1);

namespace BlackCat\Database\Packages\Orders\Mapper;

use BlackCat\Database\Packages\Orders\Dto\OrderDto;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Obousměrný mapovač řádek DB <-> DTO:
 * - bezpečné casty dle whitelistů sloupců (bool/int/float/json/date/binary)
 * - mapování názvů sloupců na vlastnosti DTO (COLUMNS_TO_PROPS_MAP)
 * - tolerantní k chybějícím sloupcům (ponechá null)
 */
final class OrderDtoMapper
{
    /** @var array<string,string> */
    private const COL_TO_PROP = [ 'uuid_bin' => 'uuidBin', 'public_order_no' => 'publicOrderNo', 'user_id' => 'userId', 'encrypted_customer_blob' => 'encryptedCustomerBlob', 'encrypted_customer_blob_key_version' => 'encryptedCustomerBlobKeyVersion', 'encryption_meta' => 'encryptionMeta', 'discount_total' => 'discountTotal', 'tax_total' => 'taxTotal', 'payment_method' => 'paymentMethod', 'created_at' => 'createdAt', 'updated_at' => 'updatedAt' ];
    /** @var string[] */
    private const BOOL_COLS   = [];
    /** @var string[] */
    private const INT_COLS    = [ 'id', 'user_id', 'version' ];
    /** @var string[] */
    private const FLOAT_COLS  = [ 'subtotal', 'discount_total', 'tax_total', 'total' ];
    /** @var string[] */
    private const JSON_COLS   = [ 'encryption_meta', 'metadata' ];
    /** @var string[] */
    private const DATE_COLS   = [ 'created_at', 'updated_at' ];
    /** @var string[] */
    private const BIN_COLS    = [ 'uuid_bin', 'encrypted_customer_blob' ];

    private const TZ = 'UTC';

    private static function colToProp(string $col): string {
        return self::COL_TO_PROP[$col] ?? $col;
    }
    private static function propToCol(string $prop): string {
        static $rev = null;
        if ($rev === null) { $rev = array_flip(self::COL_TO_PROP); }
        return $rev[$prop] ?? $prop;
    }

    private static function toBool(mixed $v): ?bool {
        if ($v === null || $v === '') return null;
        return match (true) {
            is_bool($v)   => $v,
            is_int($v)    => $v !== 0,
            is_string($v) => $v !== '' && $v !== '0',
            default       => (bool)$v,
        };
    }
    private static function toInt(mixed $v): ?int {
        if ($v === null || $v === '') return null;
        return (int)$v;
    }
    private static function toFloat(mixed $v): ?float {
        if ($v === null || $v === '') return null;
        return (float)$v;
    }
    private static function toDate(mixed $v): ?DateTimeImmutable {
        if ($v === null || $v === '') return null;
        $tz = new DateTimeZone(self::TZ);
        if ($v instanceof DateTimeImmutable) return $v->setTimezone($tz);
        if (is_int($v) || (is_string($v) && ctype_digit($v))) {
            return (new DateTimeImmutable('@'.(string)$v))->setTimezone($tz);
        }
        try {
            return new DateTimeImmutable((string)$v, $tz);
        } catch (\Throwable) {
            return null; // tolerantní fallback – neházej výjimky
        }
    }

    private static function decodeJson(mixed $v): ?array {
        if ($v === null) return null;
        if (is_array($v)) return $v;
        if ($v instanceof \stdClass) return (array)$v;

        if (is_string($v)) {
            $t = trim($v);
            if ($t === '' || $t === 'null') return null;
            try {
                /** @var mixed $x */
                $x = json_decode($t, true, 512, JSON_THROW_ON_ERROR);
                return is_array($x) ? $x : null;
            } catch (\JsonException) {
                $x = json_decode($t, true); // best-effort
                return is_array($x) ? $x : null;
            }
        }
        return (array)$v;
    }

    public static function fromRow(array $row): OrderDto {
        $vals = [];

        foreach ($row as $col => $val) {
            $col = (string)$col;
            $prop = self::colToProp($col);

            if (in_array($col, self::BOOL_COLS, true))      { $val = self::toBool($val); }
            elseif (in_array($col, self::INT_COLS, true))   { $val = self::toInt($val); }
            elseif (in_array($col, self::FLOAT_COLS, true)) { $val = self::toFloat($val); }
            elseif (in_array($col, self::JSON_COLS, true))  { $val = self::decodeJson($val); }
            else {
                $isDate = in_array($col, self::DATE_COLS, true)
                    || preg_match('/(^date$|_at$|_on$|_time$)/i', $col) === 1;
                if ($isDate) { $val = self::toDate($val); }
            }
            // BIN_COLS ponecháváme jako raw string/resource

            $vals[$prop] = $val;
        }

        $rc   = new \ReflectionClass(OrderDto::class);
        $ctor = $rc->getConstructor();

        if ($ctor === null || $ctor->getNumberOfRequiredParameters() === 0) {
            return $rc->newInstance();
        }

        $ordered = [];
        foreach ($ctor->getParameters() as $p) {
            $name = $p->getName();
            $ordered[] = $vals[$name] ?? ($p->isDefaultValueAvailable() ? $p->getDefaultValue() : null);
        }
        return $rc->newInstanceArgs($ordered);
    }

    /**
     * Mapuje DTO zpět na asociativní řádek pro DB (insert/update).
     * - JSON -> string (UTF-8, bez escapování lomítek)
     * - DATETIME -> 'Y-m-d H:i:s.u'
     * - bool -> 0/1 (kvůli MySQL)
     */
    public static function toRow(OrderDto $dto, ?array $onlyProps = null): array {
        $out = [];
        $src = $dto->toArray();

        if ($onlyProps !== null) {
            $src = array_intersect_key($src, array_fill_keys($onlyProps, true));
        }

        foreach ($src as $prop => $val) {
            $col = self::propToCol((string)$prop);

            if (in_array($col, self::JSON_COLS, true)) {
                $val = $val === null ? null : json_encode(
                    $val,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                );
            } elseif (in_array($col, self::DATE_COLS, true)) {
                if ($val instanceof DateTimeImmutable) {
                    $val = $val->format('Y-m-d H:i:s.u');
                } elseif ($val !== null && $val !== '') {
                    $val = (new DateTimeImmutable((string)$val, new DateTimeZone(self::TZ)))->format('Y-m-d H:i:s.u');
                } else {
                    $val = null;
                }
            } elseif (in_array($col, self::BOOL_COLS, true)) {
                $val = $val === null ? null : ($val ? 1 : 0);
            } elseif (in_array($col, self::INT_COLS, true)) {
                $val = $val === null ? null : (int)$val;
            } elseif (in_array($col, self::FLOAT_COLS, true)) {
                $val = $val === null ? null : (float)$val;
            }
            $out[$col] = $val;
        }
        return $out;
    }

    /** Batch: pole řádků -> pole DTO. */
    public static function hydrateList(array $rows): array {
        $out = [];
        foreach ($rows as $r) { $out[] = self::fromRow($r); }
        return $out;
    }
}
