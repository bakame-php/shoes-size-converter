<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use League\Csv\Exception;
use League\Csv\Reader;
use PDO;
use PDOException;
use SplFileInfo;
use SplFileObject;
use Throwable;
use ValueError;

use function array_column;
use function array_key_exists;
use function array_map;
use function implode;
use function is_string;
use function iterator_to_array;
use function trim;

final readonly class Converter
{
    /**
     * Creates a shoe-size collection from a CSV file.
     *
     * The CSV file must have the following columns:
     *
     * - `eu`: European shoe size
     * - `us_men`: US men's shoe size
     * - `us_women`: US women's shoe size
     * - `uk`: UK shoe size
     * - `cm`: Foot length in centimeters
     * - `mondopoint`: Foot length in millimeters
     *
     * The first row is used as the column header. Values are trimmed and converted
     * to integers or floating-point numbers based on their representation.
     *
     * Example:
     *
     * ```csv
     * mondopoint,cm,eu,uk,us_men,us_women
     * 215,21.5,34,2.5,3.5,4.5
     * 220,22.0,35,3,4,5
     * 225,22.5,35.5,3.5,4.5,5.5
     * 230,23.0,36.5,4,5,6
     * ```
     *
     * @param non-empty-string|resource|SplFileObject|SplFileInfo $path
     *
     * @throws ShoeException If the CSV data cannot be read or contains invalid data.
     */
    public static function fromCsv(string $className, mixed $path): self
    {
        $trimmer = static fn (array $row) => array_map(
            static function (mixed $value): float {
                is_string($value) || throw new ShoeException('The data layer is corrupted.');
                return (float) trim($value);
            },
            $row
        );

        try {
            $tabularData = Reader::from($path)
                ->addFormatter($trimmer)
                ->setHeaderOffset(0)
                ->setEscape('');

            return new self($className, iterator_to_array($tabularData, false)); /* @phpstan-ignore-line */
        } catch (Exception $exception) {
            throw new ShoeException('Unable to read tabular data.', previous: $exception);
        }
    }

    /**
     * Creates a shoe-size collection from the `shoe_sizes` table.
     *
     * The table must have the following columns:
     *
     * - `eu`: European shoe size
     * - `us_men`: US men's shoe size
     * - `us_women`: US women's shoe size
     * - `uk`: UK shoe size
     * - `cm`: Foot length in centimeters
     * - `mondopoint`: Foot length in millimeters
     *
     * Example schema:
     *
     * ```sql
     * CREATE TABLE shoe_sizes (
     *     eu REAL NOT NULL,
     *     us_men REAL NOT NULL,
     *     us_women REAL NOT NULL,
     *     uk REAL NOT NULL,
     *     cm REAL NOT NULL,
     *     mondopoint REAL NOT NULL
     * );
     * ```
     *
     * A limit of `0` disables the row limit.
     *
     * @param non-negative-int $limit Maximum number of rows to read, or `0` for no limit.
     *
     * @throws ValueError If the limit is negative.
     * @throws ShoeException If the table cannot be read.
     */
    public static function fromPdo(string $className, PDO $connection, int $limit = 500): self
    {
        0 <= $limit || throw new ValueError('The limit must be a non-negative integer.'); /* @phpstan-ignore-line */
        $limitQuery = 0 < $limit ? ' LIMIT '.$limit : '';

        try {
            $fields = array_map(fn (Unit $unit): string => $unit->value, Unit::cases());
            $query = 'SELECT '.implode(',', $fields).' FROM shoe_sizes ORDER BY '.Unit::Mondopoint->value.$limitQuery;
            $stmt = $connection->prepare($query);
            $stmt->execute();
            /** @param list<array{non-empty-string, int|float}> $data */
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return new self($className, $data); /* @phpstan-ignore-line */
        } catch (PDOException $exception) {
            throw new ShoeException('Unable to read tabular data.', previous: $exception);
        }
    }

    /**
     * @param list<array<non-empty-string, int|float|null>> $tabularData
     *
     * @throws ShoeException
     */
    public function __construct(private string $unitClass, private array $tabularData)
    {
        Unit::class === $this->unitClass
            || ChildUnit::class === $this->unitClass
            || throw new ShoeException('The class "'.$this->unitClass.'" is not supported.');
    }

    /**
     * @throws ShoeException
     *
     * @return iterable<ShoeSize>
     */
    public function availableSizes(ShoeUnit $for): iterable
    {
        $this->assertSupports($for);

        try {
            /** @var int|float|null $value */
            foreach (array_column($this->tabularData, $for->value) as $value) {
                if (null !== $value) {
                    yield $for->size($value);
                }
            }
        } catch (Throwable $exception) {
            throw new ShoeException('Unable to read the conversion table.', previous: $exception);
        }
    }

    /**
     * @throws ShoeException
     */
    public function inUnit(ShoeSize $size, ShoeUnit $to): ?ShoeSize
    {
        $this->assertSupports($to);
        if ($to === $size->unit) {
            return $size;
        }

        foreach ($this->equivalents($size) as $shoeSize) {
            if ($to === $shoeSize?->unit) {
                return $shoeSize;
            }
        }

        return null;
    }

    /**
     *
     * @throws ShoeException
     */
    private function assertSupports(ShoeUnit|ShoeSize $value): void
    {
        $unit = $value instanceof ShoeSize
            ? $value->unit
            : $value;

        $unit::class === $this->unitClass || throw new ShoeException(sprintf('The converter table supports %s, not %s.', $this->unitClass, $unit::class));
    }

    /**
     * Returns the available equivalents for the given shoe size.
     *
     * @throws ShoeException
     *
     * @return array<string, ?ShoeSize>
     */
    public function equivalents(ShoeSize $size): array
    {
        $this->assertSupports($size);

        $equivalents = [];
        foreach ($size->unit::cases() as $unit) {
            $equivalents[$unit->value] = null;
        }

        foreach ($this->tabularData as $arr) {
            if ($arr[$size->unit->value] !== $size->value) {
                continue;
            }

            foreach ($size->unit::cases() as $unit) {
                $equivalents[$unit->value] = (array_key_exists($unit->value, $arr) && null !== $arr[$unit->value])
                    ? $unit->size($arr[$unit->value])
                    : null;
            }

            return $equivalents;
        }

        return $equivalents;
    }

    public function lastLength(ShoeSize $size, LengthUnit $unit): ?LengthRange
    {
        return $size instanceof ChildSize
            ? $unit->lastLengthRange($this->length($size, LengthUnit::Millimeter))
            : null;
    }

    public function length(ShoeSize $size, LengthUnit $unit): float
    {
        $this->assertSupports($size);

        return $size instanceof AdultSize ? $size->length($unit) : $this->childLength($size, $unit);
    }

    private function childLength(ShoeSize $size, LengthUnit $unit): float
    {
        try {
            $shoeSize = $this->inUnit($size, ChildUnit::Mondopoint);
            if (null !== $shoeSize) {
                return LengthUnit::Millimeter->convert($shoeSize->value, $unit);
            }

            $shoeSize = $this->inUnit($size, ChildUnit::Cm);
            null !== $shoeSize || throw new ShoeException('Unable to determine the child shoe length.');

            return LengthUnit::Centimeter->convert($shoeSize->value, $unit);

        } catch (ShoeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new ShoeException('Unable to read the conversion table.', previous: $exception);
        }
    }
}
