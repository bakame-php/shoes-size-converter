<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use function round;
use function str_replace;
use function strtoupper;

enum AdultUnit: string implements ShoeUnit
{
    private const int MONDOPOINT_STEP = 5;
    private const float CM_STEP = 0.1;
    private const float SHOE_SIZE_STEP = 0.5;
    private const int EU_OFFSET = 2;
    private const float EU_SCALE = 20 / 3;
    private const int UK_OFFSET = 23;
    private const int UK_SCALE = 3;
    private const int US_MEN_OFFSET = 22;
    private const int US_WOMEN_OFFSET = 21;

    case Mondopoint = 'mondopoint';
    case Cm = 'cm';
    case Eu = 'eu';
    case Uk = 'uk';
    case UsMen = 'us_men';
    case UsWomen = 'us_women';

    public function label(): string
    {
        return match ($this) {
            self::Mondopoint => $this->name,
            default => strtoupper(str_replace('_', ' ', $this->value)),
        };
    }

    /**
     * Returns the shoe size from the shoe size value unit.
     */
    public function of(int|float $size): AdultSize
    {
        return AdultSize::fromSize($size, $this);
    }

    /**
     * Returns the foot length corresponding to the given shoe size value.
     */
    public function toFootLength(int|float $size): Length
    {
        return match ($this) {
            self::Mondopoint => Length::fromMillimeters($size),
            self::Cm => Length::fromCentimeters($size),
            self::Eu => Length::fromMillimeters(($size - self::EU_OFFSET) * self::EU_SCALE),
            self::Uk => Length::fromInches(($size + self::UK_OFFSET) / self::UK_SCALE),
            self::UsMen => Length::fromInches(($size + self::US_MEN_OFFSET) / self::UK_SCALE),
            self::UsWomen => Length::fromInches(($size + self::US_WOMEN_OFFSET) / self::UK_SCALE),
        };
    }

    /**
     * Returns the shoe size corresponding to the given foot length.
     */
    public function toSize(Length $footLength): float
    {
        $length = match ($this) {
            self::Mondopoint => $footLength->in(LengthUnit::Millimeter),
            self::Cm => $footLength->in(LengthUnit::Centimeter),
            self::Eu => $footLength->in(LengthUnit::Millimeter) / self::EU_SCALE + self::EU_OFFSET,
            self::Uk => $footLength->in(LengthUnit::Inch) * self::UK_SCALE - self::UK_OFFSET,
            self::UsMen => $footLength->in(LengthUnit::Inch) * self::UK_SCALE - self::US_MEN_OFFSET,
            self::UsWomen => $footLength->in(LengthUnit::Inch) * self::UK_SCALE - self::US_WOMEN_OFFSET,
        };

        $step = match ($this) {
            self::Mondopoint => self::MONDOPOINT_STEP,
            self::Cm => self::CM_STEP,
            self::Eu,
            self::Uk,
            self::UsMen,
            self::UsWomen => self::SHOE_SIZE_STEP,
        };

        return round($length / $step) * $step;
    }
}
