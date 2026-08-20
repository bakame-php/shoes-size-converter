<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function fwrite;
use function tmpfile;

#[CoversClass(Converter::class)]
#[CoversClass(Unit::class)]
#[CoversClass(ShoeSize::class)]
final class ConverterTest extends TestCase
{
    protected function converter(): Converter
    {
        $csv = <<<CSV
EU,UK,US,CM
39,6,7,24.5
40,7,8,25
41,8,9,26
CSV;
        $data = tmpfile();
        fwrite($data, $csv);

        return Converter::fromCsv($data);
    }

    public function testInCmReturnsValueWhenAlreadyInCm(): void
    {
        $converter = $this->converter();

        $size = new ShoeSize(24.5, Unit::Cm);

        self::assertSame(24.5, $converter->inCm($size));
    }

    public function testInCmConvertsToCm(): void
    {
        $converter = $this->converter();

        self::assertSame(
            24.5,
            $converter->inCm(Unit::Eu->size(39))
        );
    }

    public function testInCmReturnsNullWhenNoConversionExists(): void
    {
        $converter = $this->converter();

        self::assertNull(
            $converter->inCm(Unit::Eu->size(999))
        );
    }

    public function testInInchConvertsToInches(): void
    {
        $converter = $this->converter();

        self::assertSame(
            10.0,
            $converter->inInch(Unit::Cm->size(25.4))
        );
    }

    public function testInInchReturnsNullWhenNoConversionExists(): void
    {
        $converter = $this->converter();

        self::assertNull(
            $converter->inInch(Unit::Eu->size(999))
        );
    }

    public function testFromPath(): void
    {
        self::assertEquals(
            [
                'EU' => Unit::Eu->size(39),
                'UK' => Unit::Uk->size(6),
                'US' => Unit::Us->size(7),
                'CM' => Unit::Cm->size(24.5),
            ],
            $this->converter()->equivalents(Unit::Eu->size(39))
        );
    }

    public function testAvailableSizesReturnsSizesForUnit(): void
    {
        $converter = $this->converter();

        self::assertEquals(
            [
                Unit::Eu->size(39),
                Unit::Eu->size(40),
                Unit::Eu->size(41),
            ],
            iterator_to_array($converter->availableSizes(Unit::Eu))
        );
    }

    public function testListReturnsAllAvailableUnits(): void
    {
        $converter = $this->converter();

        self::assertEquals(
            [
                'EU' => Unit::Eu->size(39),
                'US' => Unit::Us->size(7),
                'UK' => Unit::Uk->size(6),
                'CM' => Unit::Cm->size(24.5),
            ],
            $converter->equivalents(Unit::Eu->size(39))
        );
    }

    public function testListReturnsEmptyArrayWhenSizeDoesNotExist(): void
    {
        $converter = $this->converter();

        self::assertSame(
            [],
            $converter->equivalents(Unit::Eu->size(999))
        );
    }

    public function testConvertReturnsRequestedUnit(): void
    {
        $converter = $this->converter();

        self::assertEquals(
            Unit::Cm->size(24.5),
            $converter->inUnit(Unit::Eu->size(39), Unit::Cm)
        );
    }

    public function testConvertReturnsSameInstanceWhenAlreadyInRequestedUnit(): void
    {
        $converter = $this->converter();

        $size = Unit::Cm->size(24.5);

        self::assertEquals($size, $converter->inUnit($size, Unit::Cm));
        self::assertSame('24.5 cm', $size->human());
    }

    public function testConvertReturnsNullWhenRequestedUnitIsUnavailable(): void
    {
        $converter = $this->converter();

        self::assertNull(
            $converter->inUnit(Unit::Eu->size(999), Unit::Cm)
        );
    }

    public function test_loading_from_pdo(): void
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec(<<<'SQL'
        CREATE TABLE shoe_sizes (
            EU INTEGER NOT NULL,
            US REAL NOT NULL,
            UK REAL NOT NULL,
            CM REAL NOT NULL
        )
    SQL);

        $pdo->exec(<<<'SQL'
        INSERT INTO shoe_sizes (EU, US, UK, CM)
        VALUES
            (39, 6.5, 6, 24.6),
            (40, 7.5, 7, 25.3)
    SQL);

        $shoes = Converter::fromPdo($pdo);

        self::assertNotEmpty($shoes->equivalents(Unit::Eu->size(39)));
    }

    public function test_loading_from_pdo_with_limit(): void
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec(<<<'SQL'
        CREATE TABLE shoe_sizes (
            EU INTEGER NOT NULL,
            US REAL NOT NULL,
            UK REAL NOT NULL,
            CM REAL NOT NULL
        )
    SQL);

        $pdo->exec(<<<'SQL'
        INSERT INTO shoe_sizes (EU, US, UK, CM)
        VALUES
            (39, 6.5, 6, 24.6),
            (40, 7.5, 7, 25.3)
    SQL);

        $shoes = Converter::fromPdo($pdo, limit: 1);

        self::assertEmpty($shoes->equivalents(Unit::Eu->size(40)));
    }
}
