<?php

declare(strict_types=1);

namespace Bakame\Shoes;

enum LengthUnit: string
{
    case Millimeter = 'mm';
    case Centimeter = 'cm';
    case Inch = 'in';

    public function label(): string
    {
        return $this->name;
    }
}
