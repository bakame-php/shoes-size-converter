<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use JsonSerializable;
use ValueError;

final readonly class ShoeSize implements JsonSerializable
{
    private const MILLIMETER_PER_CENTIMETER = 10;
    private const MILLIMETERS_PER_INCH = 25.4;

    public function __construct(
        public float $value,
        public Unit $unit,
    ) {
        $value >= 0 || throw new ValueError('The shoe size value must be greater than or equal to 1');
    }

    /**
     * @return array{value: int|float, unit: Unit}
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'unit' => $this->unit,
        ];
    }

    /**
     * @return non-empty-string
     */
    public function human(): string
    {
        return strtoupper($this->unit->value).' '.$this->value;
    }

    /**
     * Returns te foot length in millimeters.
     */
    public function inMillimeters(): float
    {
        return $this->unit->toFootLength($this->value);
    }

    public function inInches(): float
    {
        return $this->inMillimeters() / self::MILLIMETERS_PER_INCH;
    }

    public function inCentimeters(): float
    {
        return $this->inMillimeters() / self::MILLIMETER_PER_CENTIMETER;
    }

    public function canConvertTo(Unit $unit): bool
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
        return $unit->fromFoot($this);
    }

    /**
     * Returns the calculated equivalents for the given shoe size.
     *
     * An equivalent is `null` when the calculated value cannot be represented
     * by a {@see ShoeSize}.
     *
     * @return array<non-empty-string, ShoeSize|null>
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
