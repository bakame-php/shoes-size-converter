<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ValueError;

#[CoversClass(AdultUnit::class)]
#[CoversClass(LengthUnit::class)]
#[CoversClass(AdultSize::class)]
final class AdultSizeTest extends TestCase
{
    public function test_shoesize_representation(): void
    {
        $size = AdultUnit::Cm->size(24.5);
        self::assertSame('CM 24.5', $size->human());
    }

    public function test_shoe_size_instantiation_fails_with_negative_number(): void
    {
        $this->expectException(ValueError::class);

        AdultUnit::Cm->size(-1);
    }

    public function testInCmReturnsValueWhenAlreadyInCm(): void
    {
        $size = new AdultSize(24.5, AdultUnit::Mondopoint);

        self::assertSame(2.45, $size->footLength(LengthUnit::Centimeter));
    }

    public function testInCmConvertsToCm(): void
    {
        self::assertSame(34.5, AdultUnit::Mondopoint->size(345)->footLength(LengthUnit::Centimeter));
    }

    public function testInCmReturnsNullWhenNoConversionExists(): void
    {
        self::assertEquals(664.6666666666667, AdultUnit::Eu->size(999)->footLength(LengthUnit::Centimeter));
    }

    public function testInInchConvertsToInches(): void
    {
        self::assertSame(8.46456692913386, AdultUnit::Cm->size(21.5)->footLength(LengthUnit::Inch));
    }

    public function testInInchReturnsNullWhenNoConversionExists(): void
    {
        self::assertEquals(261.67979002624674, AdultUnit::Eu->size(999)->footLength(LengthUnit::Inch));
    }

    public function test_conversion_algorithm(): void
    {
        self::assertSame(8.5, AdultUnit::Eu->size(42)->in(AdultUnit::Uk)->value);
        self::assertSame(9.5, AdultUnit::Eu->size(42)->in(AdultUnit::UsMen)->value);
        self::assertSame(10.5, AdultUnit::Eu->size(42)->in(AdultUnit::UsWomen)->value);
        self::assertEquals([
            'mondopoint' => AdultUnit::Mondopoint->size(265),
            'cm'         => AdultUnit::Cm->size(26.700000000000003),
            'eu'         => AdultUnit::Eu->size(42),
            'uk'         => AdultUnit::Uk->size(8.5),
            'us_men'     => AdultUnit::UsMen->size(9.5),
            'us_women'   => AdultUnit::UsWomen->size(10.5),
        ], AdultUnit::Eu->size(42)->equivalents());
    }

    public function test_conversion_fails(): void
    {
        $shoeSize = AdultUnit::Cm->size(10);
        self::assertFalse($shoeSize->isAvailableIn(AdultUnit::UsWomen));

        $res = $shoeSize->equivalents();
        self::assertNull($res[AdultUnit::UsWomen->value]);

        $this->expectException(ValueError::class);
        $shoeSize->in(AdultUnit::UsWomen);
    }
}
