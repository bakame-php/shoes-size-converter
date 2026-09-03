<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use ValueError;

final readonly class AdultSize implements ShoeSize, HasFootLength
{
    private function __construct(
        public Length $footLength,
        public AdultUnit $unit,
        public float $size,
    ) {
        $size >= 0 || throw new ValueError('The shoe size value must be greater than or equal to 0');
    }

    public static function fromFootLength(Length $footLength, AdultUnit $unit): self
    {
        return new self($footLength, $unit, $unit->toSize($footLength));
    }

    public static function fromSize(int|float $size, AdultUnit $unit): self
    {
        return new self($unit->toFootLength($size), $unit, $size);
    }

    /**
     * @return non-empty-string
     */
    public function label(): string
    {
        return $this->unit->label().' '.$this->size;
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
        foreach (AdultUnit::cases() as $unit) {
            try {
                $value = $this->in($unit);
            } catch (ValueError) {
                $value = null;
            }

            $result[$unit->value] = $value;
        }

        return $result;
    }

    public function isAvailableIn(AdultUnit $unit): bool
    {
        try {
            $this->in($unit);

            return true;
        } catch (ValueError) {
            return false;
        }
    }

    /**
     * Convert the shoe size between 2 shoe units.
     */
    public function in(AdultUnit $unit): self
    {
        return $unit === $this->unit
            ? $this
            : self::fromFootLength($this->footLength, $unit);
    }
}
