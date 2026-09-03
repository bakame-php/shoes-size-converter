<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use ValueError;

use function is_int;
use function round;

use const PHP_ROUND_HALF_UP;

final readonly class Length
{
    private const float MILLIMETERS_PER_INCH = 25.4;
    private const int MILLIMETERS_PER_CENTIMETER = 10;

    public static function fromMillimeters(int|float $millimeters): self
    {
        return new self(match (true) {
            is_int($millimeters) => $millimeters,
            default => (int) round($millimeters, mode: PHP_ROUND_HALF_UP),
        });
    }

    public static function fromCentimeters(int|float $centimeters): self
    {
        return self::fromMillimeters($centimeters * self::MILLIMETERS_PER_CENTIMETER);
    }

    public static function fromInches(int|float $inches): self
    {
        return self::fromMillimeters($inches * self::MILLIMETERS_PER_INCH);
    }

    public static function compare(HasFootLength|self $a, HasFootLength|self $b): int
    {
        $a = $a instanceof HasFootLength ? $a->footLength : $a;
        $b = $b instanceof HasFootLength ? $b->footLength : $b;

        return $a->millimeters <=> $b->millimeters;
    }

    private function __construct(public int $millimeters)
    {
        $millimeters >= 0 || throw new ValueError('The length value must be greater than or equal to 0');
    }

    public function in(LengthUnit $unit): int|float
    {
        return match ($unit) {
            LengthUnit::Millimeter => $this->millimeters,
            LengthUnit::Centimeter => $this->millimeters / self::MILLIMETERS_PER_CENTIMETER,
            LengthUnit::Inch => $this->millimeters / self::MILLIMETERS_PER_INCH,
        };
    }
}
