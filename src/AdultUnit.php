<?php

declare(strict_types=1);

namespace Bakame\Shoes;

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
        return strtoupper(str_replace('_', ' ', $this->value));
    }

    /**
     * Returns the shoe size from the shoe size value unit.
     */
    public function size(int|float $value): AdultSize
    {
        return new AdultSize($value, $this);
    }

    /**
     * Returns the shoe size from a foot length.
     */
    public function fromFoot(int|float $length, LengthUnit $unit): AdultSize
    {
        return $this->size($this->fromFootLength($length, $unit));
    }

    /**
     * Convert the shoe size between 2 shoe units.
     */
    public function convert(AdultSize $size): AdultSize
    {
        return $this->fromFoot($size->footLength(LengthUnit::Millimeter), LengthUnit::Millimeter);
    }

    /**
     * Convert the unit size value into the expressed foot length.
     */
    public function toFootLength(float $size, LengthUnit $to): float
    {
        [$length, $unit] = match ($this) {
            self::Mondopoint => [$size, LengthUnit::Millimeter],
            self::Cm => [$size, LengthUnit::Centimeter],
            self::Eu => [($size - self::EU_OFFSET) * self::EU_SCALE, LengthUnit::Millimeter],
            self::Uk => [($size + self::UK_OFFSET) / self::UK_SCALE, LengthUnit::Inch],
            self::UsMen => [($size + self::US_MEN_OFFSET) / self::UK_SCALE, LengthUnit::Inch],
            self::UsWomen => [($size + self::US_WOMEN_OFFSET) / self::UK_SCALE, LengthUnit::Inch],
        };

        return $unit->convert($length, $to);
    }

    /**
     * Convert a foot length into the unit shoe size value.
     */
    private function fromFootLength(float $length, LengthUnit $unit): float
    {
        $length = match ($this) {
            self::Mondopoint => $unit->convert($length, LengthUnit::Millimeter),
            self::Cm => $unit->convert($length, LengthUnit::Centimeter),
            self::Eu => $unit->convert($length, LengthUnit::Millimeter) / self::EU_SCALE + self::EU_OFFSET,
            self::Uk => $unit->convert($length, LengthUnit::Inch) * self::UK_SCALE - self::UK_OFFSET,
            self::UsMen => $unit->convert($length, LengthUnit::Inch) * self::UK_SCALE - self::US_MEN_OFFSET,
            self::UsWomen => $unit->convert($length, LengthUnit::Inch) * self::UK_SCALE - self::US_WOMEN_OFFSET,
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
