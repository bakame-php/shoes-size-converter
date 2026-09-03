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
    private const float CHILD_TOE_ALLOWANCE = 1.08;
    private const int CHILD_LAST_LENGTH_RANGE = 6;

    /**
     * Creates a shoe-size converter from CSV data.
     *
     * The CSV file must contain columns corresponding to the shoe units supported
     * by the given unit type. The first row is used as the column header.
     * Values are trimmed and converted to integers or floating-point numbers.
     *
     * The unit type determines which shoe-unit enum is used to interpret the
     * CSV columns and validate the resulting data.
     *
     * @param non-empty-string|resource|SplFileObject|SplFileInfo $path
     *
     * @throws ShoeException If the CSV data cannot be read or contains invalid data.
     */
    public static function fromCsv(mixed $path, ShoeType $shoeType): self
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

            return new self(
                $shoeType,
                iterator_to_array($tabularData, false) /* @phpstan-ignore-line */
            );
        } catch (Exception $exception) {
            throw new ShoeException('Unable to read tabular data.', previous: $exception);
        }
    }

    /**
     * Creates a shoe-size converter from a database table.
     *
     * The table schema depends on the given unit type.
     *
     * For adults, the table must contain the following columns:
     *
     * * `eu`: European shoe size
     * * `us_men`: US men's shoe size
     * * `us_women`: US women's shoe size
     * * `uk`: UK shoe size
     * * `cm`: Foot length in centimeters
     * * `mondopoint`: Foot length in millimeters
     *
     * For children, the table must contain the following columns:
     *
     * * `eu`: European shoe size
     * * `us`: US shoe size
     * * `uk`: UK shoe size
     * * `cm`: Foot length in centimeters
     * * `mondopoint`: Foot length in millimeters
     *
     * Column names are read directly from the database table. Values are read
     * as integers or floating-point numbers. Values in the children table may
     * also be null.
     *
     * A limit of `0` disables the row limit.
     *
     * @param non-negative-int $limit Maximum number of rows to read, or `0` for no limit.
     *
     * @throws ValueError If the limit is negative.
     * @throws ShoeException If the table cannot be read or contains invalid data.
     */
    public static function fromPdo(PDO $connection, ShoeType $unitType, int $limit = 500): self
    {
        0 <= $limit || throw new ValueError('The limit must be a non-negative integer.'); /* @phpstan-ignore-line */
        $limitQuery = 0 < $limit ? ' LIMIT '.$limit : '';

        try {
            $unitClass = AdultUnit::class;
            $orderby = AdultUnit::Mondopoint->value;
            if (ShoeType::Children === $unitType) {
                $unitClass = ChildUnit::class;
                $orderby = ChildUnit::Mondopoint->value;
            }
            $tableName = 'shoe_size_'.$unitType->value;
            $fields = array_map(fn (ShoeUnit $unit): string => $unit->value, $unitClass::cases());
            $query = 'SELECT '.implode(',', $fields).' FROM '.$tableName.' ORDER BY '.$orderby.$limitQuery;
            $stmt = $connection->prepare($query);
            $stmt->execute();
            /** @param list<array{non-empty-string, int|float}> $data */
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return new self($unitType, $data); /* @phpstan-ignore-line */
        } catch (PDOException $exception) {
            throw new ShoeException('Unable to read tabular data.', previous: $exception);
        }
    }

    /**
     * @param list<array<non-empty-string, int|float|null>> $tabularData
     */
    public function __construct(private ShoeType $shoeType, private array $tabularData)
    {
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
                    yield $for->of($value);
                }
            }
        } catch (Throwable $exception) {
            throw new ShoeException('Unable to read the conversion table.', previous: $exception);
        }
    }

    /**
     * @throws ShoeException
     */
    public function size(ShoeSize $shoe, ShoeUnit $in): ?ShoeSize
    {
        $this->assertSupports($in);
        if ($in === $shoe->unit) {
            return $shoe;
        }

        foreach ($this->equivalents($shoe) as $equivalent) {
            if ($in === $equivalent?->unit) {
                return $equivalent;
            }
        }

        return null;
    }

    public function supports(ShoeUnit|ShoeSize $value): bool
    {
        return $this->shoeType->supports($value);
    }

    /**
     * Returns the available equivalents for the given shoe size.
     *
     * @throws ShoeException
     *
     * @return array<string, ?ShoeSize>
     */
    public function equivalents(ShoeSize $shoe): array
    {
        $this->assertSupports($shoe);

        $equivalents = [];
        foreach ($shoe->unit::cases() as $unit) {
            $equivalents[$unit->value] = null;
        }

        foreach ($this->tabularData as $arr) {
            if ($arr[$shoe->unit->value] !== $shoe->size) {
                continue;
            }

            foreach ($shoe->unit::cases() as $unit) {
                $key = $unit->value;
                if (array_key_exists($key, $arr) && null !== $arr[$key]) {
                    $equivalents[$key] = $unit->of($arr[$key]);
                }
            }

            return $equivalents;
        }

        return $equivalents;
    }

    /**
     * Returns the last length range if the converter can determine one.
     */
    public function lastLengthRange(ShoeSize $shoe): ?LengthRange
    {
        if (!$shoe instanceof ChildSize) {
            return null;
        }

        try {
            $length = $this->footLength($shoe);
        } catch (ShoeException) {
            return null;
        }

        $min = $length->millimeters * self::CHILD_TOE_ALLOWANCE;
        $max = $min + self::CHILD_LAST_LENGTH_RANGE;

        return new LengthRange(Length::fromMillimeters($min), Length::fromMillimeters($max));
    }

    /**
     * @throws ShoeException
     */
    public function footLength(ShoeSize $shoe): Length
    {
        $this->assertSupports($shoe);

        return $shoe instanceof AdultSize ? $shoe->footLength : $this->childFootLength($shoe);
    }

    /**
     * @throws ShoeException
     */
    private function assertSupports(ShoeUnit|ShoeSize $value): void
    {
        $this->shoeType->supports($value) || throw new ShoeException('The converter table supports '.$this->shoeType->label());
    }

    private function childFootLength(ShoeSize $shoe): Length
    {
        try {
            $shoeSize = $this->size($shoe, ChildUnit::Mondopoint);
            if (null !== $shoeSize) {
                return Length::fromMillimeters($shoeSize->size);
            }

            $shoeSize = $this->size($shoe, ChildUnit::Cm);
            null !== $shoeSize || throw new ShoeException('Unable to determine the child shoe length.');

            return Length::fromCentimeters($shoeSize->size);
        } catch (ShoeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new ShoeException('Unable to read the conversion table.', previous: $exception);
        }
    }
}
