<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use ValueError;

final readonly class ChildSize implements ShoeSize
{
    private function __construct(
        public float $size,
        public ChildUnit $unit,
    ) {
        $size >= 0 || throw new ValueError('The shoe size value must be greater than or equal to 0');
    }

    public static function fromSize(int|float $size, ChildUnit $unit): self
    {
        return new self($size, $unit);
    }

    /**
     * @return non-empty-string
     */
    public function label(): string
    {
        return $this->unit->label().' '.$this->size;
    }
}
