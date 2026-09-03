<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function fwrite;
use function tmpfile;

#[CoversClass(Converter::class)]
#[CoversClass(ChildSize::class)]
#[CoversClass(ChildUnit::class)]
#[CoversClass(AdultSize::class)]
#[CoversClass(AdultUnit::class)]
#[CoversClass(Length::class)]
#[CoversClass(LengthUnit::class)]
#[CoversClass(LengthRange::class)]
final class ConverterTest extends TestCase
{
    protected function adultConverter(): Converter
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

    private function childConverter(): Converter
    {
        $csv = <<<CSV
eu,uk,us,cm
20,4,5,12.0
21,5,6,12.5
22,6,7,13.0
23,7,8,13.5
CSV;

        $data = tmpfile();
        fwrite($data, $csv);

        return Converter::fromCsv($data, ShoeType::Children);
    }

    public function testFromPath(): void
    {
        self::assertEquals(
            [
                'eu' => AdultUnit::Eu->of(34),
                'uk' => AdultUnit::Uk->of(2.5),
                'us_men' => AdultUnit::UsMen->of(3.5),
                'us_women' => AdultUnit::UsWomen->of(4.5),
                'cm' => AdultUnit::Cm->of(21.5),
                'mondopoint' => AdultUnit::Mondopoint->of(215),
            ],
            $this->adultConverter()->equivalents(AdultUnit::Eu->of(34))
        );
    }

    public function testAvailableSizesReturnsSizesForUnit(): void
    {
        self::assertEquals(
            [
                AdultUnit::Eu->of(34),
                AdultUnit::Eu->of(35),
                AdultUnit::Eu->of(35.5),
                AdultUnit::Eu->of(36.5),
            ],
            iterator_to_array($this->adultConverter()->availableSizes(AdultUnit::Eu))
        );
    }

    public function testListReturnsAllAvailableUnits(): void
    {
        self::assertEquals(
            [
                'eu' => AdultUnit::Eu->of(34),
                'us_men' => AdultUnit::UsMen->of(3.5),
                'us_women' => AdultUnit::UsWomen->of(4.5),
                'uk' => AdultUnit::Uk->of(2.5),
                'cm' => AdultUnit::Cm->of(21.5),
                'mondopoint' => AdultUnit::Mondopoint->of(215),
            ],
            $this->adultConverter()->equivalents(AdultUnit::Mondopoint->of(215))
        );
    }

    public function testListReturnsEmptyArrayWhenSizeDoesNotExist(): void
    {
        self::assertNull($this->adultConverter()->equivalents(AdultUnit::Eu->of(999))['eu']);
    }

    public function testConvertReturnsRequestedUnit(): void
    {
        self::assertEquals(
            AdultUnit::Cm->of(21.5),
            $this->adultConverter()->size(AdultUnit::Mondopoint->of(215), AdultUnit::Cm)
        );
    }

    public function testConvertReturnsSameInstanceWhenAlreadyInRequestedUnit(): void
    {
        $size = AdultUnit::Cm->of(24.5);

        self::assertEquals($size, $this->adultConverter()->size($size, AdultUnit::Cm));
        self::assertSame('CM 24.5', $size->label());
    }

    public function testConvertReturnsNullWhenRequestedUnitIsUnavailable(): void
    {
        self::assertNull($this->adultConverter()->size(AdultUnit::Eu->of(999), AdultUnit::Cm));
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

        self::assertNotEmpty($converter->equivalents(AdultUnit::Eu->of(39)));
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

        self::assertNull($shoes->equivalents(AdultUnit::Eu->of(40))['eu']);
    }

    public function testLastLengthRangeReturnsRangeForChildSize(): void
    {
        $range = $this->childConverter()->lastLengthRange(ChildUnit::Eu->of(20));
        self::assertNotNull($range);
        [$min, $max] = $range;
        self::assertEquals($min->millimeters, 130);
        self::assertEquals($max->millimeters, 136);
    }

    public function testLastLengthRangeReturnsNullWhenUnavailable(): void
    {
        self::assertNull($this->childConverter()->lastLengthRange(ChildUnit::Eu->of(999)));
    }
}
