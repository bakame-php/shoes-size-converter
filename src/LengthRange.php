<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use ValueError;

final readonly class LengthRange
{
    public function __construct(
        public float $min,
        public float $max,
        public LengthUnit $unit,
    ) {
        $min <= $max || throw new ValueError('The minimum length must be less than or equal to the maximum length.');
    }

    public function in(LengthUnit $unit): self
    {
        return new self(
            $this->unit->convert($this->min, $unit),
            $this->unit->convert($this->max, $unit),
            $unit
        );
    }
}
