<?php

//The script is based on https://github.com/sarah-schuh/schuhgroessen-umrechner/blob/main/index.php
// re-implemented to return only JSON responses.
// php converter.php?unit=UK&size=9.5 (where UK represents the shoe unit and 39 its size
// The script works with a SQLite Database backend

use Bakame\Shoes\Converter;
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
            default => 'Internal Server Error',
        },
        'status' => $status,
        'detail' => $message,
        'instance' => $_SERVER['REQUEST_URI'],
    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
};

$unit = Unit::tryFrom(strtoupper((string) ($_GET['unit'] ?? '')));
null !== $unit || $fail('Please provide a valid shoe-size unit (e.g., EU, US, UK, or CM).', 400);

$size = $_GET['size'] ?? '';
(is_string($size) && '' !== trim($size)) || $fail('Please provide a shoe size.', 400);
$size = filter_var(trim($size), FILTER_VALIDATE_FLOAT);
false !== $size || $fail("Please provide a valid {$unit->value} shoe size.", 400);

try {
    $converter = Converter::fromCsv($path);
} catch (Throwable) {
    $fail('Data source is missing or not readable.', 500);
}

$inputSize = $unit->size($size);
$result = $converter->equivalents($inputSize);
[] !== $result || $fail('No matching shoe size found for '.$inputSize->unit->value.' '.$inputSize->value, 404);
$cm = $result[Unit::Cm->value];

header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'sizes' => array_values($result),
    'measurements' => [
        'centimeters' => $cm->value,
        'inches' => $converter->inInch($cm),
    ],
], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
