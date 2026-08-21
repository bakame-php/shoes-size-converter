<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ValueError;

use function json_encode;

#[CoversClass(Unit::class)]
#[CoversClass(ShoeSize::class)]
final class ShoeSizeTest extends TestCase
{
    public function test_shoesize_representation(): void
    {
        $size = Unit::Cm->size(24.5);
        self::assertSame('CM 24.5', $size->human());
        self::assertSame('{"value":24.5,"unit":"cm"}', json_encode($size));
    }

    public function test_shoe_size_instantiation_fails_with_negative_number(): void
    {
        $this->expectException(ValueError::class);

        Unit::Cm->size(-1);
    }

    public function testInCmReturnsValueWhenAlreadyInCm(): void
    {
        $size = new ShoeSize(24.5, Unit::Mondopoint);

        self::assertSame(2.45, $size->inCentimeters());
    }

    public function testInCmConvertsToCm(): void
    {
        self::assertSame(34.5, Unit::Mondopoint->size(345)->inCentimeters());
    }

    public function testInCmReturnsNullWhenNoConversionExists(): void
    {
        self::assertEquals(664.6666666666667, Unit::Eu->size(999)->inCentimeters());
    }

    public function testInInchConvertsToInches(): void
    {
        self::assertSame(8.46456692913386, Unit::Cm->size(21.5)->inInches());
    }

    public function testInInchReturnsNullWhenNoConversionExists(): void
    {
        self::assertEquals(261.67979002624674, Unit::Eu->size(999)->inInches());
    }

    public function test_conversion_algorithm(): void
    {
        self::assertSame(8.5, Unit::Eu->size(42)->in(Unit::Uk)->value);
        self::assertSame(9.5, Unit::Eu->size(42)->in(Unit::UsMen)->value);
        self::assertSame(10.5, Unit::Eu->size(42)->in(Unit::UsWomen)->value);
        self::assertEquals([
            'mondopoint' => Unit::Mondopoint->size(265),
            'cm'         => Unit::Cm->size(26.700000000000003),
            'eu'         => Unit::Eu->size(42),
            'uk'         => Unit::Uk->size(8.5),
            'us_men'     => Unit::UsMen->size(9.5),
            'us_women'   => Unit::UsWomen->size(10.5),
        ], Unit::Eu->size(42)->equivalents());
    }

    public function test_conversion_fails(): void
    {
        $shoeSize = Unit::Cm->size(10);
        self::assertFalse($shoeSize->canConvertTo(Unit::UsWomen));

        $res = $shoeSize->equivalents();
        self::assertNull($res[Unit::UsWomen->value]);

        $this->expectException(ValueError::class);
        $shoeSize->in(Unit::UsWomen);
    }
}
