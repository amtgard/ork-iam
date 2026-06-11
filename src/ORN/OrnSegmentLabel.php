<?php

namespace Amtgard\IAM\Orn;

use Amtgard\IAM\Catalog\ServiceCatalog;

/**
 * Label for one middle segment in an ORN segment schema.
 */
readonly class OrnSegmentLabel
{
    public function __construct(
        public string $name,
        private ?ServiceCatalog $catalogEntry = null,
    ) {
    }

    public static function from(string|ServiceCatalog $label): static
    {
        if ($label instanceof ServiceCatalog) {
            return new static($label->value, $label);
        }

        if ($label === '') {
            throw new \InvalidArgumentException('ORN segment label cannot be empty.');
        }

        $builtin = ServiceCatalog::tryFrom($label);
        if ($builtin !== null) {
            return new static($builtin->value, $builtin);
        }

        return new static($label, null);
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

    /**
     * @param (self|ServiceCatalog|string)[] $schema
     */
    public function offsetIn(array $schema): int
    {
        foreach ($schema as $index => $entry) {
            $schemaLabel = $entry instanceof self ? $entry : self::from($entry);
            if ($this->equals($schemaLabel)) {
                return $index;
            }
        }

        throw new \InvalidArgumentException("Label {$this->name} not found in segment schema.");
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
