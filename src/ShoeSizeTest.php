<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ValueError;

use function json_encode;

#[CoversClass(ShoeSize::class)]
final class ShoeSizeTest extends TestCase
{
    public function test_shoesize_representation(): void
    {
        $size = Unit::Cm->size(24.5);
        self::assertSame('24.5 cm', $size->human());
        self::assertSame('{"value":24.5,"unit":"CM"}', json_encode($size));
    }

    public function test_shoesize_instantiation(): void
    {
        $this->expectException(ValueError::class);

        Unit::Cm->size(-1);
    }
}
