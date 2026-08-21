<?php

//The script is based on https://github.com/sarah-schuh/schuhgroessen-umrechner/blob/main/index.php
// re-implemented to return only JSON responses.
// php converter.php?unit=UK&size=9.5 (where UK represents the shoe unit and 39 its size
// The script works with a SQLite Database backend

use Bakame\Shoes\Converter;
use Bakame\Shoes\ShoeSize;
use Bakame\Shoes\Unit;

require __DIR__.'/../vendor/autoload.php';

$path =  __DIR__.'/../data/shoe_sizes.csv';

$fail = static function (string $message, int $status): never {
    http_response_code($status);
    header('Content-Type: application/problem+json; charset=UTF-8');
    echo json_encode([
        'type' => 'about:blank',
        'title' => match ($status) {
            400 => 'Bad Request',
            404 => 'Not Found',
            422 => 'Unprocessable content',
            default => 'Internal Server Error',
        },
        'status' => $status,
        'detail' => $message,
        'instance' => $_SERVER['REQUEST_URI'],
    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
};

$unit = Unit::tryFrom(strtolower((string) ($_GET['unit'] ?? '')));
null !== $unit || $fail('Please provide a valid shoe-size unit (e.g., EU, US, UK, or CM).', 400);

$size = $_GET['size'] ?? '';
(is_string($size) && '' !== trim($size)) || $fail('Please provide a shoe size.', 400);
$size = filter_var(trim($size), FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
false !== $size || $fail("Please provide a valid {$unit->value} shoe size.", 400);

try {
    $converter = Converter::fromCsv($path);
} catch (Throwable) {
    $fail('Data source is missing or not readable.', 500);
}

$source = 'ISO 19407:2023-based';
$inputSize = $unit->size($size);
try {
    $result = $converter->equivalents($inputSize);
    if ([] === $result) {
        $source = 'calculated';
        $result = $inputSize->equivalents();
    }

    [] !== $result || $fail('No matching shoe size found for "'.$inputSize->human().'"', 404);
} catch (ValueError) {
    $fail('The input size "'.$inputSize->human().'" cannot be converted to another unit system.', 422);
}

$cm = $result[Unit::Cm->value];
null !== $cm || $fail('Unable to determine the foot length for "'.$inputSize->human().'"', 422);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'source' => $source,
    'measurements' => [
        'centimeters' => $cm->value,
        'inches' => $cm->inInches(),
    ],
    'sizes' => array_values(array_map(
        static fn (?ShoeSize $size, string $unit): array => ['value' => $size?->value, 'unit' => Unit::from($unit)],
        $result,
        array_keys($result),
    )),
], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
