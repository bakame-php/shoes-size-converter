<?php

declare(strict_types=1);

namespace Bakame\Shoes;

use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\UnableToProcessCsv;
use RuntimeException;
use SplFileInfo;
use SplFileObject;

use function array_map;
use function str_contains;
use function trim;

final readonly class Converter
{
    private const CM_TO_INCH = 2.54;

    /**
     * @param Reader<array<non-empty-string, int|float>> $tabularData
     */
    private function __construct(private Reader $tabularData)
    {
    }

    /**
     * @param non-empty-string|resource|SplFileObject|SplFileInfo $path
     *
     * @throws \League\Csv\Exception
     * @throws \League\Csv\InvalidArgument
     * @throws \League\Csv\UnavailableStream
     */
    public static function fromPath($path): self
    {
        $trimmer = static fn (array $row) => array_map(
            static function (mixed $value): int|float {
                is_string($value) || throw new RuntimeException('The CSV data layer is corrupted.');

                $v = trim($value);

                return true === str_contains($v, '.')
                    ? (float) $v
                    : (int) $v;
            },
            $row
        );
        $reader = Reader::from($path);
        $reader->addFormatter($trimmer);
        $reader->setHeaderOffset(0);
        $reader->setEscape('');

        return new self($reader);
    }

    /**
     * @throws UnableToProcessCsv
     *
     * @return iterable<ShoeSize>
     */
    public function availableSizes(Unit $for): iterable
    {
        /** @var int|float $value */
        foreach ($this->tabularData->fetchColumn($for->value) as $value) {
            yield $for->size($value);
        }
    }

    public function inInch(ShoeSize $size): ?float
    {
        $cm = $this->inCm($size);

        return null === $cm ? null : ($cm / self::CM_TO_INCH);
    }

    public function inCm(ShoeSize $size): int|float|null
    {
        return Unit::Cm === $size->unit ? $size->value : $this->convert($size, Unit::Cm)?->value;
    }

    public function convert(ShoeSize $size, Unit $to): ?ShoeSize
    {
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
