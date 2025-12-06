<?php
declare(strict_types=1);

namespace BlackCat\Database\Packages\Orders\Dto;

/**
 * Simple immutable DTO with public readonly properties.
 * - No logic; just a data carrier.
 * - Strong types enforce the contract across layers.
 */
final class OrderDto implements \JsonSerializable {
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $uuid,
        public readonly ?string $uuidBin,
        public readonly ?string $publicOrderNo,
        public readonly ?int $userId,
        public readonly string $status,
        public readonly ?string $encryptedCustomerBlob,
        public readonly ?string $encryptedCustomerBlobKeyVersion,
        public readonly array|null $encryptionMeta,
        public readonly string $currency,
        public readonly array|null $metadata,
        public readonly string $subtotal,
        public readonly string $discountTotal,
        public readonly string $taxTotal,
        public readonly string $total,
        public readonly ?string $paymentMethod,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
        public readonly int $version
    ) {}

    /** Suitable for serialization/logging (without large blobs). */
    public function toArray(): array {
        return get_object_vars($this);
    }

    /** toArray() without null values - for clean logging/diffs. */
    public function toArrayNonNull(): array {
        return array_filter(get_object_vars($this), static fn($v) => $v !== null);
    }

    public function jsonSerialize(): array {
       $a = $this->toArray();
       foreach ($a as $k => $v) {
           if ($v instanceof \DateTimeInterface) {
               // ISO-8601 with a timezone; switch to 'Y-m-d H:i:s.u' if needed
               $a[$k] = $v->format(\DateTimeInterface::ATOM);
           }
       }
       return $a;
   }
}
