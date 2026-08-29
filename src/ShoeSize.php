<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use BackedEnum;

interface ShoeSize
{
    public int|float $value {
        get;
    }

    public ShoeUnit&BackedEnum $unit {
        get;
    }

    /**
     * @return non-empty-string
     */
    public function human(): string;
}
