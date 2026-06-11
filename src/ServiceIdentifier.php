<?php

namespace Amtgard\IAM;

/**
 * ORN prefix — the leading segment identifying who owns the ORN.
 *
 * Renamed to {@see \Amtgard\IAM\Orn\OrnPrefix} in 2.0.0.
 *
 * @see docs/ORN-ONTOLOGY.md
 */
final readonly class ServiceIdentifier
{
    public function __construct(
        public string $name,
        private ?OrkServices $orkService = null,
    ) {
    }

    public static function from(string $ornPrefix): self
    {
        if ($ornPrefix === '') {
            throw new \InvalidArgumentException('Service identifier cannot be empty.');
        }

        $builtin = OrkServices::tryFrom($ornPrefix);
        if ($builtin !== null) {
            return new self($builtin->value, $builtin);
        }

        if (!preg_match('/^[A-Z][A-Za-z0-9]*$/', $ornPrefix)) {
            throw new \InvalidArgumentException(
                "Invalid custom service identifier '$ornPrefix'. Must match /^[A-Z][A-Za-z0-9]*$/."
            );
        }

        return new self($ornPrefix, null);
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

    public function __toString(): string
    {
        return $this->name;
    }
}
