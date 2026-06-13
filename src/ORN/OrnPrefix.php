<?php

namespace Amtgard\IAM\ORN;

use Amtgard\IAM\Catalog\ServiceCatalog;

/**
 * ORN prefix — the leading segment identifying who owns the ORN.
 */
final readonly class OrnPrefix
{
    public function __construct(
        public string $name,
        private ?ServiceCatalog $catalogEntry = null,
    ) {
    }

    public static function from(string $prefix): self
    {
        if ($prefix === '') {
            throw new \InvalidArgumentException('ORN prefix cannot be empty.');
        }

        $builtin = ServiceCatalog::tryFrom($prefix);
        if ($builtin !== null) {
            return new self($builtin->value, $builtin);
        }

        if (!preg_match('/^[A-Z][A-Za-z0-9]*$/', $prefix)) {
            throw new \InvalidArgumentException(
                "Invalid custom ORN prefix '$prefix'. Must match /^[A-Z][A-Za-z0-9]*$/."
            );
        }

        return new self($prefix, null);
    }

    public function isBuiltin(): bool
    {
        return $this->catalogEntry !== null;
    }

    public function toCatalogEntry(): ?ServiceCatalog
    {
        return $this->catalogEntry;
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
