<?php

namespace Amtgard\IAM\ORN;

use Amtgard\IAM\OrkServices;

/**
 * Label for one middle segment in an ORN segment schema.
 *
 * @see OrkResourceName::ornSegmentSchema()
 */
readonly class OrnSegmentLabel
{
    public function __construct(
        public string $name,
        private ?OrkServices $orkService = null,
    ) {
    }

    public static function from(string|OrkServices $label): static
    {
        if ($label instanceof OrkServices) {
            return new static($label->value, $label);
        }

        if ($label === '') {
            throw new \InvalidArgumentException('ORN segment label cannot be empty.');
        }

        $builtin = OrkServices::tryFrom($label);
        if ($builtin !== null) {
            return new static($builtin->value, $builtin);
        }

        return new static($label, null);
    }

    public function isBuiltin(): bool
    {
        return $this->orkService !== null;
    }

    public function toOrkServices(): ?OrkServices
    {
        return $this->orkService;
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name;
    }

    /**
     * Zero-based position of this label in the given segment schema.
     *
     * @param (self|OrkServices|string)[] $schema
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
