<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use JsonSerializable;
use ValueError;

use function strtolower;

final readonly class ShoeSize implements JsonSerializable
{
    public function __construct(
        public float $value,
        public Unit $unit,
    ) {
        $value >= 0 || throw new ValueError('The shoe size value cannot be less than 1');
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
        return Unit::Cm === $this->unit
            ? $this->value.' '.strtolower($this->unit->value)
            : $this->unit->value.' '.$this->value;
    }
}
