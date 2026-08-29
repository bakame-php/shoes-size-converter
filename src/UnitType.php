<?php

declare(strict_types=1);

namespace Bakame\Shoes;

enum UnitType: string
{
    case Adult = 'adult';
    case Child = 'child';

    public function label(): string
    {
        return match ($this) {
            self::Adult => 'Adults',
            self::Child => 'Children',
        };
    }

    /**
     * @param list<array<non-empty-string, int|float|null>> $tabularData
     *
     * @throws ShoeException
     */
    public function converter(array $tabularData): Converter
    {
        return match ($this) {
            self::Adult => new Converter(Unit::class, $tabularData),
            self::Child => new Converter(ChildUnit::class, $tabularData),
        };
    }

    public function unit(string $unit): ?ShoeUnit
    {
        return match ($this) {
            self::Adult => Unit::tryFrom($unit),
            self::Child => ChildUnit::tryFrom($unit),
        };
    }

    /**
     * @return list<ShoeUnit>
     */
    public function list(): array
    {
        return match ($this) {
            self::Adult => Unit::cases(),
            self::Child => ChildUnit::cases(),
        };
    }
}
