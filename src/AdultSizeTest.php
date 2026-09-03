<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ValueError;

#[CoversClass(AdultUnit::class)]
#[CoversClass(Length::class)]
#[CoversClass(LengthUnit::class)]
#[CoversClass(LengthRange::class)]
#[CoversClass(AdultSize::class)]
final class AdultSizeTest extends TestCase
{
    public function test_shoesize_representation(): void
    {
        self::assertSame('CM 24.5', AdultSize::fromSize(24.5, AdultUnit::Cm)->label());
    }

    public function test_shoe_size_instantiation_fails_with_negative_number(): void
    {
        $this->expectException(ValueError::class);

        AdultUnit::Cm->of(-1);
    }

    #[DataProvider('providesSizesAndFootLength')]
    public function test_shoe_size_instantiation_and_foot_length_conversion(
        int|float $size,
        AdultUnit $shoeUnit,
        int|float $length,
        LengthUnit $lengthUnit,
    ): void {
        $shoes = AdultSize::fromSize($size, $shoeUnit);

        self::assertSame($length, $shoes->footLength->in($lengthUnit));
    }

    /**
     * @return iterable<non-empty-string, array{
     *     size: int|float,
     *     shoeUnit: AdultUnit,
     *     length: int|float,
     *     lengthUnit: LengthUnit,
     * }>
     */
    public static function providesSizesAndFootLength(): iterable
    {
        yield 'size in mondopoint rounded in cm' => [
            'size' => 24.5,
            'shoeUnit' => AdultUnit::Mondopoint,
            'length' => 2.5,
            'lengthUnit' => LengthUnit::Centimeter,
        ];

        yield 'size preserve value' => [
            'size' => 345,
            'shoeUnit' => AdultUnit::Mondopoint,
            'length' => 34.5,
            'lengthUnit' => LengthUnit::Centimeter,
        ];

        yield 'size in eu rounded in cm' => [
            'size' => 999,
            'shoeUnit' => AdultUnit::Eu,
            'length' => 664.7,
            'lengthUnit' => LengthUnit::Centimeter,
        ];

        yield 'size in cm rounded in inch' => [
            'size' => 21.5,
            'shoeUnit' => AdultUnit::Cm,
            'length' => 8.46456692913386,
            'lengthUnit' => LengthUnit::Inch,
        ];

        yield 'size in eu converted in inch' => [
            'size' => 999,
            'shoeUnit' => AdultUnit::Eu,
            'length' => 261.6929133858268,
            'lengthUnit' => LengthUnit::Inch,
        ];
    }

    public function test_conversion_algorithm(): void
    {
        $eu42 = AdultUnit::Eu->of(42);
        self::assertSame(8.5, $eu42->in(AdultUnit::Uk)->size);
        self::assertSame(9.5, $eu42->in(AdultUnit::UsMen)->size);
        self::assertSame(10.5, $eu42->in(AdultUnit::UsWomen)->size);

        $footLength = $eu42->footLength;

        self::assertEquals([
            'mondopoint' => AdultSize::fromFootLength($footLength, AdultUnit::Mondopoint),
            'cm' => AdultSize::fromFootLength($footLength, AdultUnit::Cm),
            'eu' => $eu42,
            'uk' => AdultSize::fromFootLength($footLength, AdultUnit::Uk),
            'us_men' => AdultSize::fromFootLength($footLength, AdultUnit::UsMen),
            'us_women' => AdultSize::fromFootLength($footLength, AdultUnit::UsWomen),
        ], $eu42->equivalents());
    }

    public function test_it_can_compare_shoes_size(): void
    {
        $euShoes = AdultUnit::Eu->of(42);
        $footLength = $euShoes->footLength;
        $ukShoes = AdultSize::fromFootLength($footLength, AdultUnit::Uk);
        $usShoes = AdultUnit::UsWomen->of(10);

        self::assertSame(0, Length::compare($euShoes, $ukShoes));
        self::assertSame(1, Length::compare($euShoes, $usShoes));
    }

    public function test_conversion_fails(): void
    {
        $shoeSize = AdultUnit::Cm->of(10);
        self::assertFalse($shoeSize->isAvailableIn(AdultUnit::UsWomen));

        $res = $shoeSize->equivalents();
        self::assertNull($res[AdultUnit::UsWomen->value]);

        $this->expectException(ValueError::class);
        $shoeSize->in(AdultUnit::UsWomen);
    }
}
