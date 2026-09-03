<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use ArrayAccess;
use Override;
use ValueError;

/**
 * @implements ArrayAccess<non-negative-int, Length>
 */
final readonly class LengthRange implements ArrayAccess
{
    public function __construct(public Length $min, public Length $max)
    {
        0 >= Length::compare($min, $max) || throw new ValueError('The minimum length must be less than or equal to the maximum length.');
    }

    public function offsetExists(mixed $offset): bool
    {
        return 1 === $offset || 0 === $offset;
    }

    public function offsetGet(mixed $offset): Length
    {
        return match ($offset) {
            0 => $this->min,
            1 => $this->max,
            default => throw new ValueError('Only offsets 0 and 1 are supported.'),
        };
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw ShoeError::dueToImmutability(self::class);
    }

    #[Override]
    public function offsetUnset(mixed $offset): never
    {
        throw ShoeError::dueToImmutability(self::class);
    }
}
