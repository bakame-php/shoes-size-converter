<?php

declare(strict_types=1);

namespace Bakame\Shoes;

interface ShoeUnit
{
    public string $value {
        get;
    }

    public function label(): string;

    public function size(int|float $value): ShoeSize;
}
