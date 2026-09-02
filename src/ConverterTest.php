<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function fwrite;
use function tmpfile;

#[CoversClass(Converter::class)]
#[CoversClass(AdultUnit::class)]
#[CoversClass(LengthUnit::class)]
#[CoversClass(AdultSize::class)]
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

        return Converter::fromCsv($data, ShoeType::Adults);
    }

    public function testFromPath(): void
    {
        self::assertEquals(
            [
                'eu' => AdultUnit::Eu->size(34),
                'uk' => AdultUnit::Uk->size(2.5),
                'us_men' => AdultUnit::UsMen->size(3.5),
                'us_women' => AdultUnit::UsWomen->size(4.5),
                'cm' => AdultUnit::Cm->size(21.5),
                'mondopoint' => AdultUnit::Mondopoint->size(215),
            ],
            $this->converter()->equivalents(AdultUnit::Eu->size(34))
        );
    }

    public function testAvailableSizesReturnsSizesForUnit(): void
    {
        self::assertEquals(
            [
                AdultUnit::Eu->size(34),
                AdultUnit::Eu->size(35),
                AdultUnit::Eu->size(35.5),
                AdultUnit::Eu->size(36.5),
            ],
            iterator_to_array($this->converter()->availableSizes(AdultUnit::Eu))
        );
    }

    public function testListReturnsAllAvailableUnits(): void
    {
        self::assertEquals(
            [
                'eu' => AdultUnit::Eu->size(34),
                'us_men' => AdultUnit::UsMen->size(3.5),
                'us_women' => AdultUnit::UsWomen->size(4.5),
                'uk' => AdultUnit::Uk->size(2.5),
                'cm' => AdultUnit::Cm->size(21.5),
                'mondopoint' => AdultUnit::Mondopoint->size(215),
            ],
            $this->converter()->equivalents(AdultUnit::Mondopoint->size(215))
        );
    }

    public function testListReturnsEmptyArrayWhenSizeDoesNotExist(): void
    {
        self::assertNull($this->converter()->equivalents(AdultUnit::Eu->size(999))['eu']);
    }

    public function testConvertReturnsRequestedUnit(): void
    {
        self::assertEquals(
            AdultUnit::Cm->size(21.5),
            $this->converter()->size(AdultUnit::Mondopoint->size(215), AdultUnit::Cm)
        );
    }

    public function testConvertReturnsSameInstanceWhenAlreadyInRequestedUnit(): void
    {
        $size = AdultUnit::Cm->size(24.5);

        self::assertEquals($size, $this->converter()->size($size, AdultUnit::Cm));
        self::assertSame('CM 24.5', $size->human());
    }

    public function testConvertReturnsNullWhenRequestedUnitIsUnavailable(): void
    {
        self::assertNull($this->converter()->size(AdultUnit::Eu->size(999), AdultUnit::Cm));
    }

    public function test_loading_from_pdo(): void
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec(<<<'SQL'
      CREATE TABLE shoe_size_adults (
          eu REAL NOT NULL,
          us_men REAL NOT NULL,
          us_women REAL NOT NULL,
          uk REAL NOT NULL,
          cm REAL NOT NULL,
          mondopoint REAL NOT NULL
      );
    SQL);

        $pdo->exec(<<<'SQL'
        INSERT INTO shoe_size_adults (eu, us_men, us_women, uk, cm, mondopoint)
        VALUES
            (39, 6.5, 6, 24.6, 32, 35),
            (40, 7.5, 7, 25.3, 38, 39)
    SQL);

        $converter = Converter::fromPdo($pdo, ShoeType::Adults);

        self::assertNotEmpty($converter->equivalents(AdultUnit::Eu->size(39)));
    }

    public function test_loading_from_pdo_with_limit(): void
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec(<<<'SQL'
      CREATE TABLE shoe_size_adults (
          eu REAL NOT NULL,
          us_men REAL NOT NULL,
          us_women REAL NOT NULL,
          uk REAL NOT NULL,
          cm REAL NOT NULL,
          mondopoint REAL NOT NULL
      );
    SQL);

        $pdo->exec(<<<'SQL'
        INSERT INTO shoe_size_adults (eu, us_men, us_women, uk, cm, mondopoint)
        VALUES
            (39, 6.5, 6, 24.6, 32, 35),
            (40, 7.5, 7, 25.3, 38, 39)
    SQL);

        $shoes = Converter::fromPdo($pdo, ShoeType::Adults, limit: 1);

        self::assertNull($shoes->equivalents(AdultUnit::Eu->size(40))['eu']);
    }
}
