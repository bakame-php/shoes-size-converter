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
mondopoint,cm,eu,uk,us_men,us_women
215,21.5,34,2.5,3.5,4.5
220,22.0,35,3,4,5
225,22.5,35.5,3.5,4.5,5.5
230,23.0,36.5,4,5,6
CSV;
        $data = tmpfile();
        fwrite($data, $csv);

        return Converter::fromCsv($data);
    }

    public function testFromPath(): void
    {
        self::assertEquals(
            [
                'eu' => Unit::Eu->size(34),
                'uk' => Unit::Uk->size(2.5),
                'us_men' => Unit::UsMen->size(3.5),
                'us_women' => Unit::UsWomen->size(4.5),
                'cm' => Unit::Cm->size(21.5),
                'mondopoint' => Unit::Mondopoint->size(215),
            ],
            $this->converter()->equivalents(Unit::Eu->size(34))
        );
    }

    public function testAvailableSizesReturnsSizesForUnit(): void
    {
        $converter = $this->converter();

        self::assertEquals(
            [
                Unit::Eu->size(34),
                Unit::Eu->size(35),
                Unit::Eu->size(35.5),
                Unit::Eu->size(36.5),
            ],
            iterator_to_array($converter->availableSizes(Unit::Eu))
        );
    }

    public function testListReturnsAllAvailableUnits(): void
    {
        $converter = $this->converter();

        self::assertEquals(
            [
                'eu' => Unit::Eu->size(34),
                'us_men' => Unit::UsMen->size(3.5),
                'us_women' => Unit::UsWomen->size(4.5),
                'uk' => Unit::Uk->size(2.5),
                'cm' => Unit::Cm->size(21.5),
                'mondopoint' => Unit::Mondopoint->size(215),
            ],
            $converter->equivalents(Unit::Mondopoint->size(215))
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
            Unit::Cm->size(21.5),
            $converter->inUnit(Unit::Mondopoint->size(215), Unit::Cm)
        );
    }

    public function testConvertReturnsSameInstanceWhenAlreadyInRequestedUnit(): void
    {
        $converter = $this->converter();

        $size = Unit::Cm->size(24.5);

        self::assertEquals($size, $converter->inUnit($size, Unit::Cm));
        self::assertSame('CM 24.5', $size->human());
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
          eu REAL NOT NULL,
          us_men REAL NOT NULL,
          us_women REAL NOT NULL,
          uk REAL NOT NULL,
          cm REAL NOT NULL,
          mondopoint REAL NOT NULL
      );
    SQL);

        $pdo->exec(<<<'SQL'
        INSERT INTO shoe_sizes (eu, us_men, us_women, uk, cm, mondopoint)
        VALUES
            (39, 6.5, 6, 24.6, 32, 35),
            (40, 7.5, 7, 25.3, 38, 39)
    SQL);

        $shoes = Converter::fromPdo($pdo);

        self::assertNotEmpty($shoes->equivalents(Unit::Eu->size(39)));
    }

    public function test_loading_from_pdo_with_limit(): void
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec(<<<'SQL'
      CREATE TABLE shoe_sizes (
          eu REAL NOT NULL,
          us_men REAL NOT NULL,
          us_women REAL NOT NULL,
          uk REAL NOT NULL,
          cm REAL NOT NULL,
          mondopoint REAL NOT NULL
      );
    SQL);

        $pdo->exec(<<<'SQL'
        INSERT INTO shoe_sizes (eu, us_men, us_women, uk, cm, mondopoint)
        VALUES
            (39, 6.5, 6, 24.6, 32, 35),
            (40, 7.5, 7, 25.3, 38, 39)
    SQL);

        $shoes = Converter::fromPdo($pdo, limit: 1);

        self::assertEmpty($shoes->equivalents(Unit::Eu->size(40)));
    }
}
