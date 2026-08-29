<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use ValueError;

final readonly class AdultSize implements ShoeSize
{
    public function __construct(
        public float $value,
        public Unit $unit,
    ) {
        $value >= 0 || throw new ValueError('The shoe size value must be greater than or equal to 0');
    }

    /**
     * @return non-empty-string
     */
    public function human(): string
    {
        return $this->unit->label().' '.$this->value;
    }

    /**
     * Returns te foot length.
     */
    public function length(LengthUnit $unit): float
    {
        return $this->unit->toFootLength($this->value, $unit);
    }

    public function isAvailableIn(Unit $unit): bool
    {
        try {
            $this->in($unit);

            return true;
        } catch (ValueError) {
            return false;
        }
    }

    public function in(Unit $unit): self
    {
        return $unit->convert($this);
    }

    /**
     * Returns the calculated equivalents for the given shoe size.
     *
     * An equivalent is `null` when the calculated value cannot be represented
     * by a {@see AdultSize}.
     *
     * @return array<non-empty-string, ?AdultSize>
     */
    public function equivalents(): array
    {
        $result = [];
        foreach (Unit::cases() as $unit) {
            try {
                $value = $this->in($unit);
            } catch (ValueError) {
                $value = null;
            }

            $result[$unit->value] = $value;
        }

        return $result;
    }
}
