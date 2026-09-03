<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use PDO;
use SplFileInfo;
use SplFileObject;

enum ShoeType: string
{
    private const array CONVERSION_TABLE_ADULTS = [
        ['mondopoint' => 215, 'cm' => 21.5, 'eu' => 34, 'uk' => 2.5, 'us_men' => 3.5, 'us_women' => 4.5],
        ['mondopoint' => 220, 'cm' => 22.0, 'eu' => 35, 'uk' => 3, 'us_men' => 4, 'us_women' => 5],
        ['mondopoint' => 225, 'cm' => 22.5, 'eu' => 35.5, 'uk' => 3.5, 'us_men' => 4.5, 'us_women' => 5.5],
        ['mondopoint' => 230, 'cm' => 23.0, 'eu' => 36.5, 'uk' => 4, 'us_men' => 5, 'us_women' => 6],
        ['mondopoint' => 235, 'cm' => 23.5, 'eu' => 37, 'uk' => 4.5, 'us_men' => 5.5, 'us_women' => 6.5],
        ['mondopoint' => 240, 'cm' => 24.0, 'eu' => 38, 'uk' => 5.5, 'us_men' => 6.5, 'us_women' => 7.5],
        ['mondopoint' => 245, 'cm' => 24.5, 'eu' => 38.5, 'uk' => 6, 'us_men' => 7, 'us_women' => 8],
        ['mondopoint' => 250, 'cm' => 25.0, 'eu' => 39.5, 'uk' => 6.5, 'us_men' => 7.5, 'us_women' => 8.5],
        ['mondopoint' => 255, 'cm' => 25.5, 'eu' => 40, 'uk' => 7, 'us_men' => 8, 'us_women' => 9],
        ['mondopoint' => 260, 'cm' => 26.0, 'eu' => 41, 'uk' => 7.5, 'us_men' => 8.5, 'us_women' => 9.5],
        ['mondopoint' => 265, 'cm' => 26.5, 'eu' => 41.5, 'uk' => 8.5, 'us_men' => 9.5, 'us_women' => 10.5],
        ['mondopoint' => 270, 'cm' => 27.0, 'eu' => 42.5, 'uk' => 9, 'us_men' => 10, 'us_women' => 11],
        ['mondopoint' => 275, 'cm' => 27.5, 'eu' => 43, 'uk' => 9.5, 'us_men' => 10.5, 'us_women' => 11.5],
        ['mondopoint' => 280, 'cm' => 28.0, 'eu' => 44, 'uk' => 10, 'us_men' => 11, 'us_women' => 12],
        ['mondopoint' => 285, 'cm' => 28.5, 'eu' => 44.5, 'uk' => 10.5, 'us_men' => 11.5, 'us_women' => 12.5],
        ['mondopoint' => 290, 'cm' => 29.0, 'eu' => 45.5, 'uk' => 11, 'us_men' => 12, 'us_women' => 13],
        ['mondopoint' => 295, 'cm' => 29.5, 'eu' => 46, 'uk' => 12, 'us_men' => 13, 'us_women' => 14],
        ['mondopoint' => 300, 'cm' => 30.0, 'eu' => 47, 'uk' => 12.5, 'us_men' => 13.5, 'us_women' => 14.5],
        ['mondopoint' => 305, 'cm' => 30.5, 'eu' => 47.5, 'uk' => 13, 'us_men' => 14, 'us_women' => 15],
        ['mondopoint' => 310, 'cm' => 31.0, 'eu' => 48.5, 'uk' => 13.5, 'us_men' => 14.5, 'us_women' => 15.5],
        ['mondopoint' => 315, 'cm' => 31.5, 'eu' => 49, 'uk' => 14, 'us_men' => 15, 'us_women' => 16],
        ['mondopoint' => 320, 'cm' => 32.0, 'eu' => 50, 'uk' => 15, 'us_men' => 16, 'us_women' => 17],
    ];

    private const array CONVERSION_TABLE_CHILDREN = [
        ['mondopoint' => 120, 'cm' => 12.0, 'eu' => 19.5, 'uk' => 3.5, 'us' => 4],
        ['mondopoint' => 125, 'cm' => 12.3, 'eu' => 20, 'uk' => 4, 'us' => 4.5],
        ['mondopoint' => null, 'cm' => 12.7, 'eu' => 20.5, 'uk' => 4.5, 'us' => 5],
        ['mondopoint' => 130, 'cm' => 13.0, 'eu' => 21, 'uk' => 5, 'us' => 5.5],
        ['mondopoint' => null, 'cm' => 13.3, 'eu' => 21.5, 'uk' => 5.5, 'us' => 6],
        ['mondopoint' => 135, 'cm' => 13.5, 'eu' => 22, 'uk' => null, 'us' => null],
        ['mondopoint' => 140, 'cm' => 13.8, 'eu' => 22.5, 'uk' => 6, 'us' => 6.5],
        ['mondopoint' => null, 'cm' => 14.2, 'eu' => 23, 'uk' => 6.5, 'us' => 7],
        ['mondopoint' => 145, 'cm' => 14.6, 'eu' => 23.5, 'uk' => 7, 'us' => 7.5],
        ['mondopoint' => null, 'cm' => 14.8, 'eu' => 24, 'uk' => null, 'us' => null],
        ['mondopoint' => 150, 'cm' => 15.0, 'eu' => 24.5, 'uk' => 7.5, 'us' => 8],
        ['mondopoint' => 155, 'cm' => 15.4, 'eu' => 25, 'uk' => 8, 'us' => 8.5],
        ['mondopoint' => null, 'cm' => 15.7, 'eu' => 25.5, 'uk' => 8.5, 'us' => 9],
        ['mondopoint' => 160, 'cm' => 16.0, 'eu' => 26, 'uk' => 9, 'us' => 9.5],
        ['mondopoint' => null, 'cm' => 16.4, 'eu' => 26.5, 'uk' => null, 'us' => null],
        ['mondopoint' => 165, 'cm' => 16.6, 'eu' => 27, 'uk' => 9.5, 'us' => 10],
        ['mondopoint' => 170, 'cm' => 16.9, 'eu' => 27.5, 'uk' => 10, 'us' => 10.5],
        ['mondopoint' => null, 'cm' => 17.3, 'eu' => 28, 'uk' => 10.5, 'us' => 11],
        ['mondopoint' => 175, 'cm' => 17.6, 'eu' => 28.5, 'uk' => 11, 'us' => 11.5],
        ['mondopoint' => 180, 'cm' => 17.9, 'eu' => 29, 'uk' => 11.5, 'us' => 12],
        ['mondopoint' => null, 'cm' => 18.2, 'eu' => 29.5, 'uk' => null, 'us' => null],
        ['mondopoint' => 185, 'cm' => 18.5, 'eu' => 30, 'uk' => 12, 'us' => 12.5],
        ['mondopoint' => null, 'cm' => 18.8, 'eu' => 30.5, 'uk' => 12.5, 'us' => 13],
        ['mondopoint' => 190, 'cm' => 19.2, 'eu' => 31, 'uk' => 13, 'us' => 13.5],
        ['mondopoint' => 195, 'cm' => 19.5, 'eu' => 31.5, 'uk' => 13.5, 'us' => 1],
        ['mondopoint' => null, 'cm' => 19.8, 'eu' => 32, 'uk' => null, 'us' => null],
        ['mondopoint' => 200, 'cm' => 20.0, 'eu' => 32.5, 'uk' => 1, 'us' => 1.5],
        ['mondopoint' => 205, 'cm' => 20.4, 'eu' => 33, 'uk' => 1.5, 'us' => 2],
        ['mondopoint' => null, 'cm' => 20.7, 'eu' => 33.5, 'uk' => null, 'us' => null],
        ['mondopoint' => 210, 'cm' => 21.0, 'eu' => 34, 'uk' => 2, 'us' => 2.5],
        ['mondopoint' => null, 'cm' => 21.3, 'eu' => 34.5, 'uk' => 2.5, 'us' => 3],
        ['mondopoint' => 215, 'cm' => 21.7, 'eu' => 35, 'uk' => 3, 'us' => 3.5],
        ['mondopoint' => 220, 'cm' => 22.0, 'eu' => 35.5, 'uk' => 3.5, 'us' => 4],
        ['mondopoint' => null, 'cm' => 22.4, 'eu' => 36, 'uk' => null, 'us' => null],
        ['mondopoint' => 225, 'cm' => 22.6, 'eu' => 36.5, 'uk' => 4, 'us' => 4.5],
        ['mondopoint' => 230, 'cm' => 23.0, 'eu' => 37, 'uk' => 4.5, 'us' => 5],
        ['mondopoint' => null, 'cm' => 23.2, 'eu' => 37.5, 'uk' => null, 'us' => null],
        ['mondopoint' => 235, 'cm' => 23.6, 'eu' => 38, 'uk' => 5, 'us' => null],
    ];

    case Adults = 'adults';
    case Children = 'children';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * @param non-empty-string|resource|SplFileObject|SplFileInfo|PDO|null $source
     *
     * @throws ShoeException
     */
    public function converter(mixed $source = null): Converter
    {
        if ($source instanceof PDO) {
            return Converter::fromPdo($source, $this);
        }

        if (null !== $source) {
            return Converter::fromCsv($source, $this);
        }

        return match ($this) {
            self::Adults => new Converter($this, self::CONVERSION_TABLE_ADULTS),
            self::Children => new Converter($this, self::CONVERSION_TABLE_CHILDREN),
        };
    }

    public function unit(string $unit): ?ShoeUnit
    {
        return match ($this) {
            self::Adults => AdultUnit::tryFrom($unit),
            self::Children => ChildUnit::tryFrom($unit),
        };
    }

    /**
     * @return list<ShoeUnit>
     */
    public function list(): array
    {
        return match ($this) {
            self::Adults => AdultUnit::cases(),
            self::Children => ChildUnit::cases(),
        };
    }

    public function supports(ShoeUnit|ShoeSize $value): bool
    {
        $unit = $value instanceof ShoeSize ? $value->unit : $value;

        return match ($this) {
            self::Adults => AdultUnit::class === $unit::class,
            self::Children => ChildUnit::class === $unit::class,
        };
    }
}
