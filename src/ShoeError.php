<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use Error;
use Throwable;

class ShoeError extends Error
{
    final public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function dueToImmutability(string $className): static
    {
        return new static($className.' is immutable.');
    }
}
