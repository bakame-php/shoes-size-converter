<?php

declare(strict_types=1);

namespace Bakame\Shoes;

enum Unit: string
{
    case Cm = 'CM';
    case Eu = 'EU';
    case Us = 'US';
    case Uk = 'UK';

    public function size(int|float $value): ShoeSize
    {
        return new ShoeSize($value, $this);
    }
}
