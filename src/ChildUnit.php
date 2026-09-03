<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use function strtoupper;

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
            self::Mondopoint => $this->name,
            default => strtoupper($this->name),
        };
    }

    public function of(float|int $size): ChildSize
    {
        return ChildSize::fromSize($size, $this);
    }
}
