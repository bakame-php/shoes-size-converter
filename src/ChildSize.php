<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use ValueError;

final readonly class ChildSize implements ShoeSize
{
    public function __construct(
        public float $value,
        public ChildUnit $unit,
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
}
