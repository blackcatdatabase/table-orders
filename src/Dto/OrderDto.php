<?php
declare(strict_types=1);

namespace BlackCat\Database\Packages\Orders\Dto;

/**
 * Jednoduché, neměnné DTO s veřejnými readonly vlastnostmi.
 * - Bez logiky; pouze nosič dat.
 * - Silné typy drží kontrakt napříč vrstvami.
 */
final class OrderDto {
    public function __construct(
        public readonly ?int $id,
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

    /** Vhodné pro serializaci/logování (bez velkých blobů). */
    public function toArray(): array {
        return get_object_vars($this);
    }
}
