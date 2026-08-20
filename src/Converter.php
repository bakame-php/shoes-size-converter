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
use function str_contains;
use function trim;

final readonly class Converter
{
    private const CM_TO_INCH = 2.54;

    public function __construct(private TabularData $tabularData)
    {
    }

    /**
     * Creates a shoe-size collection from a CSV file.
     *
     * The CSV file must have the following columns:
     *
     * - `EU`: European shoe size
     * - `US`: US shoe size
     * - `UK`: UK shoe size
     * - `CM`: Foot length in centimeters
     *
     * The first row is used as the column header. Values are trimmed and converted
     * to integers or floating-point numbers based on their representation.
     *
     * Example:
     *
     * ```csv
     * EU,US,UK,CM
     * 39,6.5,6,24.6
     * 40,7.5,7,25.3
     * 41,8,7.5,26
     * ```
     *
     * @param non-empty-string|resource|SplFileObject|SplFileInfo $path
     *
     * @throws ShoeException If the CSV data cannot be read or contains invalid data.
     */
    public static function fromCsv($path): self
    {
        $trimmer = static fn (array $row) => array_map(
            static function (mixed $value): int|float {
                is_string($value) || throw new ShoeException('The data layer is corrupted.');

                $v = trim($value);

                return true === str_contains($v, '.')
                    ? (float) $v
                    : (int) $v;
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
     * - `EU`: European shoe size
     * - `US`: US shoe size
     * - `UK`: UK shoe size
     * - `CM`: Foot length in centimeters
     *
     * Example schema:
     *
     * ```sql
     * CREATE TABLE shoe_sizes (
     *     EU INTEGER NOT NULL,
     *     US REAL NOT NULL,
     *     UK REAL NOT NULL,
     *     CM REAL NOT NULL
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
            /** @var int|float $value */
            foreach ($this->tabularData->fetchColumn($for->value) as $value) {
                yield $for->size($value);
            }
        } catch (Throwable $exception) {
            throw new ShoeException('Unable to read tabular data.', previous: $exception);
        }
    }

    public function inInch(ShoeSize $size): ?float
    {
        $cm = $this->inCm($size);

        return null === $cm ? null : ($cm / self::CM_TO_INCH);
    }

    public function inCm(ShoeSize $size): int|float|null
    {
        return Unit::Cm === $size->unit ? $size->value : $this->inUnit($size, Unit::Cm)?->value;
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
