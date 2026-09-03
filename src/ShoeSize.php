<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use BackedEnum;

interface ShoeSize
{
    public int|float $size {
        get;
    }

    public ShoeUnit&BackedEnum $unit {
        get;
    }

    /**
     * @return non-empty-string
     */
    public function label(): string;
}
