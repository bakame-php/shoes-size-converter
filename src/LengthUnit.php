<?php

declare(strict_types=1);

namespace Bakame\Shoes;

enum LengthUnit: string
{
    private const float MILLIMETERS_PER_INCH = 25.4;
    private const int CENTIMETERS_PER_MILLIMETERS = 10;
    private const float CHILD_TOE_ALLOWANCE = 1.08;
    private const int CHILD_LAST_LENGTH_RANGE = 6;

    case Millimeter = 'mm';
    case Centimeter = 'cm';
    case Inch = 'in';

    public function label(): string
    {
        return match ($this) {
            self::Millimeter => 'Millimeter',
            self::Centimeter => 'Centimeter',
            self::Inch => 'Inch',
        };
    }

    public function convert(float $value, self $to): float
    {
        return $to === $this ? $value : $to->fromMillimeters($this->toMillimeters($value));
    }

    public function lastLengthRange(float $length, self $unit): LengthRange
    {
        $min = $unit->toMillimeters($length) * self::CHILD_TOE_ALLOWANCE;

        return new LengthRange(
            LengthUnit::Millimeter->convert($min, $this),
            LengthUnit::Millimeter->convert($min + self::CHILD_LAST_LENGTH_RANGE, $this),
            $this,
        );
    }

    private function toMillimeters(float $value): float
    {
        return match ($this) {
            self::Millimeter => $value,
            self::Centimeter => $value * self::CENTIMETERS_PER_MILLIMETERS,
            self::Inch => $value * self::MILLIMETERS_PER_INCH,
        };
    }

    private function fromMillimeters(float $millimeters): float
    {
        return match ($this) {
            self::Millimeter => $millimeters,
            self::Centimeter => $millimeters / self::CENTIMETERS_PER_MILLIMETERS,
            self::Inch => $millimeters / self::MILLIMETERS_PER_INCH,
        };
    }
}
