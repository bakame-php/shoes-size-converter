<?php

declare(strict_types=1);

namespace Bakame\Shoes;

enum ChildUnit: string implements ShoeUnit
{
    case Mondopoint = 'mondopoint';
    case Cm = 'cm';
    case Eu = 'eu';
    case Uk = 'uk';
    case Us = 'us';

    public function label(): string
    {
        return match ($this) {
            self::Mondopoint => 'Mondopoint',
            self::Cm => 'CM',
            self::Eu => 'EU',
            self::Uk => 'UK',
            self::Us => 'US',
        };
    }

    public function size(float|int $value): ChildSize
    {
        return new ChildSize($value, $this);
    }
}
