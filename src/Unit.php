<?php

declare(strict_types=1);

namespace Bakame\Shoes;

enum Unit: string
{
    private const MILLIMETERS_PER_CENTIMETER = 10;
    private const MILLIMETERS_PER_INCH = 25.4;
    private const MONDOPOINT_STEP = 5;
    private const CENTIMETER_STEP = 0.1;
    private const SHOE_SIZE_STEP = 0.5;
    private const EU_OFFSET = 2;
    private const EU_SCALE = 20 / 3;
    private const UK_OFFSET = 23;
    private const US_MEN_OFFSET = 22;
    private const US_WOMEN_OFFSET = 21;
    private const THIRD = 1 / 3;

    case Mondopoint = 'mondopoint';
    case Cm = 'cm';
    case Eu = 'eu';
    case Uk = 'uk';
    case UsMen = 'us_men';
    case UsWomen = 'us_women';

    public function size(int|float $value): ShoeSize
    {
        return new ShoeSize($value, $this);
    }

    public function fromFoot(ShoeSize $size): ShoeSize
    {
        return new ShoeSize($this->fromFootLength($size->inMillimeters()), $this);
    }

    public function toFootLength(float $value): float
    {
        return match ($this) {
            self::Mondopoint => $value,
            self::Cm => $value * self::MILLIMETERS_PER_CENTIMETER,
            self::Eu => ($value - self::EU_OFFSET) * self::EU_SCALE,
            self::Uk => ($value + self::UK_OFFSET) * self::MILLIMETERS_PER_INCH * self::THIRD,
            self::UsMen => ($value + self::US_MEN_OFFSET) * self::MILLIMETERS_PER_INCH * self::THIRD,
            self::UsWomen => ($value + self::US_WOMEN_OFFSET) * self::MILLIMETERS_PER_INCH * self::THIRD,
        };
    }

    public function fromFootLength(float $footLength): float
    {
        $value = match ($this) {
            self::Mondopoint => $footLength,
            self::Cm => $footLength / self::MILLIMETERS_PER_CENTIMETER,
            self::Eu => $footLength / self::EU_SCALE + self::EU_OFFSET,
            self::Uk => $footLength * 3 / self::MILLIMETERS_PER_INCH - self::UK_OFFSET,
            self::UsMen => $footLength * 3 / self::MILLIMETERS_PER_INCH - self::US_MEN_OFFSET,
            self::UsWomen => $footLength * 3 / self::MILLIMETERS_PER_INCH - self::US_WOMEN_OFFSET,
        };

        $step = match ($this) {
            self::Mondopoint => self::MONDOPOINT_STEP,
            self::Cm => self::CENTIMETER_STEP,
            self::Eu,
            self::Uk,
            self::UsMen,
            self::UsWomen => self::SHOE_SIZE_STEP,
        };

        return round($value / $step) * $step;
    }
}
