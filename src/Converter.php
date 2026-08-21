<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use League\Csv\Buffer;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularData;
use PDO;
use SplFileInfo;
use SplFileObject;
use Throwable;
use ValueError;

use function array_map;
use function implode;
use function is_string;
use function trim;

final readonly class Converter
{
    public function __construct(private TabularData $tabularData)
    {
    }

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
    public static function fromCsv(mixed $path): self
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
        } catch (Throwable $exception) {
            throw new ShoeException('Unable to read tabular data.', previous: $exception);
        }

        return new self($tabularData);
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
    public static function fromPdo(PDO $connection, int $limit = 500): self
    {
        0 <= $limit || throw new ValueError('The limit must be a non-negative integer.'); /* @phpstan-ignore-line */
        $limitQuery = 0 < $limit ? ' LIMIT '.$limit : '';

        try {
            $fields = array_map(fn (Unit $unit): string => $unit->value, Unit::cases());
            $query = 'SELECT '.implode(',', $fields).' FROM shoe_sizes ORDER BY EU'.$limitQuery;
            $stmt = $connection->prepare($query);
            $stmt->execute();
            $tabularData = Buffer::from($stmt);
        } catch (Throwable $exception) {
            throw new ShoeException('Unable to read tabular data.', previous: $exception);
        }

        return new self($tabularData);
    }

    /**
     * @throws ShoeException
     *
     * @return iterable<ShoeSize>
     */
    public function availableSizes(Unit $for): iterable
    {
        try {
            /** @var float $value */
            foreach ($this->tabularData->fetchColumn($for->value) as $value) {
                yield $for->size($value);
            }
        } catch (Throwable $exception) {
            throw new ShoeException('Unable to read tabular data.', previous: $exception);
        }
    }

    public function inUnit(ShoeSize $size, Unit $to): ?ShoeSize
    {
        if ($to === $size->unit) {
            return $size;
        }

        foreach ($this->equivalents($size) as $shoeSize) {
            if ($to === $shoeSize->unit) {
                return $shoeSize;
            }
        }

        return null;
    }

    /**
     * Returns the available equivalents for the given shoe size.
     *
     * @return array<non-empty-string, ShoeSize>
     */
    public function equivalents(ShoeSize $size): array
    {
        $data = (new Statement())
            ->andWhere($size->unit->value, '=', $size->value)
            ->process($this->tabularData)
            ->first();

        $sizes = [];
        /**
         * @var non-empty-string $unitString
         * @var int|float $value
         */
        foreach ($data as $unitString => $value) {
            $sizes[$unitString] = Unit::from($unitString)->size($value);
        }

        return $sizes;
    }
}
